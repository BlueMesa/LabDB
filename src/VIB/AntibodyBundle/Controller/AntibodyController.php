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

namespace VIB\AntibodyBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Bluemesa\Bundle\AclBundle\Controller\SecureCRUDController;

use VIB\AntibodyBundle\Entity\Antibody;
use VIB\AntibodyBundle\Entity\Application;
use VIB\AntibodyBundle\Form\AntibodyType;

/**
 * AntibodyController class
 *
 * @Route("/")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class AntibodyController extends SecureCRUDController
{
    const ENTITY_CLASS = 'VIB\AntibodyBundle\Entity\Antibody';
    const ENTITY_NAME = 'antibody|antibodies';

    /**
     * Construct AntibodyController
     *
     */
    public function __construct()
    {
        $antibody = new Antibody;
        $application = new Application;
        
        $antibody->addApplication($application);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditForm()
    {
        return new AntibodyType();
    }
}
