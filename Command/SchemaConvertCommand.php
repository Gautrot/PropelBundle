<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Command;

use Propel\Bundle\PropelBundle\Service\SchemaConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * # SchemaConvertCommand
 */
class SchemaConvertCommand extends Command
{
    /**
     * @param SchemaConverter $schemaConverter
     * @param string|null $name
     */
    public function __construct(
        private readonly SchemaConverter $schemaConverter,
        ?string                          $name = null,
    )
    {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('propel:schema:convert')
            ->setDescription('Convert a Propel schema to either XML and YAML format')
            ->addArgument('source', InputArgument::REQUIRED, 'The schema to convert')
            ->addArgument('target', InputArgument::OPTIONAL, 'The converted schema file. A sibling file is created by default')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite the target schema if it already exists');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = $input->getArgument('source');

        if (!is_string($source) || !is_file($source)) {
            $output->writeln("<error>Schema \"$source\" does not exist.</error>");

            return Command::INVALID;
        }

        $target = $input->getArgument('target');
        if (!is_string($target)) {
            $target = $this->schemaConverter->getDefaultTarget($source);
        }

        if (is_file($target) && !$input->getOption('force')) {
            $output->writeln("<error>Target schema \"$target\" already exists. Use --force to overwrite it.</error>");

            return Command::FAILURE;
        }

        try {
            $targetFormat = $this->schemaConverter->getFormat($target);
            $convertedSchema = $this->schemaConverter->convert($source, $targetFormat);

            (new Filesystem())->dumpFile($target, $convertedSchema);
        } catch (Throwable $exception) {
            $output->writeln("<error>{$exception->getMessage()}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Converted $source to $target.</info>");

        return Command::SUCCESS;
    }
}
