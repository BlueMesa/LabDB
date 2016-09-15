<?php

/*
 * This file is part of the CRUD Bundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\Controller\Annotations;


/**
 * Entity Annotation
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class Controller
{
    /**
     * @var string
     */
    public $entity_class;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $form_type;

    /**
     * @var string
     */
    public $edit_redirect;

    /**
     * @var string
     */
    public $delete_redirect;

    /**
     * @var string
     */
    public $delete_route;


    /**
     * Entity Annotation constructor.
     */
    public function __construct()
    {
        $this->entity_class = null;
        $this->name = null;
        $this->form_type = null;
        $this->edit_redirect = null;
        $this->delete_redirect = null;
        $this->delete_route = null;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entity_class;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return $this->form_type;
    }

    /**
     * @return string
     */
    public function getEditRedirect()
    {
        return $this->edit_redirect;
    }

    /**
     * @return string
     */
    public function getDeleteRedirect()
    {
        return $this->delete_redirect;
    }

    /**
     * @return string
     */
    public function getDeleteRoute()
    {
        return $this->delete_route;
    }
}
