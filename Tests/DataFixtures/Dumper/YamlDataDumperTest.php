<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\DataFixtures\Dumper;

use Propel\Bundle\PropelBundle\DataFixtures\Dumper\YamlDataDumper;
use Propel\Bundle\PropelBundle\Tests\DataFixtures\TestCase;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\CoolBook;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\CoolBookAuthor;

/**
 * # YamlDataDumperTest
 * @author William Durand <william.durand1@gmail.com>
 * @author Toni Uebernickel <tuebernickel@gmail.com>
 */
class YamlDataDumperTest extends TestCase
{
    /**
     * @return void
     */
    public function testYamlDump()
    {
        $author = new CoolBookAuthor();
        $author->setName('A famous one')->save($this->con);

        $book = new CoolBook();
        $book
            ->setName('An important one')
            ->setAuthorId(1)
            ->save($this->con);

        $statement = $this->con->prepare('UPDATE cool_book SET complementary_infos = :value WHERE id = :id');
        $statement->execute([
            'value' => json_encode(['first_word_date' => '2012-01-01'], JSON_THROW_ON_ERROR),
            'id' => 1,
        ]);

        $filename = $this->getTempFile();

        $loader = new YamlDataDumper(__DIR__ . '/../../Fixtures/DataFixtures/Loader', array());
        $loader->dump($filename);

        $expected = <<<YAML
\Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\CoolBookAuthor:
    CoolBookAuthor_1:
        id: '1'
        name: 'A famous one'
\Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\CoolBook:
    CoolBook_1:
        id: '1'
        name: 'An important one'
        author_id: CoolBookAuthor_1
        complementary_infos: { first_word_date: '2012-01-01' }

YAML;
        $result = file_get_contents($filename);

        $this->assertEquals(str_replace(["\r\n", "\n", "\r"], '', $expected), str_replace(["\r\n", "\n", "\r"], '', $result));
    }
}
