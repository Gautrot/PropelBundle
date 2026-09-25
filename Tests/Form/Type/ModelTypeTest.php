<?php

namespace Propel\Bundle\PropelBundle\Tests\Form\Type;

use PDO;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Form\ChoiceList\PropelChoiceLoader;
use Propel\Bundle\PropelBundle\Form\DataTransformer\CollectionToArrayTransformer;
use Propel\Bundle\PropelBundle\Form\Type\ModelType;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * # ModelTypeTest
 */
class ModelTypeTest extends TestCase
{
    /**
     * @return void
     */
    public function testOptions(): void
    {
        $type = new ModelType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $query = $this->query(PDO::PARAM_INT);

        $options = $resolver->resolve([
            'class' => ModelTypeChoice::class,
            'query' => $query,
            'property' => 'name',
        ]);

        $this->assertSame('model', $type->getBlockPrefix());
        $this->assertSame(ChoiceType::class, $type->getParent());
        $this->assertSame([ModelType::class, 'createChoiceName'], $options['choice_name']);
        $this->assertSame(false, $options['choice_translation_domain']);
        $this->assertFalse($options['by_reference']);
        $this->assertInstanceOf(PropelChoiceLoader::class, $options['choice_loader']);

        $book = new ModelTypeChoice(42, 'A book');

        $this->assertSame('A_book', ModelType::createChoiceName($book, 'ignored', 'A-book'));
        $this->assertSame([0 => '42'], $options['choice_loader']->loadValuesForChoices([$book]));
        $this->assertSame([], $options['choice_loader']->loadChoicesForValues([]));
        $this->assertSame('A book', $options['choice_label']($book));
        $this->assertSame(42, $options['choice_value']($book));
        $this->assertNull($options['choice_value'](null));
    }

    /**
     * @param int $pdoType
     * @return ModelCriteria
     * @throws Exception
     */
    private function query(int $pdoType): ModelCriteria
    {
        $column = $this->createMock(ColumnMap::class);
        $column->method('getPdoType')->willReturn($pdoType);
        $column->method('getPhpName')->willReturn('Id');

        $tableMap = $this->createMock(TableMap::class);
        $tableMap->method('getPrimaryKeys')->willReturn([$column]);

        $query = $this->getMockBuilder(ModelCriteria::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTableMap'])
            ->getMock();
        $query->method('getTableMap')->willReturn($tableMap);

        return $query;
    }

    /**
     * @return void
     */
    public function testChoices(): void
    {
        $resolver = new OptionsResolver();
        (new ModelType())->configureOptions($resolver);

        $options = $resolver->resolve([
            'class' => ModelTypeChoice::class,
            'query' => $this->query(PDO::PARAM_INT),
            'choices' => [],
        ]);

        $this->assertNull($options['choice_loader']);
    }

    /**
     * @return void
     */
    public function testQueryErrors(): void
    {
        $resolver = new OptionsResolver();
        (new ModelType())->configureOptions($resolver);

        try {
            $resolver->resolve(['class' => '']);
            $this->fail('An empty class name must be rejected.');
        } catch (MissingOptionsException $exception) {
            $this->assertStringContainsString('class', $exception->getMessage());
        }

        $this->expectException(InvalidOptionsException::class);
        $resolver->resolve(['class' => 'App\\Missing\\Model']);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testTransformer(): void
    {
        $type = new ModelType();
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->with($this->isInstanceOf(CollectionToArrayTransformer::class), true);

        $type->buildForm($builder, ['multiple' => true]);

        $singleChoiceBuilder = $this->createMock(FormBuilderInterface::class);
        $singleChoiceBuilder->expects($this->never())->method('addViewTransformer');
        $type->buildForm($singleChoiceBuilder, ['multiple' => false]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testFactory(): void
    {
        $factory = $this->createMock(ChoiceListFactoryInterface::class);
        $type = new ModelType(null, $factory);

        $this->assertSame('model', $type->getBlockPrefix());
    }
}

/**
 * # ModelTypeChoice
 */
readonly class ModelTypeChoice
{
    /**
     * @param int $id
     * @param string $name
     */
    public function __construct(private int $id, private string $name)
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
