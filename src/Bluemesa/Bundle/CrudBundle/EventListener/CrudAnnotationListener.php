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

use Bluemesa\Bundle\CrudBundle\Controller\Annotations\Action;
use Bluemesa\Bundle\CrudBundle\Controller\Annotations\Controller;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * The CrudAnnotationListener handles CRUD annotations for controllers.
 *
 * @DI\Service("bluemesa.crud.listener.annotation")
 * @DI\Tag("kernel.event_listener",
 *     attributes = {
 *         "event" = "kernel.controller",
 *         "method" = "onKernelController",
 *         "priority" = 10
 *     }
 * )
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrudAnnotationListener
{
    /**
     * @var ParamConverterManager
     */
    protected $manager;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("sensio_framework_extra.converter.manager"),
     *     "reader" = @DI\Inject("annotation_reader"),
     *     "router" = @DI\Inject("router")
     * })
     *
     * @param ParamConverterManager $manager  A ParamConverterManager instance
     * @param Reader                $reader   A Reader instance
     * @param RouterInterface       $router   A RouterInterface instance
     */
    public function __construct(ParamConverterManager $manager, Reader $reader, RouterInterface $router)
    {
        $this->manager = $manager;
        $this->reader = $reader;
        $this->router = $router;
    }

    /**
     * Modifies the ParamConverterManager instance.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (is_array($controller)) {
            $c = new \ReflectionClass(ClassUtils::getClass($controller[0]));
            $m = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && is_callable($controller, '__invoke')) {
            /** @var object $controller */
            $c = new \ReflectionClass(ClassUtils::getClass($controller));
            $m = new \ReflectionMethod($controller, '__invoke');
        } else {
            return;
        }

        /** @var Controller $controllerAnnotation */
        $controllerAnnotation = $this->reader->getClassAnnotation($c, Controller::class);
        /** @var Action $actionAnnotation */
        $actionAnnotation = $this->reader->getMethodAnnotation($m, Action::class);
        if (! $controllerAnnotation || ! $actionAnnotation) {
            return;
        }

        $class = $this->getEntityClass($controllerAnnotation, $c);

        if ((! $request->attributes->has('entity'))&&($request->attributes->has('id'))) {
            $configurations = array();
            $configuration = new ParamConverter(array());
            $configuration->setName('entity');
            $configuration->setClass($class);
            $configurations['entity'] = $configuration;
            $this->manager->apply($request, $configurations);
        }

        $name = $this->getEntityName($controllerAnnotation, $c);
        $action = $this->getActionName($actionAnnotation, $m);
        $type = $this->getFormType($actionAnnotation, $controllerAnnotation, $class);
        $editRedirect = $this->getEditRedirectRoute($actionAnnotation, $controllerAnnotation, $request, $c);
        $deleteRedirect = $this->getDeleteRedirectRoute($actionAnnotation, $controllerAnnotation, $request, $c);
        $delete = $this->getDeleteRoute($actionAnnotation, $controllerAnnotation, $request, $c);

        $this->addRequestAttribute($request, 'entity_class', $class);
        $this->addRequestAttribute($request, 'entity_name', $name);
        $this->addRequestAttribute($request, 'crud_action', $action);
        $this->addRequestAttribute($request, 'form_type', $type);
        $this->addRequestAttribute($request, 'edit_redirect_route', $editRedirect);
        $this->addRequestAttribute($request, 'delete_redirect_route', $deleteRedirect);
        $this->addRequestAttribute($request, 'delete_route', $delete);
    }

    /**
     * @param Controller        $controllerAnnotation
     * @param \ReflectionClass  $c
     *
     * @return string
     * @throws \LogicException
     */
    private function getEntityClass(Controller $controllerAnnotation, \ReflectionClass $c)
    {
        $class = $controllerAnnotation->getEntityClass();
        if (null === $class) {
            $name = $this->getEntityName($controllerAnnotation, $c);
            $class = preg_replace('/[\s_]+/', '', $name);
        }
        if (! class_exists($class)) {
            $controllerNamespace = $c->getNamespaceName() . "\\";
            $class = str_replace("\\Controller\\", "\\Entity\\", $controllerNamespace) . $class;
            if (! class_exists($class)) {
                $message  = "Cannot find class ";
                $message .= $controllerAnnotation->getEntityClass();
                $message .= ". Please specify the entity FQCN using entity_class parameter.";
                throw new \LogicException($message);
            }
        }

        return $class;
    }

    private function getEntityName(Controller $controllerAnnotation, \ReflectionClass $c)
    {
        $name = $controllerAnnotation->getEntityName();
        if (null === $name) {
            $controller = $c->getShortName();
            $name = str_replace("Controller", "", $controller);
        }

        return $name;
    }

    /**
     * @param Action $actionAnnotation
     * @param \ReflectionMethod $m
     *
     * @return string
     * @throws \LogicException
     */
    private function getActionName(Action $actionAnnotation, \ReflectionMethod $m)
    {
        $action = $actionAnnotation->getAction();
        if (null === $action) {
            $method = $m->getName();
            $action = str_replace("Action", "", $method);
        }
        if (! in_array($action, array('index', 'show', 'new', 'edit', 'delete'))) {
            $message  = "The action '" . $action;
            $message .= "' is not one of the allowed CRUD actions ('index', 'show', 'new', 'edit', 'delete').";
            throw new \LogicException($message);
        }

        return $action;
    }

    /**
     * @param Controller    $controllerAnnotation
     * @param Action        $actionAnnotation
     * @param $entityClass
     *
     * @return string
     * @throws \LogicException
     */
    private function getFormType(Action $actionAnnotation, Controller $controllerAnnotation, $entityClass)
    {
        $type = $actionAnnotation->getFormType();
        if (null === $type) {
            $type = $controllerAnnotation->getFormType();
            if (null === $type) {
                $type = str_replace("\\Entity\\", "\\Form\\", $entityClass) . "Type";
            }
        }
        if (! class_exists($type)) {
            $message  = "Connot find form ";
            $message .= $type;
            $message .= ". Please specify the form FQCN using form_type parameter.";
            throw new \LogicException($message);
        }

        return $type;
    }

    /**
     * @param Action            $actionAnnotation
     * @param Controller        $controllerAnnotation
     * @param Request           $request
     * @param \ReflectionClass  $c
     *
     * @return string
     */
    private function getEditRedirectRoute(Action $actionAnnotation, Controller $controllerAnnotation,
                                          Request $request, \ReflectionClass $c)
    {
        $route = $actionAnnotation->getRedirectRoute();
        if (null === $route) {
            $route = $controllerAnnotation->getEditRedirect();
            if (null === $route) {
                $route = $this->getRoutePrefix($c) . "show";
            }
        }

        $this->verifyRouteExists($route);

        return $route;
    }

    /**
     * @param Action            $actionAnnotation
     * @param Controller        $controllerAnnotation
     * @param Request           $request
     * @param \ReflectionClass  $c
     *
     * @return string
     */
    private function getDeleteRedirectRoute(Action $actionAnnotation, Controller $controllerAnnotation,
                                          Request $request, \ReflectionClass $c)
    {
        $route = $actionAnnotation->getRedirectRoute();
        if (null === $route) {
            $route = $controllerAnnotation->getDeleteRedirect();
            if (null === $route) {
                $route = $this->getRoutePrefix($c) . "index";
            }
        }
        $this->verifyRouteExists($route);

        return $route;
    }

    /**
     * @param Action            $actionAnnotation
     * @param Controller        $controllerAnnotation
     * @param Request           $request
     * @param \ReflectionClass  $c
     *
     * @return string
     */
    private function getDeleteRoute(Action $actionAnnotation, Controller $controllerAnnotation,
                                            Request $request, \ReflectionClass $c)
    {
        $route = $actionAnnotation->getDeleteRoute();
        if (null === $route) {
            $route = $controllerAnnotation->getDeleteRoute();
            if (null === $route) {
                $route = $this->getRoutePrefix($c) . "delete";
            }
        }
        $this->verifyRouteExists($route);

        return $route;
    }

    /**
     * @param \ReflectionClass $c
     *
     * @return string
     */
    private function getRoutePrefix(\ReflectionClass $c)
    {
        /** @var NamePrefix $namePrefixAnnotation */
        $namePrefixAnnotation = $this->reader->getClassAnnotation($c, NamePrefix::class);
        return $namePrefixAnnotation->value;
    }

    /**
     * @param  string                  $route
     * @throws RouteNotFoundException
     */
    private function verifyRouteExists($route)
    {
        try {
            $this->router->generate($route);
        } catch (\Exception $e) {
            if ($e instanceof RouteNotFoundException) {
                throw $e;
            }
        }
    }

    /**
     * @param Request $request
     * @param string  $attribute
     * @param string  $value
     */
    private function addRequestAttribute(Request $request, $attribute, $value)
    {
        if (! $request->attributes->has($attribute)) {
            $request->attributes->set($attribute, $value);
        }
    }
}
