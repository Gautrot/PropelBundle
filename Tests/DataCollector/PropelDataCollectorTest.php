<?php

namespace Propel\Bundle\PropelBundle\Tests\DataCollector;

use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\DataCollector\PropelDataCollector;
use Propel\Bundle\PropelBundle\Logger\PropelLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * # PropelDataCollectorTest
 */
class PropelDataCollectorTest extends TestCase
{
    /**
     * @return void
     */
    public function testCollect(): void
    {
        $logger = new class extends PropelLogger {
            public function setQueries(array $queries): void
            {
                $this->queries = $queries;
            }
        };
        $logger->setQueries([
            ['sql' => 'SELECT 1', 'connection' => 'default', 'time' => 0.125, 'memory' => 0],
            ['sql' => 'SELECT 2', 'connection' => 'default', 'time' => 0.375, 'memory' => 0],
        ]);

        $collector = new PropelDataCollector($logger);
        $collector->collect(new Request(), new Response());

        $this->assertSame('propel', $collector->getName());
        $this->assertSame(2, $collector->getQueryCount());
        $this->assertSame(0.5, $collector->getTime());
        $this->assertInstanceOf(Data::class, $collector->getQueries());

        $collector->reset();
    }

    /**
     * @return void
     */
    public function testEmpty(): void
    {
        $collector = new PropelDataCollector(new PropelLogger());
        $collector->collect(new Request(), new Response());

        $this->assertSame(0, $collector->getQueryCount());
        $this->assertSame(0, $collector->getTime());
    }
}
