<?php

namespace Propel\Bundle\PropelBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Twig\Extension\SyntaxExtension;

/**
 * # SyntaxExtensionTest
 */
class SyntaxExtensionTest extends TestCase
{
    /**
     * @return void
     */
    public function testFilters(): void
    {
        $extension = new SyntaxExtension();

        $this->assertSame('propel_syntax_extension', $extension->getName());
        $filters = $extension->getFilters();
        $this->assertCount(2, $filters);
        $this->assertSame('format_sql', $filters[0]->getName());
        $this->assertSame('format_memory', $filters[1]->getName());
    }

    /**
     * @return void
     */
    public function testMemory(): void
    {
        $extension = new SyntaxExtension();

        $this->assertSame('0 B', $extension->formatMemory(0));
        $this->assertSame('1.000 B', $extension->formatMemory(1));
        $this->assertSame('2.00 kB', $extension->formatMemory(2048));
        $this->assertSame('-2.00 kB', $extension->formatMemory(-2048));
    }

    /**
     * @return void
     */
    public function testSql(): void
    {
        $extension = new SyntaxExtension();
        $formatted = $extension->formatSQL('SELECT `book`.`id` FROM book WHERE id = 1 /* comment */');

        $this->assertStringContainsString('<span class="SQLKeyword">SELECT</span>', $formatted);
        $this->assertStringContainsString('<br /><span class="SQLKeyword">FROM</span>', $formatted);
        $this->assertStringContainsString('<span class="SQLName">`book`</span>', $formatted);
        $this->assertStringContainsString('<span class="SQLComment">/* comment */</span>', $formatted);
    }

    /**
     * @return void
     */
    public function testSqlArray(): void
    {
        $formatted = (new SyntaxExtension())->formatSQL(['SELECT 1', 'DELETE FROM book']);

        $this->assertCount(2, $formatted);
        $this->assertStringContainsString('<span class="SQLKeyword">SELECT</span>', $formatted[0]);
        $this->assertStringContainsString('<span class="SQLKeyword">DELETE</span>', $formatted[1]);
    }

    /**
     * @return void
     */
    public function testPrecision(): void
    {
        $this->assertSame('0', SyntaxExtension::toPrecision(0));
        $this->assertSame('1.23', SyntaxExtension::toPrecision(1.23456));
    }
}
