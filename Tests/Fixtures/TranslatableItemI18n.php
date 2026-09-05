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

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Connection\ConnectionInterface;

class TranslatableItemI18n implements ActiveRecordInterface
{
    private mixed $id;

    private mixed $locale;

    private mixed $value;

    private mixed $value2;

    private mixed $item;

    public function __construct($id = null, $locale = null, $value = null)
    {
        $this->id = $id;
        $this->locale = $locale;
        $this->value = $value;
    }

    public function getPrimaryKey()
    {
        return $this->getId();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setPrimaryKey($primaryKey): void
    {
        $this->setId($primaryKey);
    }

    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getId();
    }

    public function isModified(): bool
    {
        return false;
    }

    public function isColumnModified($col): bool
    {
        return false;
    }

    public function isNew(): bool
    {
        return false;
    }

    public function setNew($b)
    {
    }

    public function resetModified()
    {
    }

    public function isDeleted(): bool
    {
        return false;
    }

    public function setDeleted($b)
    {
    }

    public function delete(?ConnectionInterface $con = null)
    {
    }

    public function save(?ConnectionInterface $con = null)
    {
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function setItem($item): void
    {
        $this->item = $item;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getValue2()
    {
        return $this->value2;
    }

    public function setValue2($value2): void
    {
        $this->value2 = $value2;
    }
}
