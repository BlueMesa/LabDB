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

namespace Bluemesa\Bundle\FliesBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

use Bluemesa\Bundle\FliesBundle\Entity\StockVial;

class LoadStockVials extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
    public function load(ObjectManager $vm)
    {
        $vm = $this->container->get('bluemesa.core.doctrine.registry')->getManagerForClass('Bluemesa\Bundle\FliesBundle\Entity\StockVial');
        $vm->disableAutoAcl();
        
        $user = $this->getReference('user');

        $vial_1 = new StockVial();
        $vial_1->setStock($this->getReference('stock_4'));
        $vm->persist($vial_1);
        $vm->flush();
        $vm->createACL($vial_1, $user);
        $this->addReference('vial_1', $vial_1);

        $vial_2 = new StockVial();
        $vial_2->setStock($this->getReference('stock_1'));
        $vial_2->getSetupDate()->sub(new \DateInterval('P3M'));
        $vm->persist($vial_2);
        $vm->flush();
        $vm->createACL($vial_2, $user);

        $vial_3 = new StockVial();
        $vial_3->setStock($this->getReference('stock_1'));
        $vial_3->setTrashed(true);
        $vm->persist($vial_3);
        $vm->flush();
        $vm->createACL($vial_3, $user);
        
        $vm->enableAutoAcl();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
