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

namespace VIB\FliesBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FlyCrossRepository
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 * 
 */
class FlyCrossRepository extends EntityRepository
{
    /**
     * Return QueryBuilder object finding all living crosses
     * 
     * @return Doctrine\ORM\QueryBuilder
     */
    public function findAllLivingQuery() {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P2M'));
        
        $query = $this->createQueryBuilder('c')
            ->join('c.vial','v')
            ->where('v.setupDate > :date')
            ->andWhere('v.trashed = false')
            ->setParameter('date', $date->format('d.m.y'))
            ->orderBy('v.setupDate', 'DESC')
            ->addOrderBy('c.id', 'DESC');
                
        return $query;
    }
    
        /**
     * Return living cross vials
     * 
     * @return mixed 
     */
    public function findLivingCrossesByName($term) {
        
        $query = $this->findAllLivingQuery()
            ->join('c.virgin','f')
            ->join('c.male','m')
            ->leftJoin('f.stock', 'fs')
            ->leftJoin('m.stock', 'ms')
            ->andWhere('fs.name like :term or ms.name like :term or c.maleName like :term or c.virginName like :term')
            ->setParameter('term', '%' . $term .'%');
                
        return $query;
    }
}