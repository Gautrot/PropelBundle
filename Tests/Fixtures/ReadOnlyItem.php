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

class ReadOnlyItem implements ActiveRecordInterface
{
    public function getName(): string
    {
        return 'Marvin';
    }

    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getPrimaryKey();
    }

    public function getPrimaryKey(): int
    {
        return 42;
    }
}
