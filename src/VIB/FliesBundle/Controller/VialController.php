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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\SatisfiesParentSecurityPolicy;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Bluemesa\Bundle\AclBundle\Controller\SecureCRUDController;
use Bluemesa\Bundle\CoreBundle\Filter\RedirectFilterInterface;

use VIB\FliesBundle\Doctrine\VialManager;
use VIB\FliesBundle\Filter\VialFilter;
use VIB\FliesBundle\Form\BatchVialAclType;
use VIB\FliesBundle\Label\PDFLabel;

use VIB\FliesBundle\Form\VialType;
use VIB\FliesBundle\Form\VialNewType;
use VIB\FliesBundle\Form\VialExpandType;
use VIB\FliesBundle\Form\SelectType;
use VIB\FliesBundle\Form\VialGiveType;
use VIB\FliesBundle\Form\BatchVialType;

use VIB\FliesBundle\Entity\Vial;
use VIB\FliesBundle\Entity\Incubator;
use VIB\UserBundle\Entity\User;

/**
 * VialController class
 *
 * @Route("/vials")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class VialController extends SecureCRUDController
{
    const ENTITY_CLASS = 'VIB\FliesBundle\Entity\Vial';
    const ENTITY_NAME = 'vial|vials';
    

    /**
     * {@inheritdoc}
     */
    protected function getCreateForm()
    {
        return new VialNewType();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditForm()
    {
        return new VialType();
    }

    /**
     * List vials
     *
     * @Route("/")
     * @Route("/list/{access}/{filter}", defaults={"access" = "private", "filter" = "living"})
     * @Route("/list/{access}/{filter}/sort/{sort}/{order}", defaults={"access" = "private", "filter" = "living", "sort" = "setup", "order" = "asc"})
     * @Template()
     * @SatisfiesParentSecurityPolicy
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $response = parent::listAction($request);
        $this->setBatchActionRedirect($request);
        $formResponse = $this->handleSelectForm($request, new SelectType($this->getEntityClass()));

        if ($response instanceof Response) {
            
            return $response;
        } 
        
        if ($formResponse instanceof Response) {
            
            return $formResponse;
        } 
        
        return ((is_array($response))&&(is_array($formResponse))) ?
            array_merge($response, $formResponse) : $response;
    }

    /**
     * Show vial
     *
     * @Route("/show/{id}")
     * @Template()
     *
     * @param   \Symfony\Component\HttpFoundation\Request   $request
     * @param   mixed                                       $id
     * @return  \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, $id)
    {
        /** @var Vial $vial */
        $vial = $this->getEntity($id);
        if ($this->controls($vial)) {

            return parent::showAction($request, $vial);
        } else {

            return $this->getVialRedirect($request, $vial);
        }
    }
    
    /**
     * Create vial
     *
     * @Route("/new")
     * @Template()
     * @SatisfiesParentSecurityPolicy
     *
     * @param  \Symfony\Component\HttpFoundation\Request                      $request
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();

        $class = $this->getEntityClass();
        
        if ($class == 'VIB\FliesBundle\Entity\Vial') {
            throw $this->createNotFoundException();
        }

        $vial = new $class();
        $data = array('vial' => $vial, 'number' => 1);
        $form = $this->createForm($this->getCreateForm(), $data);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $vial = $data['vial'];
            $number = $data['number'];

            $vials = $om->expand($vial, $number, false);
            $om->flush();

            $message = (($count = count($vials)) == 1) ?
                ucfirst($this->getEntityName()) . ' ' . $vials[0] . ' was created.' :
                ucfirst($count . ' ' . $this->getEntityPluralName()) . ' were created.';
            $this->addSessionFlash('success', $message);

            $this->autoPrint($vials);

            if ($count == 1) {
                $route = str_replace("_create", "_show", $request->attributes->get('_route'));
                /** @var Vial $firstVial */
                $firstVial = $vials[0];
                $url = $this->generateUrl($route,array('id' => $firstVial->getId()));
            } else {
                $route = str_replace("_create", "_list", $request->attributes->get('_route'));
                $url = $this->generateUrl($route);
            }

            return $this->redirect($url);
        }

        return array('form' => $form->createView());
    }

    /**
     * Edit vial
     *
     * @Route("/edit/{id}")
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @param  mixed                                       $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        /** @var Vial $vial */
        $vial = $this->getEntity($id);
        if ($this->controls($vial)) {
            return parent::editAction($request, $vial);
        } else {
            return $this->getVialRedirect($request, $vial);
        }
    }

    /**
     * Expand vial
     *
     * @Route("/expand/{id}", defaults={"id" = null})
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request         $request
     * @param  mixed                                             $id
     * @return \Symfony\Component\HttpFoundation\Response|array
     */
    public function expandAction(Request $request, $id = null)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        /** @var Vial $source */
        $source = (null !== $id) ? $this->getEntity($id) : null;
        $data = array(
            'source' => $source,
            'number' => 1,
            'size' => null !== $source ? $source->getSize() : 'medium',
            'food' => null !== $source ? $source->getFood() : 'Normal'
        );
        $form = $this->createForm(new VialExpandType(), $data);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $source = $data['source'];
            $number = $data['number'];
            $size = $data['size'];
            $food = $data['food'];

            $vials = $om->expand($source, $number, true, $size, $food);
            $om->flush();

            $message = (($count = count($vials)) == 1) ?
                ucfirst($this->getEntityName()) . ' ' . $source . ' was flipped.' :
                ucfirst($this->getEntityName()) . ' ' . $source . ' was expanded into ' . $count . ' vials.';
            $this->addSessionFlash('success', $message);

            $this->autoPrint($vials);

            $route = str_replace("_expand", "_list", $request->attributes->get('_route'));
            $url = $this->generateUrl($route);

            return $this->redirect($url);
        }

        return array('form' => $form->createView(), 'cancel' => 'vib_flies_vial_list');
    }
    
    /**
     * Expand vial
     *
     * @Route("/give/{id}", defaults={"id" = null})
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request                         $request
     * @param  mixed                                                             $id
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \InvalidArgumentException
     * @return \Symfony\Component\HttpFoundation\Response|array
     */
    public function giveAction(Request $request, $id = null)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $authorizationChecker = $this->getAuthorizationChecker();
        /** @var Vial $source */
        $source = (null !== $id) ? $this->getEntity($id) : null;
        $data = array(
            'source' => $source,
            'user' => null,
            'type' => 'give',
            'size' => null !== $source ? $source->getSize() : 'medium',
            'food' => null !== $source ? $source->getFood() : 'Normal'
        );
        $form = $this->createForm(new VialGiveType(), $data);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $source = $data['source'];
            $user = $data['user'];
            $type = $data['type'];
            $size = $data['size'];
            $food = $data['food'];

            if (! $user instanceof User) {
                throw new \InvalidArgumentException(sprintf("User object must be an instance of VIB\\UserBundle\\Entity\\User"));
            }

            if (!($authorizationChecker->isGranted('OWNER', $source)||$authorizationChecker->isGranted('ROLE_ADMIN'))) {
                throw new AccessDeniedException();
            }
            
            $om->disableAutoAcl();
            
            switch($type) {
                case 'flip':
                    $vial = $om->flip($source);
                    $vial->setPosition($source->getPosition());
                    $vial->setSize($size);
                    $vial->setFood($food);
                    $om->persist($vial);
                    $om->persist($source);
                    break;
                case 'flipped':
                    $vial = $om->flip($source);
                    $vial->setSize($size);
                    $vial->setFood($food);
                    $om->persist($vial);
                    break;
                default:
                    $vial = null;
            }
            
            $om->flush();
            $vials = new ArrayCollection();
            
            switch($type) {
                case 'flip':
                    $om->createACL($vial);
                    $om->setOwner($source, $user);
                    $vials->add($vial);
                    $vials->add($source);
                    $given = $source;
                    break;
                case 'give':
                    $om->setOwner($source, $user);
                    $vials->add($source);
                    $given = $source;
                    break;
                case 'flipped':
                    $om->createACL($vial, $user);
                    $vials->add($vial);
                    $given = $vial;
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf("Invalid operation type '%s' - valid types are 'flip', 'give' and 'flipped'", $type));
            }
            
            $om->enableAutoAcl();
            
            $message = ($type != 'give') ?
                ucfirst($this->getEntityName()) . ' ' . $source . ' was flipped into '
                    . $this->getEntityName() . ' ' . $vial . ". " : '';
            $message .= 
                ucfirst($this->getEntityName()) . ' ' . $given . ' was given to ' . $user->getFullName() . '.';
            $this->addSessionFlash('success', $message);

            $this->autoPrint($vials);
            
            $route = str_replace("_give", "_list", $request->attributes->get('_route'));
            $url = $this->generateUrl($route);

            return $this->redirect($url);
        }

        return array('form' => $form->createView(), 'cancel' => 'vib_flies_vial_list');
    }

    /**
     * Select vials
     *
     * @Route("/select")
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectAction(Request $request)
    {
        $response = array();
        $this->setBatchActionRedirect($request, null);
        $formResponse = $this->handleSelectForm($request, new SelectType('VIB\FliesBundle\Entity\Vial'));

        return is_array($formResponse) ? array_merge($response, $formResponse) : $formResponse;
    }

    /**
     * Handle batch action
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @param  array                                       $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleBatchAction(Request $request, $data)
    {
        $action = $data['action'];
        $vials = new ArrayCollection($data['items']);
        $response = $this->getDefaultBatchResponse($request);

        switch ($action) {
            case 'label':
                $response = $this->downloadLabels($request, $vials);
                break;
            case 'print':
                $this->printLabels($vials);
                $response = $this->getBackBatchResponse($request);
                break;
            case 'flip':
                $this->flipVials($vials);
                break;
            case 'fliptrash':
                $this->flipVials($vials, true);
                break;
            case 'trash':
                $this->trashVials($vials);
                $response = $this->getBackBatchResponse($request);
                break;
            case 'untrash':
                $this->untrashVials($vials);
                break;
            case 'incubate':
                $incubator = $data['incubator'];
                $this->incubateVials($vials, $incubator);
                $response = $this->getBackBatchResponse($request);
                break;
            case 'edit':
                $response = $this->editVials($request, $vials);
                break;
            case 'permissions':
                $response = $this->permissionsVials($request, $vials);
                break;
        }

        return $response;
    }

    /**
     * Handle selection form
     *
     * @param  \Symfony\Component\HttpFoundation\Request         $request
     * @param  \Symfony\Component\Form\AbstractType              $formType
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    protected function handleSelectForm(Request $request, AbstractType $formType)
    {
        $form = $this->createForm($formType);
        $form->handleRequest($request);
        if ($form->isValid()) {

            return $this->handleBatchAction($request, $form->getData());
        }

        return array('form' => $form->createView());
    }

    /**
     * Prepare vial labels
     *
     * @param  mixed                            $vials
     * @return \VIB\FliesBundle\Label\PDFLabel
     */
    protected function prepareLabels($vials)
    {
        $labelMode = ($this->getSession()->get('labelmode','std') == 'alt');
        /** @var \VIB\FliesBundle\Label\PDFLabel $pdf */
        $pdf = $this->get('vibfolks.pdflabel');
        $pdf->addLabel($vials, $labelMode);

        return $pdf;
    }

    /**
     * Submit print job
     *
     * @param  \VIB\FliesBundle\Label\PDFLabel $pdf
     * @param  integer                        $count
     * @return boolean
     */
    protected function submitPrintJob(PDFLabel $pdf, $count = 1)
    {
        $jobStatus = $pdf->printPDF();
        if ($jobStatus == 'successfull-ok') {
            if ($count == 1) {
                $this->addSessionFlash('success', 'Label for 1 vial was sent to the printer.');
            } else {
                $this->addSessionFlash('success', 'Labels for ' . $count . ' vials were sent to the printer.');
            }

            return true;
        } else {
            $this->addSessionFlash('danger', 'There was an error printing labels. The print server said: ' . $jobStatus);

            return false;
        }
    }

    /**
     * Generate vial labels
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @param  mixed                                       $vials
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function downloadLabels(Request $request, $vials)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $count = ($vials instanceof Collection) ? count($vials) : (($vials instanceof Vial) ? 1 : 0);
        $pdf = $this->prepareLabels($vials);
        if ($count > 0) {
            $om->markPrinted($vials);
            $om->flush();
        } else {
            return $this->getDefaultBatchResponse($request);
        }

        return $pdf->outputPDF();
    }

    /**
     * Print vial labels
     *
     * @param mixed $vials
     */
    protected function printLabels($vials)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $count = ($vials instanceof Collection) ? count($vials) : (($vials instanceof Vial) ? 1 : 0);
        $pdf = $this->prepareLabels($vials);
        if (($count > 0)&&($this->submitPrintJob($pdf, $count))) {
            $om->markPrinted($vials);
            $om->flush();
        }
    }

    /**
     * Automatically print vial labels if requested
     *
     * @param mixed $vials
     */
    protected function autoPrint($vials)
    {
        if ($this->getSession()->get('autoprint') == 'enabled') {
            $this->printLabels($vials);
        }
    }

    /**
     * Flip vials
     *
     * @param \Doctrine\Common\Collections\Collection $vials
     * @param boolean                                 $trash
     */
    public function flipVials(Collection $vials, $trash = false)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $flippedVials = $om->flip($vials, true, $trash);
        $om->flush();
        
        if (($count = count($flippedVials)) == 1) {
            $this->addSessionFlash('success', '1 vial was flipped.' .
                                   ($trash ? ' Source vial was trashed.' : ''));
        } else {
            $this->addSessionFlash('success', $count . ' vials were flipped.' .
                                   ($trash ? ' Source vials were trashed.' : ''));
        }
        $this->autoPrint($flippedVials);
    }

    /**
     * Trash vials
     *
     * @param \Doctrine\Common\Collections\Collection $vials
     */
    public function trashVials(Collection $vials)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $om->trash($vials);
        $om->flush();
        if (($count = count($vials)) == 1) {
            $this->addSessionFlash('success', '1 vial was trashed.');
        } else {
            $this->addSessionFlash('success', $count . ' vials were trashed.');
        }
    }

    /**
     * Untrash vials
     *
     * @param \Doctrine\Common\Collections\Collection $vials
     */
    public function untrashVials(Collection $vials)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $om->untrash($vials);
        $om->flush();
        if (($count = count($vials)) == 1) {
            $this->addSessionFlash('success', '1 vial was removed from trash.');
        } else {
            $this->addSessionFlash('success', $count . ' vials were removed from trash.');
        }
    }

    /**
     * Incubate vials
     *
     * @param \Doctrine\Common\Collections\Collection $vials
     * @param \VIB\FliesBundle\Entity\Incubator       $incubator
     */
    public function incubateVials(Collection $vials, Incubator $incubator)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $om->incubate($vials, $incubator);
        $om->flush();
        if (($count = count($vials)) == 1) {
            $this->addSessionFlash('success', '1 vial was put in ' . $incubator . '.');
        } else {
            $this->addSessionFlash('success', $count . ' vials were put in ' . $incubator . '.');
        }
    }
    
    /**
     * Batch edit vials
     *
     * @Route("/batch/edit")
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @param \Doctrine\Common\Collections\Collection      $vials
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editVials(Request $request, Collection $vials = null)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $authorizationChecker = $this->getAuthorizationChecker();
        $template = array(
            'setupDate' => null,
            'flipDate' => null,
            'size' => null,
            'food' => null
        );
        $removed = 0;
        if (null !== $vials) {
            foreach ($vials as $vial) {
                if (!($authorizationChecker->isGranted('ROLE_ADMIN')||$authorizationChecker->isGranted('EDIT', $vial))) {
                    $vials->removeElement($vial);
                    $removed++;
                }
            }
        } else {
            $vials = new ArrayCollection();
        }
                
        if ($removed > 0) {
            if ($removed == 1) {
                $this->addSessionFlash('danger', 'You do not have sufficient permissions to edit 1 vial.'
                        . ' Changes will not apply to this vial.');
            } else {
                $this->addSessionFlash('danger', 'You do not have sufficient permissions to edit ' . $removed . ' vials.'
                        . ' Changes will not apply to these vials.');
            }
        }
                
        $data = array(
            'template' => $template,
            'vials' => $vials
        );
        
        $form = $this->createForm(new BatchVialType(), $data);
        $action = 'editvials';
        
        if (substr($request->get('_route'), -strlen($action)) === $action) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                
                $data = $form->getData();
                $template = $data['template'];
                $vials = $data['vials'];

                /** @var Vial $vial */
                foreach ($vials as $vial) {
                    if (null !== ($setupDate = $template['setupDate'])) {
                        $vial->setSetupDate($setupDate);
                    }
                    if (null !== ($flipDate = $template['flipDate'])) {
                        $vial->setStoredFlipDate($flipDate);
                    }
                    if (null !== ($size = $template['size'])) {
                        $vial->setSize($size);
                    }
                    if (null !== ($food = $template['food'])) {
                        $vial->setFood($food);
                    }
                    
                    $om->persist($vial);
                }
                
                $om->flush();
                
                if (($count = count($vials)) == 1) {
                    $this->addSessionFlash('success', 'Changes to 1 vial were saved.');
                } else {
                    $this->addSessionFlash('success', 'Changes to ' . $count . ' vials were saved.');
                }

                return $this->getBackBatchResponse($request);
            }
        } else {
            if (count($vials) == 0) {
                $this->addSessionFlash('danger', 'There was nothing to edit.');

                return $this->getBackBatchResponse($request);
            }
        }
        
        $controller = $this->getCurrentController($request);
        
        return $this->render('VIBFliesBundle:' . $controller . ':batch_edit.html.twig', array('form' => $form->createView()));
    }
    
    /**
     * Batch change permissions for vials
     *
     * @Route("/batch/permissions")
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @param  \Doctrine\Common\Collections\Collection     $vials
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function permissionsVials(Request $request, Collection $vials = null)
    {
        /** @var VialManager $om */
        $om = $this->getObjectManager();
        $acl_array = $om->getDefaultACL();
        $authorizationChecker = $this->getAuthorizationChecker();
        $removed = 0;
        if (null !== $vials) {
            foreach ($vials as $vial) {
                if (!($authorizationChecker->isGranted('ROLE_ADMIN')||$authorizationChecker->isGranted('MASTER', $vial))) {
                    $vials->removeElement($vial);
                    $removed++;
                }
            }
        } else {
            $vials = new ArrayCollection();
        }
                
        if ($removed > 0) {
            if ($removed == 1) {
                $this->addSessionFlash('danger', 'You do not have sufficient permissions to change permissions'
                        . ' for 1 vial. Changes will not apply to this vial.');
            } else {
                $this->addSessionFlash('danger', 'You do not have sufficient permissions to change permissions'
                        . ' for ' . $removed . ' vials. Changes will not apply to these vials.');
            }
        }

        $acl = array(
            'user_acl' => array(),
            'role_acl' => array()
        );
                
        foreach($acl_array as $acl_entry) {
            $identity = $acl_entry['identity'];
            if ($identity instanceof UserInterface) {
                $acl['user_acl'][] = $acl_entry;
            } else if (is_string($identity)) {
                $acl['role_acl'][] = $acl_entry;
            }
        }
        
        $data = array(
            'acl' => $acl,
            'vials' => $vials
        );
        
        $form = $this->createForm(new BatchVialAclType(), $data);
        
        $action = 'permissionsvials';
        
        if (substr($request->get('_route'), -strlen($action)) === $action) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                
                $data = $form->getData();
                $acl = $data['acl'];
                $acl_array = array_merge($acl['user_acl'], $acl['role_acl']);
                $vials = $data['vials'];
                
                foreach ($vials as $vial) {
                    $om->updateACL($vial, $acl_array);
                }
                                
                if (($count = count($vials)) == 1) {
                    $this->addSessionFlash('success', 'Changes to 1 vial permissions were saved.');
                } else {
                    $this->addSessionFlash('success', 'Changes to ' . $count . ' vials permissions were saved.');
                }
                
                return $this->getBackBatchResponse($request);
            }
        } else {
            if (count($vials) == 0) {
                $this->addSessionFlash('danger', 'There was nothing to edit.');

                return $this->getBackBatchResponse($request);
            }
        }
        
        $controller = $this->getCurrentController($request);
        
        return $this->render('VIBFliesBundle:' . $controller . ':batch_permissions.html.twig', array('form' => $form->createView()));        
    }
    
    /**
     * Get default batch action response
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getDefaultBatchResponse($request)
    {
        $currentRoute = $request->attributes->get('_route');

        if ($currentRoute == '') {
            $url = $this->generateUrl('vib_flies_vial_list');

            return $this->redirect($url);
        }

        $pieces = explode('_',$currentRoute);
        
        if (is_numeric($pieces[count($pieces) - 1])) {
            array_pop($pieces);
        }
        $pieces[count($pieces) - 1] = 'list';
        $route = ($currentRoute == 'default') ? 'default' : implode('_', $pieces);
        $url = $this->generateUrl($route);

        return $this->redirect($url);
    }
    
    /**
     * Get back to where batch job has started

     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getBackBatchResponse(Request $request)
    {
        if (null === ($url = $this->getSession()->get('batch_action_redirect'))) {
            $currentRoute = $request->attributes->get('_route');
            $routeArguments = $request->attributes->get('_route_params', null);

            if ($currentRoute == '') {
                $url = $this->generateUrl('vib_flies_vial_list');

                return $this->redirect($url);
            }

            $pieces = explode('_',$currentRoute);

            if (in_array('select', $pieces)) {
                $pieces[count($pieces) - 1] = 'list';
            }

            $route = ($currentRoute == 'default') ? 'default' : implode('_', $pieces);
            $url = $this->generateUrl($route, $routeArguments);
        }
        
        $this->getSession()->set('batch_action_redirect', null);
        
        return $this->redirect($url);
    }

    protected function getCurrentController(Request $request)
    {        
        $pattern = "/Controller\\\([a-zA-Z]*)Controller/";
        $matches = array();
        preg_match($pattern, $request->get('_controller'), $matches);
        
        if (isset($matches[1])) {
            $controller = $matches[1];
        } else {
            $controller = 'Vial';
        }
        
        return $controller;
    }
    
    protected function setBatchActionRedirect(Request $request, $redirect = false)
    {        
        if (false === $redirect) {
            $currentRoute = $request->attributes->get('_route');
            if ($currentRoute == '') {
                return;
            }
            $routeArguments = $request->attributes->get('_route_params', null);
            $redirect = $this->generateUrl($currentRoute, $routeArguments);
        }
        
        if ((null == $redirect)&&($request->attributes->get('_route') == '')) {
            return;
        }
        
        $this->getSession()->set('batch_action_redirect', $redirect);
    }
    
    /**
     *
     *
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @param  \VIB\FliesBundle\Entity\Vial                $vial
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getVialRedirect(Request $request, Vial $vial)
    {
        $route = str_replace("_vial_", "_" . $vial->getType() . "vial_", $request->attributes->get('_route'));
        $url = $this->generateUrl($route, array('id' => $vial->getId()));

        return $this->redirect($url);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getFilterRedirect(Request $request, RedirectFilterInterface $filter)
    {
        $currentRoute = $request->attributes->get('_route');
        
        if ($currentRoute == '') {
            $route = 'vib_flies_vial_list_2';
        } else {
            $pieces = explode('_',$currentRoute);
            if (! is_numeric($pieces[count($pieces) - 1])) {
                $pieces[] = '2';
            }
            $route = ($currentRoute == 'default') ? 'vib_flies_vial_list_2' : implode('_', $pieces);
        }

        $routeParameters = ($filter instanceof VialFilter) ?
            array(
                'access' => $filter->getAccess(),
                'filter' => $filter->getFilter(),
                'sort' => $filter->getSort(),
                'order' => $filter->getOrder()) :
            array();
        
        $url = $this->generateUrl($route, $routeParameters);
        
        return $this->redirect($url);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getFilter(Request $request)
    {
        return new VialFilter($request, $this->getAuthorizationChecker(), $this->getTokenStorage());
    }
}
