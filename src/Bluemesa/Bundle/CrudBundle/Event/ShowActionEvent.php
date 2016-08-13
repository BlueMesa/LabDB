<?php

/*
 * This file is part of the CRUD Bundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\Event;


use Bluemesa\Bundle\CoreBundle\Entity\EntityInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ShowActionEvent extends EntityEvent
{
    /**
     * NewActionEvent constructor.
     *
     * @param Request $request
     * @param EntityInterface $entity
     * @param Form $form
     */
    public function __construct(Request $request, EntityInterface $entity)
    {
        $this->request = $request;
        $this->entity = $entity;
    }
}
