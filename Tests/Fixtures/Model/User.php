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
     * @return void
     */
    public function getRoles(): void
    {
        // TODO: Implement getRoles() method.
    }

    /**
     * @return void
     */
    public function getUserIdentifier(): void
    {
        // TODO: Implement getUserIdentifier() method.
    }

    /**
     * @return void
     */
    public function getPassword(): void
    {
        // TODO: Implement getPassword() method.
    }

    /**
     * @return void
     */
    public function getSalt(): void
    {
        // TODO: Implement getSalt() method.
    }
}
