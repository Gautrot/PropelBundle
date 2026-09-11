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
 * # TranslatableItem
 */
class TranslatableItem implements ActiveRecordInterface
{
    /**
     * @var mixed|null
     */
    private $id;

    /**
     * @var mixed|array
     */
    private $currentTranslations;

    /**
     * @var
     */
    private $groupName;

    /**
     * @var
     */
    private $price;

    /**
     * @param $id
     * @param $translations
     */
    public function __construct($id = null, $translations = [])
    {
        $this->id = $id;
        $this->currentTranslations = $translations;
    }

    /**
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @return mixed
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

    /**
     * @param $locale
     * @param ConnectionInterface|null $con
     * @return mixed|TranslatableItemI18n
     */
    public function getTranslation($locale = 'de', ?ConnectionInterface $con = null)
    {
        if (!isset($this->currentTranslations[$locale])) {
            $translation = new TranslatableItemI18n();
            $translation->setLocale($locale);
            $this->currentTranslations[$locale] = $translation;
        }

        return $this->currentTranslations[$locale];
    }

    /**
     * @param TranslatableItemI18n $i
     * @return void
     */
    public function addTranslatableItemI18n(TranslatableItemI18n $i): void
    {
        if (!in_array($i, $this->currentTranslations)) {
            $this->currentTranslations[$i->getLocale()] = $i;
            $i->setItem($this);
        }
    }

    /**
     * @param TranslatableItemI18n $i
     * @return void
     */
    public function removeTranslatableItemI18n(TranslatableItemI18n $i): void
    {
        unset($this->currentTranslations[$i->getLocale()]);
    }

    /**
     * @return array|mixed
     */
    public function getTranslatableItemI18ns()
    {
        return $this->currentTranslations;
    }
}
