<?php

namespace Propel\Bundle\PropelBundle\Tests\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Validator\Constraints\UniqueObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * # UniqueObjectTest
 */
class UniqueObjectTest extends TestCase
{
    /**
     * @return void
     */
    public function testStringField(): void
    {
        $constraint = new UniqueObject([
            'fields' => 'email',
            'errorPath' => 'email',
            'messageFieldSeparator' => ', ',
        ]);

        $this->assertSame('email', $constraint->fields);
        $this->assertSame('email', $constraint->errorPath);
        $this->assertSame(', ', $constraint->messageFieldSeparator);
        $this->assertSame(['fields'], $constraint->getRequiredOptions());
        $this->assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }

    /**
     * @return void
     */
    public function testFields(): void
    {
        $constraint = new UniqueObject(['fields' => ['email', 'username']]);

        $this->assertSame(['email', 'username'], $constraint->fields);
    }

    /**
     * @return void
     */
    public function testEmpty(): void
    {
        $this->expectException(ConstraintDefinitionException::class);

        new UniqueObject(['fields' => []]);
    }
}
