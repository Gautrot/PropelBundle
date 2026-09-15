<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Service;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use Propel\Bundle\PropelBundle\Service\SchemaLocator;
use Propel\Bundle\PropelBundle\Tests\Fixtures\FakeBundle\FakeBundle;
use Propel\Bundle\PropelBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;

/**
 * # SchemaLocatorTest
 */
class SchemaLocatorTest extends TestCase
{
    /**
     * container generated for the tests
     * @var ContainerBuilder
     */
    private ContainerBuilder $container;

    /**
     * @var array
     */
    private array $configuration;

    /**
     * @var FileLocator
     */
    private FileLocator $fileLocator;

    /**
     * @var MockObject|FakeBundle
     */
    private MockObject|FakeBundle $bundleMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $pathStructure = [
            'configuration' => [
                'directory' => [
                    'schema.xml' => 'XML schema from configuration',
                    'schema.yaml' => 'YAML schema from configuration',
                    'schema.yml' => 'YAML schema from configuration',
                ]
            ],
        ];
        $root = vfsStream::setup('projectDir');
        vfsStream::create($pathStructure);

        $kernelMock = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $kernelMock->method('getProjectDir')->willReturn($root->url());
        $kernelMock->method('locateResource')->willReturnCallback(function ($argument) {
            return (str_replace('@', __DIR__ . '/../Fixtures/', $argument));
        });

        // attach kernel service to container
        $this->container = $this->getContainer();
        $this->container->set('kernel', $kernelMock);

        $this->bundleMock = new FakeBundle();

        $this->configuration['paths']['schemaDir'] = vfsStream::url('projectDir/configuration/directory');
        $this->fileLocator = new FileLocator($kernelMock);
    }

    /**
     * @return void
     */
    public function testLocateFromBundle()
    {
        $locator = new SchemaLocator($this->container, $this->fileLocator, $this->configuration);
        $files = $locator->locateFromBundle($this->bundleMock);

        $this->assertCount(2, $files);
        $path = '/../Fixtures/FakeBundle/Resources/config';
        $fileName = 'bundle.schema';

        $this->assertTrue(isset($files[__DIR__ . "$path/$fileName.xml"]));
        $this->assertEquals("$fileName.xml", $files[__DIR__ . "$path/$fileName.xml"][1]->getFileName());
        $this->assertTrue(isset($files[__DIR__ . "$path/$fileName.yaml"]));
        $this->assertEquals("$fileName.yaml", $files[__DIR__ . "$path/$fileName.yaml"][1]->getFileName());
    }

    /**
     * @return void
     */
    public function testLocateFromBundlesAndConfiguration()
    {
        $locator = new SchemaLocator($this->container, $this->fileLocator, $this->configuration);
        $files = $locator->locateFromBundlesAndConfiguration(
            [$this->bundleMock]
        );

        $this->assertCount(5, $files);
        $path = '/../Fixtures/FakeBundle/Resources/config';
        $vfsPath = 'vfs://projectDir/configuration/directory';
        $fileName = 'bundle.schema';
        $vfsFileName = 'schema';

        // XML
        $this->assertTrue(isset($files[__DIR__ . "$path/$fileName.xml"]));
        $this->assertEquals("$fileName.xml", $files[__DIR__ . "$path/$fileName.xml"][1]->getFileName());
        $this->assertTrue(isset($files["$vfsPath/$vfsFileName.xml"]));
        $this->assertEquals("$vfsFileName.xml", $files["$vfsPath/$vfsFileName.xml"][1]->getFileName());

        // YAML / YML
        $this->assertTrue(isset($files[__DIR__ . "$path/$fileName.yaml"]));
        $this->assertEquals("$fileName.yaml", $files[__DIR__ . "$path/$fileName.yaml"][1]->getFileName());
        $this->assertTrue(isset($files["$vfsPath/$vfsFileName.yaml"]));
        $this->assertEquals("$vfsFileName.yaml", $files["$vfsPath/$vfsFileName.yaml"][1]->getFileName());
        $this->assertTrue(isset($files["$vfsPath/$vfsFileName.yml"]));
        $this->assertEquals("$vfsFileName.yml", $files["$vfsPath/$vfsFileName.yml"][1]->getFileName());
    }
}
