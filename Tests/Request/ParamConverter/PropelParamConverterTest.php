<?php

namespace Propel\Bundle\PropelBundle\Tests\Request\ParamConverter;

use Exception;
use LogicException;
use Propel\Bundle\PropelBundle\Request\ParamConverter\PropelParamConverter;
use Propel\Bundle\PropelBundle\Tests\CaseTest;
use Propel\Generator\Util\QuickBuilder;
use Propel\Runtime\Connection\ConnectionWrapper;
use Propel\Runtime\Propel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropelParamConverterTest extends CaseTest
{
    /**
     * @var ConnectionWrapper $con
     */
    protected mixed $con = null;

    /**
     * @return void
     * @todo SensioFrameworkExtraBundle is no longer maintained as of Symfony 6.2
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!interface_exists('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface')) {
            $this->markTestSkipped('SensioFrameworkExtraBundle is not available.');
        }

        // @fixme: some tests fail if instance pooling is disabled...
        //Propel::disableInstancePooling();

        $this->loadDatabaseMap(['Propel\\Bundle\\PropelBundle\\Tests\\Fixtures\\Model\\Map\\BookTableMap']);
    }

    public function tearDown(): void
    {
        //Propel::enableInstancePooling();

        if ($this->con) {
            $this->con->useDebug(false);
        }
    }

    public function testParamConverterSupport()
    {
        $paramConverter = new PropelParamConverter();

        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book']);
        $this->assertTrue($paramConverter->supports($configuration), 'param converter should support propel class');

        $configuration = new ParamConverter(['class' => 'fakeClass']);
        $this->assertFalse($paramConverter->supports($configuration), 'param converter should not support wrong class');

        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\CaseTest']);
        $this->assertFalse($paramConverter->supports($configuration), 'param converter should not support wrong class');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindPk()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['id' => 1, 'book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book']);

        $paramConverter->apply($request, $configuration);

        $this->assertInstanceOf(
            'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"'
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindPkNotFound()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['id' => 2, 'book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book']);

        $this->expectException(NotFoundHttpException::class);

        $paramConverter->apply($request, $configuration);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindSlug()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['slug' => 'my-book', 'book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book']);
        $paramConverter->apply($request, $configuration);
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindCamelCasedSlug()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['author_slug' => 'my-author', 'slug' => 'my-kewl-book', 'book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book']);

        $paramConverter->apply($request, $configuration);
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindSlugNotFound()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['slug' => 'my-foo', 'book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book']);

        $this->expectException(NotFoundHttpException::class);

        $paramConverter->apply($request, $configuration);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindBySlugNotByName()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['slug' => 'my-book', 'name' => 'foo', 'book' => null]);
        $configuration = new ParamConverter([
            'class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book',
            'options' => ['exclude' => ['name']]]);
        $paramConverter->apply($request, $configuration);
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindByAllParamExcluded()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['slug' => 'my-book', 'name' => 'foo', 'book' => null]);
        $configuration = new ParamConverter([
            'class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book',
            'options' => ['exclude' => ['name', 'slug']]]);

        $this->expectException(LogicException::class);

        $paramConverter->apply($request, $configuration);

        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindByIdExcluded()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['id' => '1234', 'book' => null]);
        $configuration = new ParamConverter([
            'class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book',
            'options' => ['exclude' => ['id']]]);

        $this->expectException(LogicException::class);

        $paramConverter->apply($request, $configuration);

        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindLogicError()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book']);

        $this->expectException(LogicException::class);

        $paramConverter->apply($request, $configuration);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindWithOptionalParam()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', 'name' => 'book']);
        $configuration->setIsOptional(true);
        $paramConverter->apply($request, $configuration);

        $this->assertNull($request->attributes->get('book'),
            'param "book" should be null if book is not found and the parameter is optional');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindWithMapping()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['toto' => 1, 'book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book',
            'name' => 'book',
            'options' => ['mapping' => ['toto' => 'id']]
        ]);
        $paramConverter->apply($request, $configuration);
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConverterFindSlugWithMapping()
    {
        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['slugParam_special' => 'my-book', 'book' => null]);
        $configuration = new ParamConverter(['class' => 'Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book',
            'name' => 'book',
            'options' => ['mapping' => ['slugParam_special' => 'slug']]
        ]);
        $paramConverter->apply($request, $configuration);
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book', $request->attributes->get('book'),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Fixtures\Model\Book"');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConvertWithOptionWith()
    {
        $this->loadFixtures();

        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['id' => 1, 'book' => null]);
        $configuration = new ParamConverter([
            'class' => 'Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyBook',
            'name' => 'book',
            'options' => [
                'with' => 'MyAuthor'
            ]
        ]);

        $nb = $this->con->getQueryCount();
        $paramConverter->apply($request, $configuration);

        $book = $request->attributes->get('book');
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyBook', $book,
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyBook"');

        $this->assertEquals($nb + 1, $this->con->getQueryCount(), 'only one query to get the book');

        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor', $book->getMyAuthor(),
            'param "book" should be an instance of "Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor"');

        $this->assertEquals($nb + 1, $this->con->getQueryCount(), 'no new query to get the author');
        Propel::enableInstancePooling();
    }

    protected function loadFixtures(): void
    {
        $schema = <<<XML
<database name="default" package="vendor.bundles.Propel.Bundle.PropelBundle.Tests.Request.ParamConverter"
    namespace="Propel\Bundle\PropelBundle\Tests\Request\ParamConverter" defaultIdMethod="native">
    <table name="my_book">
        <column name="id" type="integer" primaryKey="true" />
        <column name="name" type="varchar" size="255" />
        <column name="my_author_id" type="integer" required="true" />

        <foreign-key foreignTable="my_author" onDelete="CASCADE" onUpdate="CASCADE">
            <reference local="my_author_id" foreign="id" />
        </foreign-key>
    </table>

    <table name="my_author">
        <column name="id" type="integer" primaryKey="true" />
        <column name="name" type="varchar" size="255" />
    </table>
</database>
XML;

        if (!class_exists('Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor')) {
            QuickBuilder::buildSchema($schema);
        }

        $this->con = Propel::getConnection('default');
        $this->con->useDebug();

        MyBookQuery::create()->deleteAll($this->con);
        MyAuthorQuery::create()->deleteAll($this->con);

        $author = new MyAuthor();
        $author->setId(10);
        $author->setName('Will');

        $book = new MyBook();
        $book->setId(1);
        $book->setName('PropelBook');
        $book->setMyAuthor($author);

        $book2 = new MyBook();
        $book2->setId(2);
        $book2->setName('sf2lBook');
        $book2->setMyAuthor($author);

        $author->save($this->con);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConvertWithOptionWithLeftJoin()
    {
        $this->loadFixtures();

        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['param1' => 10, 'author' => null]);
        $configuration = new ParamConverter([
            'class' => 'Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor',
            'name' => 'author',
            'options' => [
                'with' => [['MyBook', 'left join']],
                'mapping' => ['param1' => 'id'],
            ]
        ]);

        $nb = $this->con->getQueryCount();
        $paramConverter->apply($request, $configuration);

        $author = $request->attributes->get('author');
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor', $author,
            'param "author" should be an instance of "Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor"');

        $this->assertEquals($nb + 1, $this->con->getQueryCount(), 'only one query to get the author');

        $books = $author->getMyBooks();
        $this->assertInstanceOf('\Propel\Runtime\Collection\ObjectCollection', $books);
        $this->assertCount(2, $books, 'Author should have two books');

        $this->assertEquals($nb + 1, $this->con->getQueryCount(), 'no new query to get the books');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testParamConvertWithOptionWithFindPk()
    {
        $this->loadFixtures();

        $paramConverter = new PropelParamConverter();
        $request = new Request([], [], ['id' => 10, 'author' => null]);
        $configuration = new ParamConverter([
            'class' => 'Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor',
            'name' => 'author',
            'options' => [
                'with' => [['MyBook', 'left join']],
            ]
        ]);

        $nb = $this->con->getQueryCount();
        $paramConverter->apply($request, $configuration);

        $author = $request->attributes->get('author');
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor', $author,
            'param "author" should be an instance of "Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor"');

        $this->assertEquals($nb + 1, $this->con->getQueryCount(), 'only one query to get the book');

        $books = $author->getMyBooks();
        $this->assertInstanceOf('\Propel\Runtime\Collection\ObjectCollection', $books);
        $this->assertCount(2, $books, 'Author should have two books');

        $this->assertEquals($nb + 1, $this->con->getQueryCount(), 'no new query to get the books');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testConfigurationReadFromRequestAttributesIfEmpty()
    {
        $this->loadFixtures();

        $paramConverter = new PropelParamConverter();

        $request = new Request();
        $request->attributes->add([
            '_route' => 'test_route',
            'id' => 10,
            'author' => null,
            'propel_converter' => [
                'author' => [
                    'mapping' => [
                        'authorId' => 'id',
                    ],
                ],
            ],
        ]);

        $configuration = new ParamConverter([
            'class' => 'Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor',
            'name' => 'author',
            'options' => [],
        ]);

        $paramConverter->apply($request, $configuration);

        $author = $request->attributes->get('author');
        $this->assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor', $author,
            'param "author" should be an instance of "Propel\Bundle\PropelBundle\Tests\Request\ParamConverter\MyAuthor"');
    }
}
