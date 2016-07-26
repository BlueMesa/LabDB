<?php

/*
 * This file is part of the ConstructBundle.
 *
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\Bundle\ConstructBundle\Repository;

use Bluemesa\Bundle\SearchBundle\Repository\SearchableRepository;
use Bluemesa\Bundle\SearchBundle\Search\SearchQueryInterface;

/**
 * ConstructRepository
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ConstructRepository extends SearchableRepository
{
    /**
     * {@inheritdoc}
     */
    protected function getSearchFields(SearchQueryInterface $search)
    {
        $fields = array('e.name');
        
        return $fields;
    }
}
