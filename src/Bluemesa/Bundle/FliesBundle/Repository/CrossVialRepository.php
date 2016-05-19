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

namespace Bluemesa\Bundle\FliesBundle\Repository;

use JMS\DiExtraBundle\Annotation as DI;

use Bluemesa\Bundle\CoreBundle\Doctrine\ObjectManager;
use Bluemesa\Bundle\CoreBundle\Filter\ListFilterInterface;
use Bluemesa\Bundle\SearchBundle\Search\SearchQueryInterface;
use Bluemesa\Bundle\FliesBundle\Filter\VialFilter;
use Bluemesa\Bundle\FliesBundle\Search\SearchQuery;
use Bluemesa\Bundle\FliesBundle\Entity\CrossVial;

/**
 * CrossVialRepository
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrossVialRepository extends SearchableVialRepository
{
    /**
     * {@inheritdoc}
     * 
     * @DI\InjectParams({ "objectManager" = @DI\Inject("bluemesa.doctrine.crossvial_manager") })
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        parent::setObjectManager($objectManager);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getListQueryBuilder(ListFilterInterface $filter = null)
    {
        return parent::getListQueryBuilder($filter)
            ->addSelect('m, v')
            ->leftJoin('e.male', 'm')
            ->leftJoin('e.virgin', 'v');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getSearchFields(SearchQueryInterface $search)
    {
        $fields = array('e.maleName', 'e.virginName');
        if ($search instanceof SearchQuery ? $search->searchNotes() : false) {
            $fields[] = 'e.notes';
        }
        
        return $fields;
    }
    
    /**
     * Find similar crosses
     *
     * @param CrossVial $cross
     */
    public function findSimilar(CrossVial $cross)
    {
        $filter = new VialFilter();
        $filter->setAccess('shared');
        $filter->setFilter('all');

        $startDate = clone $cross->getSetupDate();
        $stopDate = clone $cross->getSetupDate();
        $startDate->sub(new \DateInterval('P2W'));
        $stopDate->add(new \DateInterval('P2W'));

        $owner = $this->getObjectManager()->getOwner($cross);

        $qb = $this->getListQueryBuilder($filter);
        $qb->andWhere('e.maleName = :male_name')
           ->andWhere('e.virginName = :virgin_name')
           ->andWhere('e.setupDate > :start_date')
           ->andWhere('e.setupDate <= :stop_date')
           ->orderBy('e.setupDate', 'ASC')
           ->addOrderBy('e.id', 'ASC')
           ->setParameter('male_name', $cross->getUnformattedMaleName())
           ->setParameter('virgin_name', $cross->getUnformattedVirginName())
           ->setParameter('start_date', $startDate->format('Y-m-d'))
           ->setParameter('stop_date', $stopDate->format('Y-m-d'));

        return $this->getAclFilter()->apply($qb, $filter->getPermissions(), $owner)->getResult();
    }
}
