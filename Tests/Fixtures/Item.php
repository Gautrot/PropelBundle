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

class Item implements ActiveRecordInterface
{
    private mixed $id;

    private mixed $value;

    private mixed $groupName;

    private mixed $price;

    public function __construct($id = null, $value = null, $groupName = null, $price = null)
    {
        $this->id = $id;
        $this->value = $value;
        $this->groupName = $groupName;
        $this->price = $price;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }

    public function getPrice()
    {
        return $this->price;
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
}
