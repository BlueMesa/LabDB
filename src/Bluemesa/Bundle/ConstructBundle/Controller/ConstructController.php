<?php

/*
 * Copyright 2011 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
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

namespace Bluemesa\Bundle\ConstructBundle\Controller;

use Bluemesa\Bundle\ConstructBundle\Entity\Construct;
use Bluemesa\Bundle\ConstructBundle\Form\ConstructType;
use Bluemesa\Bundle\CoreBundle\Controller\RestController;
use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * AntibodyController class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */

/**
 * Class ConstructController
 * @package Bluemesa\Bundle\ConstructBundle\Controller
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 *
 * @Route("/constructs")
 */
class ConstructController extends RestController
{
    /**
     * @REST\View()
     * @REST\Get("/index.{_format}", name="bluemesa_construct_index", defaults={"_format" = "html"})
     *
     * @param  Request     $request
     * @return View
     */
    public function indexAction(Request $request)
    {
        return new View();
    }

    /**
     * @REST\View()
     * @REST\Get("/{id}.{_format}", name="bluemesa_construct_show",
     *     defaults={"_format" = "html"}, requirements={"construct" = "\d+"})
     *
     * @ParamConverter("construct", class="BluemesaConstructBundle:Construct")
     *
     * @param  Request     $request
     * @param  Construct   $construct
     * @return View
     */
    public function showAction(Request $request, Construct $construct)
    {
        return new View(array('construct' => $construct));
    }

    /**
     * @REST\View()
     * @REST\Get("/new.{_format}", name="bluemesa_construct_new",
     *     defaults={"_format" = "html"})
     * @REST\Post("/new.{_format}", name="bluemesa_construct_new_submit",
     *     defaults={"_format" = "html"})
     *
     * @param  Request     $request
     * @return View
     */
    public function newAction(Request $request)
    {
        $construct = new Construct;
        $form = $this->createEditForm($construct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManagerForClass(Construct::class);
            $em->persist($construct);
            $em->flush();

            return $this->routeRedirectView('bluemesa_construct_show',
                array('construct' => $construct->getId()));
        }

        return new View(array('form' => $form));
    }

    /**
     * Displays a form to edit an existing Construct entity.
     *
     * @REST\View()
     * @REST\Get("/{id}/edit.{_format}", name="bluemesa_construct_edit",
     *     defaults={"_format" = "html"})
     * @REST\Post("/{id}.{_format}", name="bluemesa_construct_edit_submit",
     *     defaults={"_format" = "html"})
     *
     * @ParamConverter("construct", class="BluemesaConstructBundle:Construct")
     *
     * @param  Request     $request
     * @return View
     */
    public function editAction(Request $request, Construct $construct)
    {
        $form = $this->createEditForm($construct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManagerForClass(Construct::class);
            $em->persist($construct);
            $em->flush();

            return $this->routeRedirectView('bluemesa_construct_show',
                array('construct' => $construct->getId()));
        }

        return new View(array('form' => $form));
    }

    /**
     * Deletes a Construct entity.
     *
     * @REST\Delete("/{id}/delete.{_format}", name="bluemesa_construct_delete",
     *     defaults={"_format" = "html"})
     */
    public function deleteAction(Request $request, Construct $construct)
    {
        $form = $this->createDeleteForm($construct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManagerForClass(Construct::class);
            $em->remove($construct);
            $em->flush();
        }

        return $this->redirectToRoute('bluemesa_construct_index');
    }

    /**
     * @REST\Post("/_ajax/method/form.{_format}", name="bluemesa_construct_ajaxform",
     *     defaults={"_format" = "html"}, requirements={"_format" = "html"})
     * @REST\RequestParam(name="entity")
     * @REST\RequestParam(name="form")
     *
     * @param  Request      $request
     * @param  ParamFetcher $paramFetcher
     * @return Response
     */
    public function ajaxFormAction(Request $request, ParamFetcher $paramFetcher)
    {
        $entityClass = $paramFetcher->get('entity');
        $formClass = $paramFetcher->get('form');

        $data = array('method' => new $entityClass());
        $builder = $this->container->get('form.factory')
            ->createNamedBuilder('construct', FormType::class, $data)
            ->add('method', $formClass, array(
                'horizontal_label_offset_class' => null,
                'horizontal_input_wrapper_class' => 'construct_method_placeholder',
                'label_render'      => false,
                'widget_form_group' => false
            ))
            ;

        return new Response($this->renderView('BluemesaConstructBundle:Construct:ajaxform.html.twig',
            array('form' => $builder->getForm()->createView())));
    }

    /**
     * Creates a form to edit a Construct entity.
     *
     * @param Construct $construct
     * @return \Symfony\Component\Form\Form
     */
    private function createEditForm(Construct $construct)
    {
        return $this->createForm(ConstructType::class, $construct);
    }

    /**
     * Creates a form to delete a Construct entity.
     *
     * @param Construct $construct The Construct entity
     * @return Form The form
     */
    private function createDeleteForm(Construct $construct)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('bluemesa_construct_delete', array('id' => $construct->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
