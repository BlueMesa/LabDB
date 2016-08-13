<?php

/*
 * This file is part of the CRUD Bundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\Request;


use Bluemesa\Bundle\CoreBundle\Doctrine\ObjectManagerRegistry;
use Bluemesa\Bundle\CoreBundle\Entity\Entity;
use Bluemesa\Bundle\CrudBundle\Event\CrudControllerEvents;
use Bluemesa\Bundle\CrudBundle\Event\DeleteActionEvent;
use Bluemesa\Bundle\CrudBundle\Event\EditActionEvent;
use Bluemesa\Bundle\CrudBundle\Event\IndexActionEvent;
use Bluemesa\Bundle\CrudBundle\Event\NewActionEvent;
use Bluemesa\Bundle\CrudBundle\Event\ShowActionEvent;
use FOS\RestBundle\View\View;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class EntityHandler
 *
 * @DI\Service("bluemesa.crud.handler")
 *
 * @package Bluemesa\Bundle\CrudBundle\Request
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class EntityHandler
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ObjectManagerRegistry
     */
    protected $registry;

    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var RouterInterface
     */
    protected $router;


    /**
     * EntityHandler constructor.
     *
     * @DI\InjectParams({
     *     "dispatcher" = @DI\Inject("event_dispatcher"),
     *     "registry" = @DI\Inject("bluemesa.core.doctrine.registry"),
     *     "factory" = @DI\Inject("form.factory"),
     *     "router" = @DI\Inject("router")
     * })
     *
     * @param EventDispatcherInterface  $dispatcher
     * @param ObjectManagerRegistry     $registry
     * @param FormFactoryInterface      $factory
     * @param RouterInterface           $router
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                ObjectManagerRegistry $registry,
                                FormFactoryInterface $factory,
                                RouterInterface $router)
    {
        $this->dispatcher = $dispatcher;
        $this->registry = $registry;
        $this->factory = $factory;
        $this->router = $router;
    }


    /**
     * @param  Request $request
     *
     * @return View
     */
    public function handleIndexAction(Request $request)
    {
        $em = $this->registry->getManager();
        $entityClass = $request->get('entity_class');
        $repository = $em->getRepository($entityClass);

        $event = new IndexActionEvent($request, $repository);
        $this->dispatcher->dispatch(CrudControllerEvents::INDEX_INITIALIZE, $event);

        $entities = $repository->findAll();
        $event = new IndexActionEvent($request, $repository, $entities);
        $this->dispatcher->dispatch(CrudControllerEvents::INDEX_FETCHED, $event);

        if (null === $view = $event->getView()) {
            $view = View::create(array('entities' => $entities));
        }

        $event = new IndexActionEvent($request, $repository, $entities);
        $this->dispatcher->dispatch(CrudControllerEvents::INDEX_COMPLETED, $event);

        /** @var View $view */
        return $view;
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function handleNewAction(Request $request)
    {
        $type = $request->get('form_class');
        $entityClass = $request->get('entity_class');

        /** @var Entity $entity */
        $entity = new $entityClass();
        $form = $this->factory->create($type, $entity);

        $event = new NewActionEvent($request, $entity, $form);
        $this->dispatcher->dispatch(CrudControllerEvents::NEW_INITIALIZE, $event);

        if (null !== $event->getView()) {
            return $event->getView();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new NewActionEvent($request, $entity, $form);
            $this->dispatcher->dispatch(CrudControllerEvents::NEW_SUCCESS, $event);

            $em = $this->registry->getManagerForClass(get_class($entity));
            $em->persist($entity);
            $em->flush();

            if (null === $view = $event->getView()) {
                $route = $request->get('redirect_route');
                $view = View::createRouteRedirect($route, array('id' => $entity->getId()));
            }

            $event = new NewActionEvent($request, $entity, $form);
            $this->dispatcher->dispatch(CrudControllerEvents::NEW_COMPLETED, $event);

            /** @var View $view */
            return $view;
        }

        return View::create(array('entity' => $entity, 'form' => $form->createView()));
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function handleShowAction(Request $request)
    {
        $entity = $request->get('entity');
        $deleteForm = $this->createDeleteForm($request);

        $event = new ShowActionEvent($request, $entity);
        $this->dispatcher->dispatch(CrudControllerEvents::SHOW_INITIALIZE, $event);

        if (null === $view = $event->getView()) {
            $view = View::create(array('entity' => $entity, '$delete_form' => $deleteForm->createView()));
        }

        $event = new IndexActionEvent($request, $entity);
        $this->dispatcher->dispatch(CrudControllerEvents::SHOW_COMPLETED, $event);

        /** @var View $view */
        return $view;
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function handleEditAction(Request $request)
    {
        /** @var Entity $entity */
        $entity = $request->get('entity');
        $type = $request->get('form_class');
        $form = $this->factory->create($type, $entity);
        $deleteForm = $this->createDeleteForm($request);

        $event = new EditActionEvent($request, $entity, $form);
        $this->dispatcher->dispatch(CrudControllerEvents::EDIT_INITIALIZE, $event);

        if (null !== $event->getView()) {
            return $event->getView();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new EditActionEvent($request, $entity, $form);
            $this->dispatcher->dispatch(CrudControllerEvents::EDIT_SUCCESS, $event);

            $em = $this->registry->getManagerForClass(get_class($entity));
            $em->persist($entity);
            $em->flush();

            if (null === $view = $event->getView()) {
                $route = $request->get('success_redirect_route');
                $view = View::createRouteRedirect($route, array('id' => $entity->getId()));
            }

            $event = new EditActionEvent($request, $entity, $form);
            $this->dispatcher->dispatch(CrudControllerEvents::EDIT_COMPLETED, $event);

            /** @var View $view */
            return $view;
        }

        return View::create(array(
            'entity' => $entity,
            'form' => $form->createView(),
            '$delete_form' => $deleteForm->createView()));
    }


    /**
     * @param Request $request
     *
     * @return View
     */
    public function handleDeleteAction(Request $request)
    {
        /** @var Entity $entity */
        $entity = $request->get('entity');
        $form = $this->createDeleteForm($request);

        $event = new DeleteActionEvent($request, $entity, $form);
        $this->dispatcher->dispatch(CrudControllerEvents::DELETE_INITIALIZE, $event);

        if (null !== $event->getView()) {
            return $event->getView();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new DeleteActionEvent($request, $entity, $form);
            $this->dispatcher->dispatch(CrudControllerEvents::DELETE_SUCCESS, $event);

            $em = $this->registry->getManagerForClass(get_class($entity));
            $em->remove($entity);
            $em->flush();

            if (null === $view = $event->getView()) {
                $route = $request->get('success_redirect_route');
                $view = View::createRouteRedirect($route);
            }

            $event = new DeleteActionEvent($request, $entity, $form);
            $this->dispatcher->dispatch(CrudControllerEvents::DELETE_COMPLETED, $event);

            /** @var View $view */
            return $view;
        }

        return View::create(array(
            'entity' => $entity,
            'form' => $form->createView()));
    }

    /**
     * Creates a form to delete an entity.
     *
     * @param Request $request
     *
     * @return Form
     */
    private function createDeleteForm(Request $request)
    {
        /** @var Entity $entity */
        $entity = $request->get('entity');
        $url = $this->router->generate($request->get('delete_route'), array('id' => $entity->getId()));

        return $this->factory->createBuilder()
            ->setAction($url)
            ->setMethod('DELETE')
            ->getForm();
    }
}
