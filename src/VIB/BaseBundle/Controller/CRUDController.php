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

namespace VIB\BaseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use \ReflectionClass;


/**
 * Base class for CRUD operations CRUDController
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
abstract class CRUDController extends AbstractController {
    
    /**
     * Entity class for this controller
     * 
     * @var string
     */
    protected $entityClass;

    
    /**
     * Construct CRUDController
     *
     */ 
    public function __construct()
    {
        $this->entityClass = null;
    }
        
    /**
     * List entities
     * 
     * @Route("/")
     * @Template()
     * @Secure(roles="ROLE_USER, ROLE_ADMIN")
     * 
     * @param integer $page
     * @return array
     */
    public function listAction()
    {
        $paginator  = $this->get('knp_paginator');
        $page = $this->get('request')->query->get('page', 1);
        $query = $this->get('vib.security.helper.acl')->apply($this->getListQuery());
        $entities = $paginator->paginate($query, $page, 15);
        return array('entities' => $entities);
    }
    
    /**
     * Show entity
     * 
     * @Route("/show/{id}")
     * @Template()
     * 
     * @param mixed $id
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id) {
        $entity = $this->getEntity($id);
        $securityContext = $this->get('security.context');
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('VIEW', $entity))) {
            throw new AccessDeniedException();
        }
        return array('entity' => $entity);
    }    
    
    /**
     * Create entity
     * 
     * @Route("/new")
     * @Template()
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function createAction() {
        $em = $this->getDoctrine()->getManager();
        $class = $this->getEntityClass();
        $entity = new $class();
        $form = $this->createForm($this->getCreateForm(), $entity);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            
            $form->bindRequest($request);
            
            if ($form->isValid()) {
                
                $em->persist($entity);
                $em->flush();

                $this->setACL($entity);
                
                $route = str_replace("_create", "_show", $request->attributes->get('_route'));
                $url = $this->generateUrl($route,array('id' => $entity->getId()));
                return $this->redirect($url);
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * Edit entity
     * 
     * @Route("/edit/{id}")
     * @Template()
     * 
     * @param mixed $id
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getEntity($id);
        $securityContext = $this->get('security.context');
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('EDIT', $entity))) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm($this->getEditForm(), $entity);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {

            $form->bindRequest($request);

            if ($form->isValid()) {

                $em->persist($entity);
                $em->flush();
                
                $route = str_replace("_edit", "_show", $request->attributes->get('_route'));
                $url = $this->generateUrl($route,array('id' => $entity->getId()));
                return $this->redirect($url);
            }
        }
        
        return array('form' => $form->createView());
    }

    /**
     * Delete entity
     * 
     * @Route("/delete/{id}")
     * @Template()
     * 
     * @param mixed $id
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getEntity($id);
        $securityContext = $this->get('security.context');
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('DELETE', $entity))) {
            throw new AccessDeniedException();
        }
        $em->remove($entity);
        $em->flush();
        $request = $this->getRequest();
        $route = str_replace("_delete", "_list", $request->attributes->get('_route'));
        $url = $this->generateUrl($route);
        return $this->redirect($url);
    }
    
    /**
     * Get query returning entities to list
     * 
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getListQuery() {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository($this->getEntityClass())->createQueryBuilder('q');
    }
        
    /**
     * Get entity
     * 
     * @param mixed $id
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \VIB\BaseBundle\Entity\Entity
     */
    protected function getEntity($id) {
        $class = $this->getEntityClass();        
        
        if ($id instanceof $class) {
            return $id;
        }
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($this->getEntityClass())->find($id);
        
        if ($entity instanceof $class) {
            return $entity;
        } else {
            throw new NotFoundHttpException();
        }
        
        return null;
    }
    
    /**
     * Get create form
     * 
     * @return \Symfony\Component\Form\AbstractType
     */
    protected function getCreateForm() {
        return $this->getEditForm();
    }
    
    /**
     * Get edit form
     * 
     * @return \Symfony\Component\Form\AbstractType
     */
    protected function getEditForm() {
        return null;
    }

    /**
     * Set ACL for entity
     * 
     * @param object $entity
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param integer $mask
     */
    protected function setACL($entity, UserInterface $user = null, $mask = MaskBuilder::MASK_OWNER) {
        
        if ($user === null) {
            $user = $this->getUser();
        }
        
        $currentUserIdentity = UserSecurityIdentity::fromAccount($user);
        $adminRoleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');
        $userRoleIdentity = new RoleSecurityIdentity('ROLE_USER');
        $objectIdentity = ObjectIdentity::fromDomainObject($entity);
        $aclProvider = $this->getAclProvider();
        $acl = $aclProvider->createAcl($objectIdentity);
        $acl->insertObjectAce($currentUserIdentity, $mask);
        $acl->insertObjectAce($userRoleIdentity, MaskBuilder::MASK_VIEW);
        $aclProvider->updateAcl($acl);
    }
    
    /**
     * Get managed entity class
     * 
     * @return string
     */
    protected function getEntityClass() {
        return $this->entityClass;
    }
    
    /**
     * Check if entity is controlled by this controller
     * 
     * @param object $entity
     * @return boolean
     */
    protected function controls($entity) {
        return $this->getEntityClass() == (new ReflectionClass($entity))->getName();
    }
}
?>