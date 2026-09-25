<?php

namespace Propel\Bundle\PropelBundle\Tests\DependencyInjection\Security\UserProvider;

use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\DependencyInjection\Security\UserProvider\PropelFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * # PropelFactoryTest
 */
class PropelFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testKey(): void
    {
        $factory = new PropelFactory('propel', 'propel.security.user.provider');
        $factory->addConfiguration((new TreeBuilder('propel'))->getRootNode());

        $this->assertSame('propel', $factory->getKey());
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $container = new ContainerBuilder();
        $factory = new PropelFactory('propel', 'propel.security.user.provider');

        $factory->create($container, 'app.user_provider', [
            'class' => 'App\\Entity\\User',
            'property' => 'email',
        ]);

        $definition = $container->getDefinition('app.user_provider');
        $this->assertInstanceOf(ChildDefinition::class, $definition);
        $this->assertSame('propel.security.user.provider', $definition->getParent());
        $this->assertSame(['App\\Entity\\User', 'email'], $definition->getArguments());
    }
}
