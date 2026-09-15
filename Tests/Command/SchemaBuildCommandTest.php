<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Command;

use DOMException;
use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Command\AbstractCommand;
use Propel\Bundle\PropelBundle\Service\SchemaConverter;
use Random\RandomException;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class SchemaBuildCommandTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testBuildsXmlCacheFromYamlSchema(): void
    {
        $yaml = <<<YAML
default:
  _attributes:
    defaultIdMethod: native
    package: App
  book:
    id:
      type: integer
      primaryKey: true
YAML;
        $filesystem = new Filesystem();
        $root = sys_get_temp_dir() . '/propel-bundle-' . bin2hex(random_bytes(8));
        $schemaFile = "$root/schema.yaml";
        $cacheDir = "$root/cache";
        $filesystem->mkdir($cacheDir);
        $filesystem->dumpFile($schemaFile, $yaml);
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getCacheDir')->willReturn($cacheDir);

        $container = new ContainerBuilder();
        $container->set('kernel', $kernel);
        $container->set('propel.schema_converter', new SchemaConverter());

        $command = new SchemaCacheBuildCommand($container, $kernel, new SplFileInfo($schemaFile), $cacheDir);

        $exitCode = (new CommandTester($command))->execute([]);
        $cachedSchema = "$cacheDir/app-schema.xml";

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertFileExists($cachedSchema);
        $this->assertXmlStringEqualsXmlString(
            <<<XML
<database name="default" defaultIdMethod="native" package="App">
<table name="book" package="App">
    <column name="id" type="integer" primaryKey="true" />
</table>
</database>
XML,
            file_get_contents($cachedSchema)
        );

        $filesystem->remove($root);
    }
}

final class SchemaCacheBuildCommand extends AbstractCommand
{
    /**
     * @param ContainerBuilder $container
     * @param KernelInterface $kernel
     * @param SplFileInfo $schema
     * @param string $schemaCacheDir
     */
    public function __construct(
        ContainerBuilder                 $container,
        private readonly KernelInterface $kernel,
        private readonly SplFileInfo     $schema,
        private readonly string          $schemaCacheDir,
    )
    {
        parent::__construct($container);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DOMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->copySchemas($this->kernel, $this->schemaCacheDir);

        return Command::SUCCESS;
    }

    /**
     * @param KernelInterface $kernel
     * @param BundleInterface|null $bundle
     * @return array
     */
    protected function getFinalSchemas(KernelInterface $kernel, ?BundleInterface $bundle = null): array
    {
        return [[null, $this->schema]];
    }

    /**
     * @return KernelInterface
     */
    protected function getKernel(): KernelInterface
    {
        return $this->kernel;
    }
}
