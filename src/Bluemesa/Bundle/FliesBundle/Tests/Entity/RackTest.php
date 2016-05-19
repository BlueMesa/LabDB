<?php

/*
 * Copyright 2011 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Bluemesa\Bundle\FliesBundle\Tests\Entity;

use Bluemesa\Bundle\FliesBundle\Entity\Rack;
use Bluemesa\Bundle\FliesBundle\Entity\Vial;
use Bluemesa\Bundle\FliesBundle\Entity\Incubator;

class RackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider rackProvider
     */
    public function testConstruct($rack)
    {
        $this->assertEquals(5,$rack->getRows());
        $this->assertEquals(3,$rack->getColumns());
    }

    /**
     * @dataProvider rackProvider
     */
    public function testGetLabelBarcode($rack)
    {
        $this->assertEquals('R000001',$rack->getLabelBarcode());
    }

    /**
     * @dataProvider rackProvider
     */
    public function testGetLabelText($rack)
    {
        $this->assertEquals('test',$rack->getLabelText());
    }

    /**
     * @dataProvider rackProvider
     */
    public function testToString($rack)
    {
        $this->assertEquals('R000001',$rack->getLabelBarcode());
    }

    /**
     * @dataProvider rackProvider
     */
    public function testName($rack)
    {
        $this->assertEquals('test',$rack->getName());
        $rack->setName('another test');
        $this->assertEquals('another test',$rack->getName());
    }

    /**
     * @dataProvider rackProvider
     */
    public function testGetPosition($rack)
    {
        for ($i = 'A', $k = 1; $i <= 'E'; $i++, $k++) {
            for ($j = 1; $j <= 3; $j++) {
                $position = $rack->getPosition($i, $j);
                $numPosition = $rack->getPosition($k, $j);
                $this->assertEquals($i, $position->getRow());
                $this->assertEquals($j, $position->getColumn());
                $this->assertEquals($position, $numPosition);
            }
        }
    }

    /**
     * @dataProvider rackProvider
     */
    public function testGeometry($rack)
    {
        $this->assertEquals('5 ✕ 3', $rack->getGeometry());
        $rack->setGeometry(3, 5);
        $this->assertEquals(3,$rack->getRows());
        $this->assertEquals(5,$rack->getColumns());
        $this->assertEquals('3 ✕ 5', $rack->getGeometry());
    }

    /**
     * @dataProvider rackProvider
     */
    public function testContents($rack)
    {
        $vial_1 = new Vial();
        $vial_2 = new Vial();
        $vial_3 = new Vial();
        $rack->addContent($vial_1, 2, 2);
        $this->assertEquals($vial_1, $rack->getContent(2, 2));
        $rack->addContent($vial_2, 3, 3);
        $this->assertContains($vial_2, $rack->getContents());
        $this->assertEquals(2, count($rack->getContents()));
        $rack->replaceContent(2, 2, $vial_3);
        $this->assertNotContains($vial_1, $rack->getContents());
        $this->assertEquals($vial_3, $rack->getContent(2, 2));
        $rack->removeContent($vial_2);
        $this->assertNotContains($vial_2, $rack->getContents());
        $this->assertEquals(false, $rack->hasContent($vial_2));
        $rack->clearContents();
        $this->assertEquals(0, count($rack->getContents()));
    }

    public function testStorageUnit()
    {
        $rack = new Rack();
        $incubator = new Incubator();
        $incubator->setTemperature(28);
        $this->assertNull($rack->getStorageUnit());
        $rack->setStorageUnit($incubator);
        $this->assertEquals($incubator, $rack->getStorageUnit());

        return $rack;
    }

    /**
     *
     * @depends testStorageUnit
     */
    public function testGetTemperature($rack)
    {
        $this->assertEquals(28, $rack->getTemperature());
        $rack->setStorageUnit(null);
        $this->assertEquals(21, $rack->getTemperature());
    }

    public function rackProvider()
    {
        $rack = new FakeRack(5, 3);

        return array(array($rack));
    }
}

class FakeRack extends Rack
{
    public function __construct($rows = null, $columns = null)
    {
        parent::__construct($rows, $columns);
        $this->id = 1;
        $this->name = 'test';
    }
}
