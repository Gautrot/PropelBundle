<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Service;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMText;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * # SchemaConverter
 */
class SchemaConverter
{
    public const FORMAT_XML = 'xml';
    public const FORMAT_YAML = 'yaml';

    /**
     * @param string $source
     * @param string $targetFormat
     * @return string
     * @throws DOMException
     */
    public function convert(string $source, string $targetFormat): string
    {
        $sourceFormat = $this->getFormat($source);

        if ($sourceFormat === $targetFormat) {
            return $this->readFile($source);
        }

        if ($sourceFormat === self::FORMAT_XML && $targetFormat === self::FORMAT_YAML) {
            return $this->convertXmlToYaml($source);
        }

        if ($sourceFormat === self::FORMAT_YAML && $targetFormat === self::FORMAT_XML) {
            return $this->convertYamlToXml($source);
        }

        throw new InvalidArgumentException("Unsupported target schema format: \"$targetFormat\".");
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFormat(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'xml' => self::FORMAT_XML,
            'yaml', 'yml' => self::FORMAT_YAML,
            default => throw new InvalidArgumentException("Unsupported schema file: \"$filename\". Expected an XML or YAML file."),
        };
    }

    /**
     * @param string $source
     * @return string
     */
    private function readFile(string $source): string
    {
        $contents = file_get_contents($source);
        if ($contents === false) {
            throw new RuntimeException("Unable to read schema: \"$source\".");
        }

        return $contents;
    }

