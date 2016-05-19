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

namespace Bluemesa\Bundle\FliesBundle\Filter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Bluemesa\Bundle\AclBundle\Filter\SecureListFilter;
use Bluemesa\Bundle\CoreBundle\Filter\SortFilterInterface;
use Bluemesa\Bundle\CoreBundle\Filter\RedirectFilterInterface;

/**
 * Description of VialFilter
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class StockFilter extends SecureListFilter implements SortFilterInterface, RedirectFilterInterface {
    
    protected $sort;
    
    protected $order;
    
    protected $redirect;

    /**
     * {@inheritdoc}
     */
    public function __construct(Request $request = null,
                                AuthorizationCheckerInterface $authorizationChecker = null,
                                TokenStorageInterface $tokenStorage = null)
    {
        parent::__construct($request, $authorizationChecker, $tokenStorage);

        if (null !== $request) {
            $this->setAccess($request->get('access', 'mtnt'));
            $this->setOrder($request->get('order', 'asc'));
            $this->setSort($request->get('sort', 'name'));
            $this->redirect = ($request->get('resolver', 'off') == 'on');
        } else {
            $this->access = 'mtnt';
            $this->sort = 'name';
            $this->order = 'asc';
            $this->redirect = false;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSort() {
        return $this->sort;
    }

    /**
     * {@inheritdoc}
     */
    public function setSort($sort) {
        $this->sort = $sort;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder($order) {
        $this->order = $order;
    }
    
    /**
     * {@inheritdoc}
     */
    public function needRedirect()
    {
        return $this->redirect;
    }
}
