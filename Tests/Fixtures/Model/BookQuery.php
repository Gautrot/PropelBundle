<?php

namespace Propel\Bundle\PropelBundle\Tests\Fixtures\Model;

use Exception;
use Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Base\BookQuery as BaseBookQuery;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Skeleton subclass for performing query and update operations on the 'book' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class BookQuery extends BaseBookQuery
{
    private bool $bySlug = false;
    private bool $byAuthorSlug = false;

    /**
     * fake for test
     * @param $key
     * @param ConnectionInterface|null $con
     * @return Book|null
     */
    public function findPk($key, ?ConnectionInterface $con = null): ?Book
    {
        if (1 === $key) {
            $book = new Book();
            $book->setId(1);

            return $book;
        }

        return null;
    }

    /**
     * fake for test
     * @param $slug
     * @param $comparison
     * @return $this
     */
    public function filterByAuthorSlug($slug = null, $comparison = null): static
    {
        if ('my-author' === $slug) {
            $this->byAuthorSlug = true;
        }

        return $this;
    }

    /**
     * fake for test
     * @param $slug
     * @param $comparison
     * @return $this
     */
    public function filterBySlug($slug = null, $comparison = null): static
    {
        if ('my-book' == $slug) {
            $this->bySlug = true;
        }

        return $this;
    }

    /**
     * fake for test
     * @throws Exception
     */
    public function filterByName($name = null, $comparison = null)
    {
        throw new Exception('Test should never call this method');
    }

    /**
     * fake for test
     * @param ConnectionInterface|null $con
     * @return Book|null
     */
    public function findOne(?ConnectionInterface $con = null): ?Book
    {
        if (true === $this->bySlug) {
            $book = new Book();
            $book->setId(1);
            $book->setName('My Book');
            $book->setSlug('my-book');

            return $book;
        } elseif (true === $this->byAuthorSlug) {
            $book = new Book();
            $book->setId(2);
            $book->setName('My Kewl Book');
            $book->setSlug('my-kewl-book');

            return $book;
        }

        return null;
    }
} // BookQuery
