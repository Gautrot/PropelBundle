<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Propel\Runtime\Propel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * CaseTest
 */
abstract class CaseTest extends BaseTestCase
{
    public function getContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.project_dir' => __DIR__ . '/../',
        ]));

        $container->setParameter('propel.configuration', []);
        $container->setDefinition('propel', new Definition('Propel\Runtime\Propel'));

        return $container;
    }

    /**
     * load propel database maps
     * @param array $databaseMapsArray
     */
    protected function loadDatabaseMap(array $databaseMapsArray): void
    {
        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->initDatabaseMaps([
            'default' => $databaseMapsArray
        ]);
    }
}
