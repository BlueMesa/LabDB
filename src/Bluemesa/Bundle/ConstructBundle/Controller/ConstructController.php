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

use Bluemesa\Bundle\ConstructBundle\Entity\CloningMethod;
use Bluemesa\Bundle\ConstructBundle\Entity\Construct;
use Bluemesa\Bundle\ConstructBundle\Entity\RestrictionLigation;
use Bluemesa\Bundle\ConstructBundle\Form\ConstructType;
use Bluemesa\Bundle\ConstructBundle\Form\RestrictionLigationType;
use Bluemesa\Bundle\CoreBundle\Controller\RestController;
use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * AntibodyController class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ConstructController extends RestController
{
    /**
     * @REST\View()
     * @REST\Get("/constructs.{_format}", defaults={"_format" = "html"})
     *
     * @param  Request     $request
     * @return View
     */
    public function getConstructsAction(Request $request)
    {
        return new View();
    }

    /**
     * @REST\View()
     * @REST\Get("/constructs/{construct}.{_format}",
     *     defaults={"_format" = "html"}, requirements={"construct" = "\d+"})
     *
     * @ParamConverter("construct", class="BluemesaConstructBundle:Construct", options={"id" = "construct"})
     *
     * @param  Request     $request
     * @param  Construct   $construct
     * @return View
     */
    public function getConstructAction(Request $request, Construct $construct)
    {
        return new View(array('construct' => $construct));
    }

    /**
     * @REST\View()
     * @REST\Get("/constructs/new.{_format}", defaults={"_format" = "html"})
     * @REST\Post("/constructs/new.{_format}", defaults={"_format" = "html"})
     *
     * @param  Request     $request
     * @return View
     */
    public function postConstructAction(Request $request)
    {
        $construct = new Construct;
        //$construct->setMethod(new RestrictionLigation());
        $form = $this->createForm(ConstructType::class, $construct);
        $form->handleRequest($request);

        return new View(array('form' => $form));
    }

    /**
     * @REST\Post("/_ajax/constructs/method/form.{_format}",
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
            ));

        $template = $this->container->get('sensio_framework_extra.view.guesser')
            ->guessTemplateName(array($this, __FUNCTION__), $request, 'twig');

        return new Response($this->renderView($template, array('form' => $builder->getForm()->createView())));
    }
}
