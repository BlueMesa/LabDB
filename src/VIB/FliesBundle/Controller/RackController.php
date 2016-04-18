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

namespace VIB\FliesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\SatisfiesParentSecurityPolicy;

use Bluemesa\Bundle\AclBundle\Controller\SecureCRUDController;

use VIB\FliesBundle\Form\RackType;
use VIB\FliesBundle\Form\SelectType;

use VIB\FliesBundle\Entity\Rack;
use VIB\FliesBundle\Entity\Incubator;
use VIB\FliesBundle\Label\PDFLabel;

/**
 * RackController class
 *
 * @Route("/racks")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class RackController extends SecureCRUDController
{
    const ENTITY_CLASS = 'VIB\FliesBundle\Entity\Rack';
    const ENTITY_NAME = 'rack|racks';

    /**
     * {@inheritdoc}
     */
    protected function getEditForm()
    {
        return new RackType();
    }

    /**
     * {@inheritdoc}
     *
     * @SatisfiesParentSecurityPolicy
     */
    public function listAction(Request $request)
    {
        throw $this->createNotFoundException();
    }

    /**
     * Show rack
     *
     * @Route("/show/{id}")
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request           $request
     * @param  mixed                                               $id
     * @return \Symfony\Component\HttpFoundation\Response | array
     */
    public function showAction(Request $request, $id)
    {
        /** @var Rack $rack */
        $rack = $this->getEntity($id);
        $response = parent::showAction($request, $rack);

        $form = $this->createForm(SelectType::class, null, array('class' => 'VIB\FliesBundle\Entity\Vial'));

        if ($request->getMethod() == 'POST') {
            $postForm = $request->request->get('select');
            $action = is_array($postForm) ? $postForm['action'] : '';
            if ($action == 'incubate') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $this->incubateRack($rack, $data['incubator']);
                }
            } else {
                $this->setBatchActionRedirect($request);
                $selectResponse = $this->forward('VIBFliesBundle:Vial:select');
                
                if (($action == 'flip')||($action == 'label')||
                    ($action == 'edit')||($action == 'permissions')||
                    ($selectResponse->getStatusCode() >= 400)) {
                    
                    return $selectResponse;
                }
            }
        }

        return is_array($response) ? array_merge($response, array('form' => $form->createView())) : $response;
    }

    /**
     * Create rack
     *
     * @Route("/new")
     * @Template()
     * @SatisfiesParentSecurityPolicy
     *
     * @param  \Symfony\Component\HttpFoundation\Request           $request
     * @return \Symfony\Component\HttpFoundation\Response | array
     */
    public function createAction(Request $request)
    {
        $om = $this->getObjectManager();
        /** @var Rack $rack */
        $rack = new Rack();
        $data = array('rack' => $rack, 'rows' => 10, 'columns' => 10);
        $form = $this->createForm($this->getCreateForm(), $data);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $rack = $data['rack'];
            $rows = $data['rows'];
            $columns = $data['columns'];

            $rack->setGeometry($rows, $columns);
            $om->persist($rack);
            $om->flush();

            $this->addSessionFlash('success', 'Rack ' . $rack . ' was created.');

            if ($this->getSession()->get('autoprint') == 'enabled') {
                return $this->printLabelAction($rack);
            } else {
                $url = $this->generateUrl('vib_flies_rack_show',array('id' => $rack->getId()));

                return $this->redirect($url);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * Edit rack
     *
     * @Route("/edit/{id}")
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request                         $request
     * @param  mixed                                                             $id
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response | array
     */
    public function editAction(Request $request, $id)
    {
        $om = $this->getObjectManager();
        /** @var Rack $rack */
        $rack = $this->getEntity($id);
        $authorizationChecker = $this->getAuthorizationChecker();

        if (!($authorizationChecker->isGranted('ROLE_ADMIN')||$authorizationChecker->isGranted('EDIT', $rack))) {
            throw new AccessDeniedException();
        }

        $data = array('rack' => $rack, 'rows' => $rack->getRows(), 'columns' => $rack->getColumns());
        $form = $this->createForm($this->getCreateForm(), $data);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $rack = $data['rack'];
            $rows = $data['rows'];
            $columns = $data['columns'];

            $rack->setGeometry($rows, $columns);
            $om->persist($rack);
            $om->flush();

            $this->addSessionFlash('success', 'Changes to rack ' . $rack . ' were saved.');

            $url = $this->generateUrl('vib_flies_rack_show',array('id' => $rack->getId()));

            return $this->redirect($url);
        }

        return array('form' => $form->createView());
    }

    /**
     * Delete rack
     *
     * @Route("/delete/{id}")
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request           $request
     * @param  mixed                                               $id
     * @return \Symfony\Component\HttpFoundation\Response | array
     */
    public function deleteAction(Request $request, $id)
    {
        $response = parent::deleteAction($request, $id);
        $url = $this->generateUrl('vib_flies_welcome_index');

        return is_array($response) ? $response : $this->redirect($url);
    }

    /**
     * Prepare label
     *
     * @param  \VIB\FliesBundle\Entity\Rack     $rack
     * @return \VIB\FliesBundle\Label\PDFLabel
     */
    public function prepareLabel(Rack $rack)
    {
        $pdf = $this->get('vibfolks.pdflabel');
        $pdf->addLabel($rack);

        return $pdf;
    }

    /**
     * Generate rack label
     *
     * @Route("/label/{id}/download")
     *
     * @param  mixed                                       $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadLabelAction($id)
    {
        /** @var Rack $rack */
        $rack = $this->getEntity($id);
        $pdf = $this->prepareLabel($rack);

        return $pdf->outputPDF();
    }

    /**
     * Print rack label
     *
     * @Route("/label/{id}/print")
     *
     * @param  mixed                                       $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function printLabelAction($id)
    {
        /** @var Rack $rack */
        $rack = $this->getEntity($id);
        $pdf = $this->prepareLabel($rack);
        $jobStatus = $pdf->printPDF();
        if ($jobStatus == 'successfull-ok') {
            $this->addSessionFlash('success', 'Label was sent to the printer. ');
        } else {
            $this->addSessionFlash('error', 'There was an error printing labels. The print server said: ' . $jobStatus);
        }
        $url = $this->generateUrl('vib_flies_rack_show',array('id' => $rack->getId()));

        return $this->redirect($url);
    }

    /**
     * Incubate rack
     *
     * @param \VIB\FliesBundle\Entity\Rack       $rack
     * @param \VIB\FliesBundle\Entity\Incubator  $incubator
     */
    public function incubateRack(Rack $rack, Incubator $incubator)
    {
        $om = $this->getObjectManager();
        $rack->setStorageUnit($incubator);
        $om->persist($rack);
        $om->flush();
        $this->addSessionFlash('success', 'Rack ' . $rack . ' was put in ' . $incubator . '.');
    }

    
    protected function setBatchActionRedirect(Request $request, $redirect = null)
    {
        if (null === $redirect) {
            $currentRoute = $request->attributes->get('_route');
            $routeArguments = $request->attributes->get('_route_params', null);
            $redirect = $this->generateUrl($currentRoute, $routeArguments);
        }
        $this->getSession()->set('batch_action_redirect', $redirect);
    }
}
