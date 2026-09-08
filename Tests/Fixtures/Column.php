<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Bundle\PropelBundle\Tests\Fixtures;

use Propel\Generator\Model\PropelTypes;

/**
 * # Column
 */
class Column
{
    /**
     * @var mixed
     */
    private mixed $name;

    /**
     * @var mixed
     */
    private mixed $type;

    /**
     * @param $name
     * @param $type
     */
    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->isText() ? 255 : 0;
    }

    /**
     * @return bool
     */
    public function isText(): bool
    {
        if (!$this->type) {
            return false;
        }

        return match ($this->type) {
            PropelTypes::CHAR, PropelTypes::VARCHAR, PropelTypes::LONGVARCHAR, PropelTypes::BLOB, PropelTypes::CLOB, PropelTypes::CLOB_EMU => true,
            default => false,
        };

    }

    /**
     * @return bool
     */
    public function isNotNull(): bool
    {
        return ('id' === $this->name);
    }
}
