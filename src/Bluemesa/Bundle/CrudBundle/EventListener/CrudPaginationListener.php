<?php

/*
 * This file is part of the CRUD Bundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\EventListener;

use Bluemesa\Bundle\CrudBundle\Controller\Annotations\Paginate;
use Bluemesa\Bundle\CrudBundle\Event\IndexActionEvent;
use Doctrine\Common\Annotations\Reader;
use JMS\DiExtraBundle\Annotation as DI;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * The CrudAnnotationListener handles CRUD annotations for controllers.
 *
 * @DI\Service("bluemesa.crud.listener.pagination")
 * @DI\Tag("kernel.event_listener",
 *     attributes = {
 *         "event" = "bluemesa.controller.index_initialize",
 *         "method" = "onIndexInitialize",
 *         "priority" = 900
 *     }
 * )
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrudPaginationListener
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "reader" = @DI\Inject("annotation_reader"),
     *     "paginator" = @DI\Inject("knp_paginator")
     * })
     *
     * @param Reader $reader
     * @param PaginatorInterface $paginator
     * @throws \Exception
     */
    public function __construct(Reader $reader, PaginatorInterface $paginator)
    {
        $this->reader = $reader;
        $this->paginator = $paginator;
    }

    public function onIndexInitialize(IndexActionEvent $event)
    {
        $request = $event->getRequest();
        $controller = $this->getController($request);

        if (is_array($controller)) {
            $m = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && is_callable($controller, '__invoke')) {
            $m = new \ReflectionMethod($controller, '__invoke');
        } else {
            return false;
        }

        /** @var Paginate $paginateAnnotation */
        $paginateAnnotation = $this->reader->getMethodAnnotation($m, Paginate::class);
        if (! $paginateAnnotation) {
            return false;
        }
        $maxResults = $paginateAnnotation->getMaxResults();

        $page = $request->get('page', 1);
        $repository = $event->getRepository();
        $count = $repository->getListCount();
        $query = $repository->getListQuery()->setHint('knp_paginator.count', $count);
        $entities = $this->paginator->paginate($query, $page, $maxResults, array('distinct' => false));
        $event->setEntities($entities);
    }

    /**
     * @param  Request  $request
     * @return array
     */
    private function getController($request)
    {
        return explode("::", $request->get('_controller'));
    }
}
