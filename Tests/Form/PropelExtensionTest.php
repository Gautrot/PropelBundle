<?php

namespace Propel\Bundle\PropelBundle\Tests\Form;

use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Form\PropelExtension;
use Propel\Bundle\PropelBundle\Form\Type\ModelType;
use Propel\Bundle\PropelBundle\Form\Type\TranslationCollectionType;
use Propel\Bundle\PropelBundle\Form\Type\TranslationType;
use Propel\Bundle\PropelBundle\Form\TypeGuesser;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PropelExtensionTest extends TestCase
{
    public function testTypes(): void
    {
        $extension = new class extends PropelExtension {
            public function types(): array
            {
                return $this->loadTypes();
            }

            public function guesser(): ?FormTypeGuesserInterface
            {
                return $this->loadTypeGuesser();
            }
        };

        $types = $extension->types();

        $this->assertCount(3, $types);
        $this->assertInstanceOf(ModelType::class, $types[0]);
        $this->assertInstanceOf(TranslationCollectionType::class, $types[1]);
        $this->assertInstanceOf(TranslationType::class, $types[2]);
        $this->assertInstanceOf(TypeGuesser::class, $extension->guesser());
    }

    public function testFactories(): void
    {
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $choiceListFactory = $this->createMock(ChoiceListFactoryInterface::class);
        $extension = new class($propertyAccessor, $choiceListFactory) extends PropelExtension {
            public function types(): array
            {
                return $this->loadTypes();
            }
        };

        $this->assertInstanceOf(ModelType::class, $extension->types()[0]);
    }
}