    /**
     * @param string $source
     * @return string
     */
    private function convertXmlToYaml(string $source): string
    {
        $document = $this->loadXml($source);

        if (!$document->documentElement instanceof DOMElement) {
            throw new InvalidArgumentException("Schema \"$source\" does not have a root element.");
        }
        if ('database' !== $document->documentElement->tagName) {
            throw new InvalidArgumentException("Schema \"$source\" must use a \"database\" root element.");
        }

        $schema = $this->databaseToYaml($document->documentElement, $source);

        return Yaml::dump($schema, 99, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    /**
     * @param string $source
     * @return DOMDocument
     */
    private function loadXml(string $source): DOMDocument
    {
        $useInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $document = new DOMDocument();
            if ($document->load($source, LIBXML_NONET | LIBXML_NOBLANKS) === false) {
                $error = libxml_get_last_error();
                throw new InvalidArgumentException(sprintf(
                    'Unable to parse XML schema "%s"%s.',
                    $source,
                    ($error) ? ': ' . trim($error->message) : ''
                ));
            }

            return $document;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
        }
    }

    /**
     * @param DOMElement $database
     * @param string $source
     * @return array
     */
    private function databaseToYaml(DOMElement $database, string $source): array
    {
        $connection = $database->getAttribute('name');
        if ($connection === '') {
            throw new InvalidArgumentException("Schema \"$source\" must define a database name.");
        }

        $schema = [];
        $attributes = $this->xmlAttributesToYaml($database, ['name']);
        if ($attributes !== []) {
            $schema['_attributes'] = $attributes;
        }

        $unsupported = [];
        foreach ($database->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            if ($child->tagName === 'table') {
                $tableName = $child->getAttribute('name');
                if ($tableName === '') {
                    throw new InvalidArgumentException("Schema \"$source\" contains a table without a name.");
                }
                if (array_key_exists($tableName, $schema)) {
                    throw new InvalidArgumentException("Schema \"$source\" contains the table \"$tableName\" more than once.");
                }

                $schema[$tableName] = $this->tableToYaml($child);
                continue;
            }

            $unsupported[$child->tagName] ??= [];
            $unsupported[$child->tagName][] = $this->xmlElementToYaml($child);
        }

        if ($unsupported !== []) {
            $schema['_xml'] = $unsupported;
        }

        return [$connection => $schema];
    }

    /**
     * @param DOMElement $element
     * @param array $excluded
     * @return array
     */
    private function xmlAttributesToYaml(DOMElement $element, array $excluded = []): array
    {
        $attributes = [];
        $namespaceDeclarations = [];
        foreach ($element->attributes as $attribute) {
            if (in_array($attribute->nodeName, $excluded, true)) {
                continue;
            }
            $attributes[$attribute->nodeName] = $this->normalizeXmlBoolean($attribute->nodeValue);

            if ($attribute->prefix !== '' && $attribute->prefix !== 'xml') {
                $namespace = $element->lookupNamespaceURI($attribute->prefix);
                if ($namespace !== null) {
                    $namespaceDeclarations['xmlns:' . $attribute->prefix] = $namespace;
                }
            }
        }

        return $attributes + $namespaceDeclarations;
    }

    /**
     * @param string $value
     * @return string|bool
     */
    private function normalizeXmlBoolean(string $value): string|bool
    {
        return match ($value) {
            'true' => true,
            'false' => false,
            default => $value,
        };
    }

    /**
     * @param DOMElement $table
     * @return array
     */
    private function tableToYaml(DOMElement $table): array
    {
        $schema = [];
        $attributes = $this->xmlAttributesToYaml($table, ['name']);
        if ($attributes !== []) {
            $schema['_attributes'] = $attributes;
        }

        $foreignKeys = [];
        $indexes = [];
        $uniques = [];
        $behaviors = [];
        $unsupported = [];

        foreach ($table->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            switch ($child->tagName) {
                case 'column':
                    $columnName = $child->getAttribute('name');
                    if ($columnName === '') {
                        throw new InvalidArgumentException(sprintf('Table "%s" contains a column without a name.', $table->getAttribute('name')));
                    }
                    $schema[$columnName] = $this->columnToYaml($child);
                    break;
                case 'foreign-key':
                    $foreignKeys[] = $this->foreignKeyToYaml($child);
                    break;
                case 'index':
                    $indexes[$child->getAttribute('name')] = $this->indexToYaml($child);
                    break;
                case 'unique':
                    $uniques[$child->getAttribute('name')] = $this->indexToYaml($child);
                    break;
                case 'behavior':
                    $behaviors[$child->getAttribute('name')] = $this->behaviorToYaml($child);
                    break;
                default:
                    $unsupported[$child->tagName] ??= [];
                    $unsupported[$child->tagName][] = $this->xmlElementToYaml($child);
                    break;
            }
        }

        if ($foreignKeys !== []) {
            $schema['_foreignKeys'] = $foreignKeys;
        }
        if ($indexes !== []) {
            $schema['_indexes'] = $indexes;
        }
        if ($uniques !== []) {
            $schema['_uniques'] = $uniques;
        }
        if ($behaviors !== []) {
            $schema['_propel_behaviors'] = $behaviors;
        }
        if ($unsupported !== []) {
            $schema['_xml'] = $unsupported;
        }

        return $schema;
    }

    /**
     * @param DOMElement $column
     * @return string|array
     */
    private function columnToYaml(DOMElement $column): string|array
    {
        $attributes = $this->xmlAttributesToYaml($column, ['name']);
        if (isset($attributes['defaultValue'])) {
            $attributes['default'] = $attributes['defaultValue'];
            unset($attributes['defaultValue']);
        }

        if (isset($attributes['type']) && 1 === count($attributes)) {
            return (string)$attributes['type'];
        }
        if (isset($attributes['type'], $attributes['size']) && 2 === count($attributes)) {
            return "{$attributes['type']}({$attributes['size']})";
        }

        return $attributes;
    }

    /**
     * @param DOMElement $foreignKey
     * @return array
     */
    private function foreignKeyToYaml(DOMElement $foreignKey): array
    {
        $schema = $this->xmlAttributesToYaml($foreignKey);
        if (isset($schema['phpName'])) {
            $schema['fkPhpName'] = $schema['phpName'];
            unset($schema['phpName']);
        }
        if (isset($schema['skipSql'])) {
            $schema['fkSkipSql'] = $schema['skipSql'];
            unset($schema['skipSql']);
        }

        $references = [];
        foreach ($foreignKey->childNodes as $child) {
            if ($child->nodeName !== 'reference' || !$child instanceof DOMElement) {
                continue;
            }
            $references[] = $this->xmlAttributesToYaml($child);
        }
        $schema['references'] = $references;

        return $schema;
    }

    /**
     * @param DOMElement $index
     * @return array
     */
    private function indexToYaml(DOMElement $index): array
    {
        $columns = [];
        foreach ($index->childNodes as $child) {
            if ($child->nodeName !== 'index-column' || !$child instanceof DOMElement) {
                continue;
            }

            $name = $child->getAttribute('name');
            $size = $child->getAttribute('size');
            $columns[] = ($size === '') ? $name : "$name($size)";
        }

        return $columns;
    }

    /**
     * @param DOMElement $behavior
     * @return array|null
     */
    private function behaviorToYaml(DOMElement $behavior): ?array
    {
        $parameters = [];
        foreach ($behavior->childNodes as $child) {
            if ($child->nodeName !== 'parameter' || !$child instanceof DOMElement) {
                continue;
            }

            $parameters[$child->getAttribute('name')] = $this->normalizeXmlBoolean($child->getAttribute('value'));
        }

        return ($parameters !== []) ? $parameters : null;
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    private function xmlElementToYaml(DOMElement $element): array
    {
        $data = [];
        if ($element->hasAttributes()) {
            $data['_attributes'] = $this->xmlAttributesToYaml($element);
        }

        $text = '';
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $data[$child->tagName] ??= [];
                $data[$child->tagName][] = $this->xmlElementToYaml($child);
            } elseif ($child instanceof DOMText) {
                $text .= $child->nodeValue;
            }
        }

        if (trim($text) !== '') {
            $data['_value'] = $text;
        }

        return $data;
    }

