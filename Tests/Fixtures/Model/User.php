<?php

namespace Propel\Bundle\PropelBundle\Tests\Fixtures\Model;

use Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Base\User as BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * # User
 */
class User extends BaseUser implements UserInterface
{
    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @return array|string[]
     */
    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
    }
}
