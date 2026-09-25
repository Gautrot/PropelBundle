<?php

namespace Propel\Bundle\PropelBundle\Tests\Form;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Form\FormBuilder;
use Propel\Generator\Model\Column;
use Propel\Generator\Model\ForeignKey;
use Propel\Generator\Model\Table;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * # FormBuilderTest
 */
class FormBuilderTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testBuild(): void
    {
        $primaryKey = $this->createMock(Column::class);
        $primaryKey->method('isPrimaryKey')->willReturn(true);

        $title = $this->createMock(Column::class);
        $title->method('isPrimaryKey')->willReturn(false);
        $title->method('isForeignKey')->willReturn(false);
        $title->method('getPhpName')->willReturn('Title');

        $author = $this->createMock(Column::class);
        $author->method('isPrimaryKey')->willReturn(false);
        $author->method('isForeignKey')->willReturn(true);
        $foreignTable = $this->createMock(Table::class);
        $foreignTable->method('getPhpName')->willReturn('Author');
        $foreignKey = $this->createMock(ForeignKey::class);
        $foreignKey->method('getForeignTable')->willReturn($foreignTable);
        $author->method('getForeignKeys')->willReturn([$foreignKey]);

        $table = $this->createMock(Table::class);
        $table->method('getPhpName')->willReturn('Book');
        $table->method('getNamespace')->willReturn('App\\Model');
        $table->method('getColumns')->willReturn([$primaryKey, $title, $author]);

        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getNamespace')->willReturn('App\\Bundle');

        $generated = (new FormBuilder())->buildFormType($bundle, $table, '/Form');

        $this->assertStringContainsString('namespace App\\Bundle\\Form;', $generated);
        $this->assertStringContainsString('class BookType extends AbstractType', $generated);
        $this->assertStringContainsString("\$builder->add('title');", $generated);
        $this->assertStringContainsString("\$builder->add('author');", $generated);
        $this->assertStringNotContainsString("\$builder->add('id');", $generated);
        $this->assertStringContainsString('App\\Model\\Book', $generated);
    }
}