    /**
     * @param string $source
     * @return string
     * @throws DOMException
     */
    private function convertYamlToXml(string $source): string
    {
        $schema = Yaml::parseFile($source);

        if (!is_array($schema)) {
            throw new InvalidArgumentException("Schema \"$source\" must contain a Propel YAML mapping.");
        }

        if ($this->isXmlShapedYamlSchema($schema)) {
            return $this->convertXmlShapedYamlToXml($schema, $source);
        }

        if (count($schema) !== 1) {
            throw new InvalidArgumentException("Schema \"$source\" must contain exactly one database connection.");
        }

        $connection = array_key_first($schema);
        if (!is_string($connection)) {
            throw new InvalidArgumentException("Schema \"$source\" must use a string connection name.");
        }

        $document = $this->newXmlDocument();

        $root = $document->createElement('database');
        $document->appendChild($root);
        $root->setAttribute('name', $connection);
        $this->populateDatabase($document, $root, $schema[$connection], $source);

        return $this->saveXml($document, $source);
    }

    /**
     * @param array $schema
     * @return bool
     */
    private function isXmlShapedYamlSchema(array $schema): bool
    {
        if (!isset($schema['database']) || !is_array($schema['database'])) {
            return false;
        }

        $attributes = $schema['database']['_attributes'] ?? [];

        return array_key_exists('table', $schema['database'])
            || is_array($attributes) && array_key_exists('name', $attributes);
    }

    /**
     * @param array $schema
     * @param string $source
     * @return string
     * @throws DOMException
     */
    private function convertXmlShapedYamlToXml(array $schema, string $source): string
    {
        $document = $this->newXmlDocument();

        $root = $document->createElement('database');
        $document->appendChild($root);
        $this->populateElement($document, $root, $schema['database'], 'database');

        return $this->saveXml($document, $source);
    }

    /**
     * @return DOMDocument
     */
    private function newXmlDocument(): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        return $document;
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $element
     * @param mixed $data
     * @param string $path
     * @return void
     * @throws DOMException
     */
    private function populateElement(DOMDocument $document, DOMElement $element, mixed $data, string $path): void
    {
        if ($data === null) {
            return;
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException("Element \"$path\" must be a YAML mapping.");
        }

        $attributes = $data['_attributes'] ?? [];
        if (!is_array($attributes) || ($attributes !== [] && array_is_list($attributes))) {
            throw new InvalidArgumentException("The attributes of \"$path\" must be a YAML mapping.");
        }

        $this->setAttributes($element, $attributes, $path);

        if (array_key_exists('_value', $data)) {
            $element->appendChild($document->createTextNode($this->normalizeScalar($data['_value'], "$path._value")));
        }

        foreach ($data as $name => $children) {
            if (in_array($name, ['_attributes', '_value'])) {
                continue;
            }

            $this->assertElementName($name, $path);
            $children = is_array($children) && array_is_list($children) ? $children : [$children];

            foreach ($children as $index => $childData) {
                $child = $document->createElement($name);
                $element->appendChild($child);
                $this->populateElement($document, $child, $childData, "$path.$name[$index]");
            }
        }
    }

