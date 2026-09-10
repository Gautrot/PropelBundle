<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Bundle\PropelBundle\Tests\Form;

use Propel\Bundle\PropelBundle\Form\Type\ModelType;
use Propel\Bundle\PropelBundle\Form\TypeGuesser;
use Propel\Bundle\PropelBundle\Tests\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;

/**
 * # TypeGuesserTest
 */
class TypeGuesserTest extends TestCase
{
    /**
     *
     */
    const CLASS_NAME = 'Propel\Bundle\PropelBundle\Tests\Fixtures\Item';

    /**
     *
     */
    const UNKNOWN_CLASS_NAME = 'Propel\Bundle\PropelBundle\Tests\Fixtures\UnknownItem';

    /**
     * @var TypeGuesser
     */
    private TypeGuesser $guesser;

    /**
     * @return array[]
     */
    public static function dataProviderForGuessType(): array
    {
        return [
            ['is_active', CheckboxType::class, Guess::HIGH_CONFIDENCE],
            ['enabled', CheckboxType::class, Guess::HIGH_CONFIDENCE],
            ['id', IntegerType::class, Guess::MEDIUM_CONFIDENCE],
            ['value', TextType::class, Guess::MEDIUM_CONFIDENCE],
            ['price', NumberType::class, Guess::MEDIUM_CONFIDENCE],
            ['updated_at', DateTimeType::class, Guess::HIGH_CONFIDENCE],

            ['Authors', ModelType::class, Guess::HIGH_CONFIDENCE, true],
            ['Resellers', ModelType::class, Guess::HIGH_CONFIDENCE, true],
            ['MainAuthor', ModelType::class, Guess::HIGH_CONFIDENCE, false],
        ];
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->guesser = new TypeGuesser();
    }

    /**
     * @return void
     */
    public function testGuessMaxLengthWithText()
    {
        $value = $this->guesser->guessMaxLength(self::CLASS_NAME, 'value');

        $this->assertNotNull($value);
        $this->assertEquals(255, $value->getValue());
    }

    /**
     * @return void
     */
    public function testGuessMaxLengthWithFloat()
    {
        $value = $this->guesser->guessMaxLength(self::CLASS_NAME, 'price');

        $this->assertNotNull($value);
        $this->assertNull($value->getValue());
    }

    /**
     * @return void
     */
    public function testGuessMinLengthWithText()
    {
        $value = $this->guesser->guessPattern(self::CLASS_NAME, 'value');

        $this->assertNull($value);
    }

    /**
     * @return void
     */
    public function testGuessMinLengthWithFloat()
    {
        $value = $this->guesser->guessPattern(self::CLASS_NAME, 'price');

        $this->assertNotNull($value);
        $this->assertNull($value->getValue());
    }

    /**
     * @return void
     */
    public function testGuessRequired()
    {
        $value = $this->guesser->guessRequired(self::CLASS_NAME, 'id');

        $this->assertNotNull($value);
        $this->assertTrue($value->getValue());
    }

    /**
     * @return void
     */
    public function testGuessRequiredWithNullableColumn()
    {
        $value = $this->guesser->guessRequired(self::CLASS_NAME, 'value');

        $this->assertNotNull($value);
        $this->assertFalse($value->getValue());
    }

    /**
     * @return void
     */
    public function testGuessTypeWithoutTable()
    {
        $value = $this->guesser->guessType(self::UNKNOWN_CLASS_NAME, 'property');

        $this->assertNotNull($value);
        $this->assertEquals(TextType::class, $value->getType());
        $this->assertEquals(Guess::LOW_CONFIDENCE, $value->getConfidence());
    }

    /**
     * @return void
     */
    public function testGuessTypeWithoutColumn()
    {
        $value = $this->guesser->guessType(self::CLASS_NAME, 'property');

        $this->assertNotNull($value);
        $this->assertEquals(TextType::class, $value->getType());
        $this->assertEquals(Guess::LOW_CONFIDENCE, $value->getConfidence());
    }

    /**
     * @param $property
     * @param $type
     * @param $confidence
     * @param $multiple
     * @return void
     */
    #[DataProvider('dataProviderForGuessType')]
    public function testGuessType($property, $type, $confidence, $multiple = null)
    {
        $value = $this->guesser->guessType(self::CLASS_NAME, $property);

        $this->assertNotNull($value);
        $this->assertEquals($type, $value->getType());
        $this->assertEquals($confidence, $value->getConfidence());

        if ($type === ModelType::class) {
            $options = $value->getOptions();

            $this->assertSame($multiple, $options['multiple']);
        }
    }
}
