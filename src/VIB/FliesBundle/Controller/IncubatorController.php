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

use Bluemesa\Bundle\AclBundle\Controller\SecureCRUDController;

use VIB\FliesBundle\Form\IncubatorType;

/**
 * IncubatorController class
 *
 * @Route("/incubators")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class IncubatorController extends SecureCRUDController
{
    const ENTITY_CLASS = 'VIB\FliesBundle\Entity\Incubator';
    const ENTITY_NAME = 'incubator|incubators';
    

    /**
     * {@inheritdoc}
     */
    protected function getEditForm()
    {
        return new IncubatorType();
    }

    /**
     * {@inheritdoc}
     *
     * @SatisfiesParentSecurityPolicy
     */
    public function listAction()
    {
        throw $this->createNotFoundException();
    }

    /**
     * Delete incubator
     *
     * @Route("/delete/{id}")
     * @Template()
     *
     * @param  mixed                                     $id
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $response = parent::deleteAction($id);
        $url = $this->generateUrl('vib_flies_welcome_index');

        return is_array($response) ? $response : $this->redirect($url);
    }
    
    /**
     * Generate links for putting stuff into incubator
     *
     * @Template()
     *
     * @return array
     */
    public function incubateAction()
    {
        return $this->menuAction();
    }

    /**
     * Generate links for incubator menu
     *
     * @Template()
     *
     * @return array
     */
    public function menuAction()
    {
        $entities = $this->getObjectManager(self::ENTITY_CLASS)->findAll($this->getEntityClass());

        return array('entities' => $entities);
    }
}