    /**
     * @param DOMElement $element
     * @param array $attributes
     * @param string $path
     * @return void
     */
    private function setAttributes(DOMElement $element, array $attributes, string $path): void
    {
        foreach ($attributes as $name => $value) {
            if (!is_string($name)) {
                throw new InvalidArgumentException("The attributes of \"$path\" must use string keys.");
            }

            $this->assertAttributeName($name, $path);
            if ($name === 'xmlns' || str_starts_with($name, 'xmlns:')) {
                $element->setAttributeNS('http://www.w3.org/2000/xmlns/', $name, $this->normalizeScalar($value, "$path._attributes.$name"));
            }
        }

        foreach ($attributes as $name => $value) {
            if ($name === 'xmlns' || str_starts_with($name, 'xmlns:')) {
                continue;
            }

            $attributeValue = $this->normalizeScalar($value, "$path._attributes.$name");
            if (str_contains($name, ':')) {
                [$prefix] = explode(':', $name, 2);
                $namespace = $element->lookupNamespaceURI($prefix);
                if ($namespace !== null) {
                    $element->setAttributeNS($namespace, $name, $attributeValue);
                    continue;
                }
            }

            $element->setAttribute($name, $attributeValue);
        }
    }

    /**
     * @param string $name
     * @param string $path
     * @return void
     */
    private function assertAttributeName(string $name, string $path): void
    {
        if (!preg_match('/^(?:[A-Za-z_][A-Za-z0-9_.-]*:)?[A-Za-z_][A-Za-z0-9_.-]*$/', $name)) {
            throw new InvalidArgumentException("Invalid attribute name \"$name\" at \"$path\".");
        }
    }

    /**
     * @param mixed $value
     * @param string $path
     * @return string
     */
    private function normalizeScalar(mixed $value, string $path): string
    {
        if (is_bool($value)) {
            return ($value) ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string)$value;
        }

        if ($value === null) {
            return '';
        }

