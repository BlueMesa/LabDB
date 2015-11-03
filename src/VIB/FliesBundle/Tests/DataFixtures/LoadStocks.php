<?php

/*
 * Copyright 2013 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
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

namespace VIB\FliesBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

use VIB\FliesBundle\Entity\Stock;

class LoadStocks extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $om = $this->container->get('bluemesa.core.doctrine.registry')->getManagerForClass('VIB\FliesBundle\Entity\Stock');
        $vm = $this->container->get('bluemesa.core.doctrine.registry')->getManagerForClass('VIB\FliesBundle\Entity\Vial');
        $om->disableAutoAcl();
        $vm->disableAutoAcl();
        
        $user = $this->getReference('user');

        $stock_1 = new Stock();
        $stock_1->setName('stock 1');
        $stock_1->setGenotype('yw');
        $om->persist($stock_1);
        $om->flush();
        $om->createACL($stock_1, $user);
        $vm->createACL($stock_1->getVials(), $user);
        $this->addReference('stock_1', $stock_1);

        $stock_2 = new Stock();
        $stock_2->setName('stock 2');
        $stock_2->setGenotype('yw;Sp/CyO');
        $om->persist($stock_2);
        $om->flush();
        $om->createACL($stock_2, $user);
        $vm->createACL($stock_2->getVials(), $user);
        $this->addReference('stock_2', $stock_2);

        $admin = $this->getReference('admin');

        $stock_3 = new Stock();
        $stock_3->setName('stock 3');
        $stock_3->setGenotype('yw;;Tm2/Tm6');
        $om->persist($stock_3);
        $om->flush();
        $om->createACL($stock_3, $admin);
        $vm->createACL($stock_3->getVials(), $admin);
        $this->addReference('stock_3', $stock_3);

        $stock_4 = new Stock();
        $stock_4->setName('stock 4');
        $stock_4->setGenotype('yw/Fm7');
        $om->persist($stock_4);
        $om->flush();
        $om->createACL($stock_4, $admin);
        $vm->createACL($stock_4->getVials(), $admin);
        $this->addReference('stock_4', $stock_4);
        
        $om->enableAutoAcl();
        $vm->enableAutoAcl();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
