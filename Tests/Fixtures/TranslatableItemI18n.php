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
 * # TranslatableItemI18n
 */
class TranslatableItemI18n implements ActiveRecordInterface
{
    /**
     * @var mixed|null
     */
    private $id;

    /**
     * @var mixed|null
     */
    private $locale;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var mixed
     */
    private $value2;

    /**
     * @var mixed
     */
    private $item;

    /**
     * @param $id
     * @param $locale
     * @param $value
     */
    public function __construct($id = null, $locale = null, $value = null)
    {
        $this->id = $id;
        $this->locale = $locale;
        $this->value = $value;
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

    /**
     * @return mixed|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param $locale
     * @return void
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param $item
     * @return void
     */
    public function setItem($item): void
    {
        $this->item = $item;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return void
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue2()
    {
        return $this->value2;
    }

    /**
     * @param $value2
     * @return void
     */
    public function setValue2($value2): void
    {
        $this->value2 = $value2;
    }
}