        throw new InvalidArgumentException("Value \"$path\" must be a scalar.");
    }

    /**
     * @param mixed $name
     * @param string $path
     * @return void
     */
    private function assertElementName(mixed $name, string $path): void
    {
        if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_.-]*$/', $name)) {
            throw new InvalidArgumentException("Invalid child element name at \"$path\".");
        }
    }

    /**
     * @param DOMDocument $document
     * @param string $source
     * @return string
     */
    private function saveXml(DOMDocument $document, string $source): string
    {
        $xml = $document->saveXML();
        if ($xml === false) {
            throw new RuntimeException("Unable to convert schema \"$source\" to XML.");
        }

        return $xml;
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $database
     * @param mixed $schema
     * @param string $source
     * @return void
     * @throws DOMException
     */
    private function populateDatabase(DOMDocument $document, DOMElement $database, mixed $schema, string $source): void
    {
        if (!is_array($schema)) {
            throw new InvalidArgumentException(sprintf('Database "%s" in "%s" must be a YAML mapping.', $database->getAttribute('name'), $source));
        }

        $attributes = $schema['_attributes'] ?? [];
        $this->setAttributes($database, $this->attributesWithoutName($attributes, 'database'), 'database');

        foreach ($schema as $name => $definition) {
            if (!is_string($name) || $name === '_attributes') {
                continue;
            }
            if ($name === '_xml') {
                $this->appendXmlChildren($document, $database, $definition, 'database._xml');
                continue;
            }
            if (str_starts_with($name, '_')) {
                throw new InvalidArgumentException("Unsupported database key \"$name\" in \"$source\".");
            }

            $this->assertElementName($name, 'database');
            $table = $document->createElement('table');
            $table->setAttribute('name', $name);
            $database->appendChild($table);
            $this->populateTable($document, $table, $definition, "database.$name");
        }
    }

    /**
     * @param mixed $attributes
     * @param string $path
     * @return array
     */
    private function attributesWithoutName(mixed $attributes, string $path): array
    {
        if (!is_array($attributes) || ($attributes !== [] && array_is_list($attributes))) {
            throw new InvalidArgumentException("The attributes of \"$path\" must be a YAML mapping.");
        }
        unset($attributes['name']);

        return $attributes;
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $parent
     * @param mixed $children
     * @param string $path
     * @return void
     * @throws DOMException
     */
    private function appendXmlChildren(DOMDocument $document, DOMElement $parent, mixed $children, string $path): void
    {
        if (!is_array($children) || array_is_list($children)) {
            throw new InvalidArgumentException("The XML extension at \"$path\" must be a YAML mapping.");
        }

        foreach ($children as $name => $definitions) {
            $this->assertElementName($name, $path);
            $definitions = is_array($definitions) && array_is_list($definitions) ? $definitions : [$definitions];
            foreach ($definitions as $index => $definition) {
                $element = $document->createElement($name);
                $parent->appendChild($element);
                $this->populateElement($document, $element, $definition, "$path.$name[$index]");
            }
        }
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $table
     * @param mixed $schema
     * @param string $path
     * @return void
     * @throws DOMException
     */
    private function populateTable(DOMDocument $document, DOMElement $table, mixed $schema, string $path): void
    {
        if ($schema === null) {
            return;
        }
        if (!is_array($schema)) {
            throw new InvalidArgumentException("Table \"$path\" must be a YAML mapping.");
        }

        $attributes = $schema['_attributes'] ?? [];
        $this->setAttributes($table, $this->attributesWithoutName($attributes, $path), $path);

        $foreignKeys = [];
        $indexes = [];
        $uniques = [];
        foreach ($schema as $name => $definition) {
            if (!is_string($name) || $name === '_attributes') {
                continue;
            }
            if ($name === '_foreignKeys') {
                $foreignKeys[] = $definition;
                continue;
            }
            if ($name === '_indexes') {
                $indexes[] = $definition;
                continue;
            }
            if ($name === '_uniques') {
                $uniques[] = $definition;
                continue;
            }
            if ($name === '_propel_behaviors') {
                $this->appendBehaviors($document, $table, $definition, "$path._propel_behaviors");
                continue;
            }
            if ($name === '_xml') {
                $this->appendXmlChildren($document, $table, $definition, "$path._xml");
                continue;
            }
            if (str_starts_with($name, '_')) {
                throw new InvalidArgumentException("Unsupported table key \"$name\" at \"$path\".");
            }

            $this->appendColumn($document, $table, $name, $definition, $foreignKeys, $indexes, $uniques, $path);
        }

        foreach ($foreignKeys as $foreignKeyDefinitions) {
            $this->appendForeignKeys($document, $table, $foreignKeyDefinitions, "$path._foreignKeys");
        }
        foreach ($indexes as $indexDefinitions) {
            $this->appendIndexes($document, $table, $indexDefinitions, 'index', "$path._indexes");
        }
        foreach ($uniques as $uniqueDefinitions) {
            $this->appendIndexes($document, $table, $uniqueDefinitions, 'unique', "$path._uniques");
        }
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $table
     * @param mixed $behaviors
     * @param string $path
     * @return void
     * @throws DOMException
     */
    private function appendBehaviors(DOMDocument $document, DOMElement $table, mixed $behaviors, string $path): void
    {
        if (!is_array($behaviors) || array_is_list($behaviors)) {
            throw new InvalidArgumentException("Behaviors at \"$path\" must be a YAML mapping.");
        }

        foreach ($behaviors as $name => $parameters) {
            if (!is_string($name)) {
                throw new InvalidArgumentException("Behavior names at \"$path\" must be strings.");
            }

            $behavior = $document->createElement('behavior');
            $behavior->setAttribute('name', $name);
            $table->appendChild($behavior);
            if (null === $parameters) {
                continue;
            }
            if (!is_array($parameters) || array_is_list($parameters)) {
                throw new InvalidArgumentException("Behavior \"$name\" at \"$path\" must contain a YAML mapping.");
            }

            foreach ($parameters as $parameterName => $value) {
                if (!is_string($parameterName)) {
                    throw new InvalidArgumentException("Behavior parameter names at \"$path\" must be strings.");
                }
                $parameter = $document->createElement('parameter');
                $parameter->setAttribute('name', $parameterName);
                $parameter->setAttribute('value', $this->normalizeScalar($value, "$path.$name.$parameterName"));
                $behavior->appendChild($parameter);
            }
        }
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $table
     * @param string $name
     * @param mixed $definition
     * @param array $foreignKeys
     * @param array $indexes
     * @param array $uniques
     * @param string $path
     * @return void
     * @throws DOMException
     */
    private function appendColumn(DOMDocument $document, DOMElement $table, string $name, mixed $definition, array &$foreignKeys, array &$indexes, array &$uniques, string $path): void
    {
        $this->assertElementName($name, $path);
        $column = $document->createElement('column');
        $column->setAttribute('name', $name);
        $table->appendChild($column);

        $attributes = $this->columnDefinitionToAttributes($name, $definition, "$path.$name");
        $foreignKey = $this->extractColumnForeignKey($attributes, $name);
        $index = $attributes['index'] ?? null;
        unset($attributes['index']);

        $this->setAttributes($column, $attributes, "$path.$name");

        if ($foreignKey !== null) {
            $foreignKeys[] = [$foreignKey];
        }
        if ($index === true || $index === 'true') {
            $indexes[] = [$name . '_index' => [$name]];
        } elseif ($index === 'unique') {
            $uniques[] = [$name . '_unique' => [$name]];
        }
    }

    /**
     * @param string $name
     * @param mixed $definition
     * @param string $path
     * @return array|string[]
     */
    private function columnDefinitionToAttributes(string $name, mixed $definition, string $path): array
    {
        if (null === $definition) {
            return $this->inferColumnAttributes($name);
        }
        if (is_string($definition)) {
            return $this->parseColumnType($definition, $path);
        }
        if (!is_array($definition) || array_is_list($definition)) {
            throw new InvalidArgumentException("Column \"$path\" must be null, a type string, or a YAML mapping.");
        }

        $attributes = $definition;
        if (isset($attributes['type']) && is_string($attributes['type'])) {
            $attributes = array_replace($attributes, $this->parseColumnType($attributes['type'], "$path.type"));
        }
        if (array_key_exists('default', $attributes)) {
            $attributes['defaultValue'] = $attributes['default'];
            unset($attributes['default']);
        }
        return $attributes;
    }

    /**
     * @param string $name
     * @return array|string[]
     */
    private function inferColumnAttributes(string $name): array
    {
        if ($name === 'id') {
            return ['type' => 'integer', 'required' => true, 'primaryKey' => true, 'autoIncrement' => true];
        }
        if (str_ends_with($name, '_id')) {
            return [
                'type' => 'integer',
                'foreignTable' => substr($name, 0, -3),
                'foreignReference' => 'id',
            ];
        }
        if (in_array($name, ['created_at', 'updated_at', 'created_on', 'updated_on'], true)) {
            return ['type' => 'timestamp'];
        }

        throw new InvalidArgumentException("Column \"$name\" has no type. Propel 2 cannot infer this empty Propel 1 column.");
    }

    /**
     * @param string $type
     * @param string $path
     * @return array
     */
    private function parseColumnType(string $type, string $path): array
    {
        if (!preg_match('/^([^()]+?)(?:\\(([^,()]+)(?:,([^()]+))?\\))?$/', $type, $matches)) {
            throw new InvalidArgumentException("Invalid column type \"$type\" at \"$path\".");
        }

        $attributes = ['type' => trim($matches[1])];
        if (isset($matches[2])) {
            $attributes['size'] = trim($matches[2]);
        }
        if (isset($matches[3])) {
            $attributes['scale'] = trim($matches[3]);
        }

        return $attributes;
    }

    /**
     * @param array $attributes
     * @param string $column
     * @return array|null
     */
    private function extractColumnForeignKey(array &$attributes, string $column): ?array
    {
        if (!isset($attributes['foreignTable'])) {
            return null;
        }

        $foreignKey = ['foreignTable' => $attributes['foreignTable']];
        unset($attributes['foreignTable']);

        foreach (['onDelete', 'onUpdate', 'refPhpName'] as $attribute) {
            if (array_key_exists($attribute, $attributes)) {
                $foreignKey[$attribute] = $attributes[$attribute];
                unset($attributes[$attribute]);
            }
        }
        foreach (['fkPhpName' => 'phpName', 'fkSkipSql' => 'skipSql'] as $legacyName => $xmlName) {
            if (array_key_exists($legacyName, $attributes)) {
                $foreignKey[$xmlName] = $attributes[$legacyName];
                unset($attributes[$legacyName]);
            }
        }

        $foreignReference = $attributes['foreignReference'] ?? 'id';
        unset($attributes['foreignReference']);
        $foreignKey['references'] = [['local' => $column, 'foreign' => $foreignReference]];

        return $foreignKey;
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $table
     * @param mixed $definitions
     * @param string $path
     * @return void
     * @throws DOMException
     */
    private function appendForeignKeys(DOMDocument $document, DOMElement $table, mixed $definitions, string $path): void
    {
        foreach ($this->yamlEntries($definitions, $path) as $name => $definition) {
            if (!is_array($definition) || array_is_list($definition)) {
                throw new InvalidArgumentException("Foreign key \"$path\" must be a YAML mapping.");
            }

            $foreignKey = $document->createElement('foreign-key');
            $table->appendChild($foreignKey);
            if (is_string($name)) {
                $definition['name'] ??= $name;
            }

            $references = $definition['references'] ?? [];
            unset($definition['references']);
            foreach (['fkPhpName' => 'phpName', 'fkSkipSql' => 'skipSql'] as $legacyName => $xmlName) {
                if (array_key_exists($legacyName, $definition)) {
                    $definition[$xmlName] = $definition[$legacyName];
                    unset($definition[$legacyName]);
                }
            }
            $this->setAttributes($foreignKey, $definition, "$path.$name");

            foreach ($this->yamlEntries($references, "$path.$name.references") as $reference) {
                if (!is_array($reference) || array_is_list($reference)) {
                    throw new InvalidArgumentException("Foreign key reference \"$path\" must be a YAML mapping.");
                }
                $element = $document->createElement('reference');
                $foreignKey->appendChild($element);
                $this->setAttributes($element, $reference, "$path.$name.references");
            }
        }
    }

    /**
     * @param mixed $entries
     * @param string $path
     * @return iterable
     */
    private function yamlEntries(mixed $entries, string $path): iterable
    {
        if (!is_array($entries)) {
            throw new InvalidArgumentException("Value at \"$path\" must be a YAML list or mapping.");
        }

        return $entries;
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $table
     * @param mixed $definitions
     * @param string $elementName
     * @param string $path
     * @return void
     * @throws DOMException
     */
    private function appendIndexes(DOMDocument $document, DOMElement $table, mixed $definitions, string $elementName, string $path): void
    {
        if (!is_array($definitions) || array_is_list($definitions)) {
            throw new InvalidArgumentException("Indexes at \"$path\" must be a YAML mapping.");
        }

        foreach ($definitions as $name => $columns) {
            if (!is_string($name)) {
                throw new InvalidArgumentException("Index names at \"$path\" must be strings.");
            }

            $index = $document->createElement($elementName);
            $index->setAttribute('name', $name);
            $table->appendChild($index);
            $columns = is_array($columns) && array_is_list($columns) ? $columns : [$columns];
            foreach ($columns as $column) {
                $indexColumn = $document->createElement('index-column');
                $index->appendChild($indexColumn);

                if (is_string($column)) {
                    $this->setAttributes($indexColumn, $this->parseIndexColumn($column, "$path.$name"), "$path.$name");
                    continue;
                }
                if (!is_array($column) || array_is_list($column)) {
                    throw new InvalidArgumentException("Index column \"$path\" must be a string or a YAML mapping.");
                }
                $this->setAttributes($indexColumn, $column, "$path.$name");
            }
        }
    }

    /**
     * @param string $column
     * @param string $path
     * @return array
     */
    private function parseIndexColumn(string $column, string $path): array
    {
        if (!preg_match('/^([^()]+?)(?:\\(([^()]+)\\))?$/', $column, $matches)) {
            throw new InvalidArgumentException("Invalid index column \"$column\" at \"$path\".");
        }

        $attributes = ['name' => trim($matches[1])];
        if (isset($matches[2])) {
            $attributes['size'] = trim($matches[2]);
        }

        return $attributes;
    }

    /**
     * @param string $source
     * @return string
     */
    public function getDefaultTarget(string $source): string
    {
        $format = $this->getFormat($source);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $filename = substr($source, 0, -strlen($extension) - 1);

        return $filename . (($format === self::FORMAT_XML) ? '.yaml' : '.xml');
    }
}
