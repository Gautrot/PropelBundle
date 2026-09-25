<?php

namespace Propel\Bundle\PropelBundle\Tests\Form\Type;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Form\EventListener\TranslationFormListener;
use Propel\Bundle\PropelBundle\Form\Type\TranslationType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * # TranslationTypeTest
 */
class TranslationTypeTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testBuild(): void
    {
        $type = new TranslationType();
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->callback(static fn($listener): bool => $listener instanceof TranslationFormListener));

        $type->buildForm($builder, [
            'columns' => ['title' => 'title'],
            'data_class' => 'App\\Entity\\Translation',
        ]);
    }

    /**
     * @return void
     */
    public function testOptions(): void
    {
        $type = new TranslationType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $options = $resolver->resolve([
            'data_class' => 'App\\Entity\\Translation',
            'columns' => ['title'],
        ]);

        $this->assertSame('App\\Entity\\Translation', $options['data_class']);
        $this->assertSame(['title'], $options['columns']);
        $this->assertSame('propel_translation', $type->getBlockPrefix());
        $this->assertSame('propel_translation', $type->getName());

        $this->expectException(MissingOptionsException::class);
        $resolver->resolve(['data_class' => 'App\\Entity\\Translation']);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testFields(): void
    {
        $data = new class {
            public function getLocale(): string
            {
                return 'fr';
            }
        };
        $form = $this->createMock(FormInterface::class);
        $addedFields = [];
        $form->method('add')
            ->willReturnCallback(function (string $name, ?string $type, array $options) use (&$addedFields, $form): FormInterface {
            $addedFields[] = [$name, $type, $options];

            return $form;
        });

        $listener = new TranslationFormListener([
            'summary' => null,
            'headline' => 'title',
            'details' => [
                'type' => TextareaType::class,
                'label' => 'Description',
                'options' => ['required' => false],
            ],
        ], $data::class);

        $listener->preSetData(new FormEvent($form, $data));

        $arrayCase = [
            ['summary', TextType::class, ['label' => 'summary FR']],
            ['title', TextType::class, ['label' => 'title FR']],
            ['details', TextareaType::class, [
                'label' => 'Description FR',
                'required' => false
            ]],
        ];
        $this->assertSame($arrayCase, $addedFields);
        $presetCase = [FormEvents::PRE_SET_DATA => ['preSetData', 1]];
        $this->assertSame($presetCase, TranslationFormListener::getSubscribedEvents());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testOtherData(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('add');

        (new TranslationFormListener(['title' => 'title'], 'stdClass'))
            ->preSetData(new FormEvent($form, null));
    }
}
