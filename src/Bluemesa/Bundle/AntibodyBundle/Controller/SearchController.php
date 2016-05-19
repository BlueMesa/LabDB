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

namespace Bluemesa\Bundle\AntibodyBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Bluemesa\Bundle\AclBundle\Controller\SecureController;

use Bluemesa\Bundle\SearchBundle\Controller\SearchController as BaseSearchController;
use Bluemesa\Bundle\AntibodyBundle\Search\SearchQuery;
use Bluemesa\Bundle\AntibodyBundle\Form\SearchType;
use Bluemesa\Bundle\AntibodyBundle\Form\AdvancedSearchType;

/**
 * Search controller for the antibody bundle
 *
 * @Route("/search")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class SearchController extends BaseSearchController
{
    use SecureController;

    /**
     * {@inheritdoc}
     */
    protected function getSearchForm()
    {
        return new SearchType();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getAdvancedSearchForm()
    {
        return new AdvancedSearchType();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getSearchRealm()
    {
        return 'bluemesa_antibodies';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function createSearchQuery($advanced = false)
    {
        $searchQuery = new SearchQuery($advanced);
        $searchQuery->setTokenStorage($this->getTokenStorage());
        
        return $searchQuery;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadSearchQuery()
    {
        $searchQuery = parent::loadSearchQuery();
        
        if (! $searchQuery instanceof SearchQuery) {
            throw $this->createNotFoundException();
        }
        
        $searchQuery->setTokenStorage($this->getTokenStorage());
        
        return $searchQuery;
    }
}
