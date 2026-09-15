<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Service;

use DOMException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Service\SchemaConverter;
use Symfony\Component\Yaml\Yaml;

/**
 * # SchemaConverterTest
 */
class SchemaConverterTest extends TestCase
{
    private const XML_SCHEMA = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<database name="default" defaultIdMethod="native" namespace="App\Model"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:noNamespaceSchemaLocation="https://xsd.propelorm.org/1.6/database.xsd">
    <table name="book" phpName="Book">
        <column name="id" type="integer" primaryKey="true" autoIncrement="true" />
        <column name="title" type="varchar" size="255" />
        <foreign-key foreignTable="author">
            <reference local="author_id" foreign="id" />
        </foreign-key>
    </table>
</database>
XML;

    /**
     * @return void
     * @throws DOMException
     */
    public function testConvertXmlToYaml(): void
    {
        $root = vfsStream::setup('project', null, ['schema.xml' => self::XML_SCHEMA]);
        $xmlFile = "{$root->url()}/schema.xml";
        $converter = new SchemaConverter();

        $yaml = $converter->convert($xmlFile, SchemaConverter::FORMAT_YAML);
        $schema = Yaml::parse($yaml);

        $this->assertSame(
            [
                'defaultIdMethod' => 'native',
                'namespace' => 'App\Model',
                'xsi:noNamespaceSchemaLocation' => 'https://xsd.propelorm.org/1.6/database.xsd',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
            $schema['default']['_attributes']
        );
        $this->assertSame('Book', $schema['default']['book']['_attributes']['phpName']);
        $this->assertSame('integer', $schema['default']['book']['id']['type']);
        $this->assertTrue($schema['default']['book']['id']['primaryKey']);
        $this->assertSame('varchar(255)', $schema['default']['book']['title']);
        $this->assertSame(
            'author',
            $schema['default']['book']['_foreignKeys'][0]['foreignTable']
        );

        vfsStream::create(['schema.yaml' => $yaml], $root);
        $roundTripXml = $converter->convert($root->url() . '/schema.yaml', SchemaConverter::FORMAT_XML);

        $this->assertXmlStringEqualsXmlString(self::XML_SCHEMA, $roundTripXml);
    }

    /**
     * @return void
     */
    public function testDefaultTargetToNewFormat(): void
    {
        $converter = new SchemaConverter();

        $this->assertSame('schema.yaml', $converter->getDefaultTarget('schema.xml'));
        $this->assertSame('schema.xml', $converter->getDefaultTarget('schema.yaml'));
        $this->assertSame('schema.xml', $converter->getDefaultTarget('schema.yml'));
    }

    /**
     * @return void
     * @throws DOMException
     */
    public function testBoolAttrToValues(): void
    {
        $yaml = <<<YAML
default:
    _attributes:
        defaultIdMethod: native
    book:
        id:
            type: integer
            primaryKey: true
            autoIncrement: false
YAML;
        $root = vfsStream::setup('project', null, ['schema.yaml' => $yaml]);
        $converter = new SchemaConverter();

        $xml = $converter->convert($root->url() . '/schema.yaml', SchemaConverter::FORMAT_XML);

        $this->assertStringContainsString('primaryKey="true"', $xml);
        $this->assertStringContainsString('autoIncrement="false"', $xml);
    }

    /**
     * @return void
     * @throws DOMException
     */
    public function testConvertFKsIndexesAndBehaviors(): void
    {
        $yaml = <<<YAML
default:
    _attributes: { defaultIdMethod: native }
    book:
        id: { type: integer, primaryKey: true }
        title: varchar(255)
        author_id: integer
        _foreignKeys:
            book_author:
                foreignTable: author
                onDelete: cascade
                fkPhpName: Author
                references:
                    - { local: author_id, foreign: id }
        _indexes:
            book_title_author: [title(10), author_id]
        _uniques:
            book_title_unique: [title]
        _propel_behaviors:
            timestampable: { create_column: created_at }
YAML;
        $root = vfsStream::setup('project', null, ['schema.yaml' => $yaml]);
        $converter = new SchemaConverter();

        $xml = $converter->convert($root->url() . '/schema.yaml', SchemaConverter::FORMAT_XML);

        $this->assertStringContainsString('<foreign-key', $xml);
        $this->assertStringContainsString('name="book_author"', $xml);
        $this->assertStringContainsString('foreignTable="author"', $xml);
        $this->assertStringContainsString('onDelete="cascade"', $xml);
        $this->assertStringContainsString('phpName="Author"', $xml);
        $this->assertStringContainsString('<reference local="author_id" foreign="id"/>', $xml);
        $this->assertStringContainsString('<index name="book_title_author">', $xml);
        $this->assertStringContainsString('<index-column name="title" size="10"/>', $xml);
        $this->assertStringContainsString('<unique name="book_title_unique">', $xml);
        $this->assertStringContainsString('<behavior name="timestampable">', $xml);
        $this->assertStringContainsString('<parameter name="create_column" value="created_at"/>', $xml);
    }

    /**
     * @return void
     * @throws DOMException
     */
    public function testInferEmptyColumns(): void
    {
        $yaml = <<<YAML
default:
    book:
        id:
        author_id:
        created_at:
YAML;
        $root = vfsStream::setup('project', null, ['schema.yaml' => $yaml]);
        $converter = new SchemaConverter();

        $xml = $converter->convert($root->url() . '/schema.yaml', SchemaConverter::FORMAT_XML);

        $this->assertStringContainsString('<column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true"/>', $xml);
        $this->assertStringContainsString('<column name="author_id" type="integer"/>', $xml);
        $this->assertStringContainsString('<foreign-key foreignTable="author">', $xml);
        $this->assertStringContainsString('<reference local="author_id" foreign="id"/>', $xml);
        $this->assertStringContainsString('<column name="created_at" type="timestamp"/>', $xml);
    }
}
