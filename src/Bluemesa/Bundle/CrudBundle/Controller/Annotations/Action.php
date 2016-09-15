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
 * @Target("METHOD")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class Action
{
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
    public $redirect_route;

    /**
     * @var string
     */
    public $delete_route;


    /**
     * Action Annotation constructor.
     */
    public function __construct()
    {
        $this->name = null;
        $this->form_type = null;
        $this->redirect_route = null;
        $this->delete_route = null;
    }

    /**
     * @return string
     */
    public function getAction()
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
    public function getRedirectRoute()
    {
        return $this->redirect_route;
    }

    /**
     * @return string
     */
    public function getDeleteRoute()
    {
        return $this->delete_route;
    }
}
