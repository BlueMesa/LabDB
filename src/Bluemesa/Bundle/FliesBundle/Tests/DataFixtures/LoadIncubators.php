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

use Bluemesa\Bundle\FliesBundle\Entity\Incubator;

class LoadIncubators extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $om = $this->container->get('bluemesa.core.doctrine.registry')->getManagerForClass('Bluemesa\Bundle\FliesBundle\Entity\Incubator');
        $om->disableAutoAcl();

        $user = $this->getReference('user');

        $incubator = new Incubator();
        $incubator->setName("Test incubator");
        $om->persist($incubator);
        $om->flush();
        $om->createACL($incubator, $user);
        
        $om->enableAutoAcl();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
