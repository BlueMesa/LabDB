<?php

/*
 * This file is part of the BlueMesa CRUD Bundle.
 *
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\Controller;

use Bluemesa\Bundle\CoreBundle\Entity\Entity;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Basic CRUD entity controller.
 *
 * @REST\Prefix("/entities")
 * @REST\NamePrefix("bluemesa_entity_")
 */
class EntityController extends Controller
{
    /**
     * Lists all Entities.
     *
     * @REST\Get("/"))
     * @REST\Get("", name="_rest"))
     */
    public function indexAction(Request $request)
    {
        $handler = $this->container->get('bluemesa.crud.handler');
        return $handler->handleIndexAction($request);
    }

    /**
     * Creates a new Entity.
     *
     * @REST\Get("/new")
     * @REST\Put("", name="_rest")
     */
    public function newAction(Request $request)
    {
        $this->createForm('');
    }

    /**
     * Finds and displays an Entity.
     *
     * @REST\Get("/{id}")
     */
    public function showAction(Request $request, Entity $entity)
    {

    }

    /**
     * Displays a form to edit an existing Entity.
     *
     * @REST\Get("/{id}/edit")
     * @REST\Post("/{id}", name="_rest")
     */
    public function editAction(Request $request, Entity $entity)
    {

    }

    /**
     * Deletes an Entity.
     *
     * @REST\Get("/{id}/delete")
     * @REST\Delete("/{id}", name="_rest"))
     */
    public function deleteAction(Request $request, Entity $entity)
    {

    }

    /**
     * Creates a form to delete an Entity.
     */
    private function createDeleteForm(Entity $entity)
    {

    }
}
