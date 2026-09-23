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

/**
 * # Item
 */
class Item implements ActiveRecordInterface
{
    /**
     * @var mixed|null
     */
    private $id;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var mixed|null
     */
    private $groupName;

    /**
     * @var mixed|null
     */
    private $price;

    /**
     * @param mixed|null $id
     * @param mixed|null $value
     * @param mixed|null $groupName
     * @param mixed|null $price
     */
    public function __construct($id = null, $value = null, $groupName = null, $price = null)
    {
        $this->id = $id;
        $this->value = $value;
        $this->groupName = $groupName;
        $this->price = $price;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed|null
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @return mixed|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return mixed|null
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed|null $id
     * @return void
     */
    public function setId($id = null): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed|null $primaryKey
     * @return void
     */
    public function setPrimaryKey($primaryKey = null): void
    {
        $this->setId($primaryKey);
    }

    /**
     * @return bool
     */
    public function isPrimaryKeyNull(): bool
    {
        return $this->getId() === null;
    }

    /**
     * @return bool
     */
    public function isModified(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isColumnModified(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return false;
    }

    /**
     * @param $b
     * @return void
     */
    public function setNew($b): void
    {
    }

    /**
     * @return void
     */
    public function resetModified(): void
    {
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return false;
    }

    /**
     * @param $b
     * @return void
     */
    public function setDeleted($b): void
    {
    }

    /**
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function delete(?ConnectionInterface $con = null): void
    {
    }

    /**
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function save(?ConnectionInterface $con = null): void
    {
    }
}
