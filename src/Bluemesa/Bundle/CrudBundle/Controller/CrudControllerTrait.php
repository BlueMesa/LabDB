<?php

/*
 * This file is part of the CRUD Bundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\Controller;


use Bluemesa\Bundle\CrudBundle\Request\CrudHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait CrudControllerTrait
{
    /**
     * @return CrudHandler
     */
    public function getCrudHandler()
    {
        /** @var ContainerInterface $container */
        $container = $this->container;

        /** @var CrudHandler $handler */
        $handler = $container->get('bluemesa.crud.handler');

        return $handler;
    }
}
