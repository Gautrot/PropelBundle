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
     * @param $id
     * @param $value
     * @param $groupName
     * @param $price
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
     * @param $id
     * @return void
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param $primaryKey
     * @return void
     */
    public function setPrimaryKey($primaryKey): void
    {
        $this->setId($primaryKey);
    }

    /**
     * @return bool
     */
    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getId();
    }

    /**
     * @return bool
     */
    public function isModified(): bool
    {
        return false;
    }

    /**
     * @param $col
     * @return bool
     */
    public function isColumnModified($col): bool
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
    public function setNew($b)
    {
    }

    /**
     * @return void
     */
    public function resetModified()
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
    public function setDeleted($b)
    {
    }

    /**
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function delete(?ConnectionInterface $con = null)
    {
    }

    /**
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function save(?ConnectionInterface $con = null)
    {
    }
}
