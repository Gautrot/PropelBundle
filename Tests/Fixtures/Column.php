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

class Column
{
    private mixed $name;

    private mixed $type;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->isText() ? 255 : 0;
    }

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

    public function isNotNull(): bool
    {
        return ('id' === $this->name);
    }
}
