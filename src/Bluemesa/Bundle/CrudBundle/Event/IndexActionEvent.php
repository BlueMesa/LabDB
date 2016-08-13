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


use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class IndexActionEvent extends CrudEvent
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var Collection
     */
    private $entities;


    /**
     * IndexActionEvent constructor.
     *
     * @param Request                $request
     * @param ObjectRepository|null  $repository
     * @param Collection|null        $entities
     * @param View|null              $view
     */
    public function __construct(Request $request, ObjectRepository $repository = null,
                                Collection $entities = null)
    {
        $this->request = $request;
        $this->repository = $repository;
        $this->entities = $entities;
        $this->view = $view;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param EntityRepository $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Collection
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param Collection $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    }
}
