<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Command;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Command\SchemaConvertCommand;
use Propel\Bundle\PropelBundle\Service\SchemaConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SchemaConvertCommandTest extends TestCase
{
    public function testConvertsToTheSiblingYamlFileByDefault(): void
    {
        $root = vfsStream::setup('project', null, [
            'schema.xml' => '<database name="default" defaultIdMethod="native" />',
        ]);
        $source = $root->url() . '/schema.xml';
        $target = $root->url() . '/schema.yaml';
        $tester = new CommandTester(new SchemaConvertCommand(new SchemaConverter()));

        $exitCode = $tester->execute(['source' => $source]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertFileExists($target);
        $this->assertStringContainsString('default:', file_get_contents($target));
        $this->assertStringContainsString($target, $tester->getDisplay());
    }

    public function testDoesNotOverwriteTheTargetWithoutForce(): void
    {
        $root = vfsStream::setup('project', null, [
            'schema.xml' => '<database name="default" defaultIdMethod="native" />',
            'schema.yaml' => 'database: {}',
        ]);
        $tester = new CommandTester(new SchemaConvertCommand(new SchemaConverter()));

        $exitCode = $tester->execute(['source' => $root->url() . '/schema.xml']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Use --force to overwrite it', $tester->getDisplay());
    }
}
