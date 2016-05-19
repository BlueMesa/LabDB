<?php

/*
 * Copyright 2013 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
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

namespace Bluemesa\Bundle\FliesBundle\Doctrine;

use JMS\DiExtraBundle\Annotation as DI;

use Doctrine\Common\Collections\Collection;
use Bluemesa\Bundle\FliesBundle\Entity\CrossVial;

/**
 * CrossVialManager is a class used to manage common operations on cross vials
 *
 * @DI\Service("bluemesa.doctrine.crossvial_manager")
 * @DI\Tag("bluemesa_core.object_manager")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrossVialManager extends VialManager
{
    /**
     * Interface that classes managed by this ObjectManager must implement
     */
    const MANAGED_INTERFACE = 'Bluemesa\Bundle\FliesBundle\Entity\CrossVialInterface';
    
    /**
     * Mark cross(es) as sterile and trash it (them)
     *
     * @param  \Bluemesa\Bundle\FliesBundle\Entity\CrossVial | \Doctrine\Common\Collections\Collection  $vials
     * @throws \ErrorException
     */
    public function markSterile($vials)
    {
        if (($vial = $vials) instanceof CrossVial) {
            $vial->setSterile(true);
            $this->persist($vial);
        } elseif ($vials instanceof Collection) {
            foreach ($vials as $vial) {
                $this->markSterile($vial);
            }
        } elseif (null === $vials) {
            throw new \ErrorException('Argument 1 must not be null');
        } else {
            throw new \ErrorException('Argument 1 must be an object of class
                Bluemesa\Bundle\FliesBundle\Entity\CrossVial or Doctrine\Common\Collections\Collection');
        }
    }

    /**
     * Mark cross(es) as successful
     *
     * @param  \Bluemesa\Bundle\FliesBundle\Entity\CrossVial | \Doctrine\Common\Collections\Collection  $vials
     * @throws \ErrorException
     */
    public function markSuccessful($vials)
    {
        if (($vial = $vials) instanceof CrossVial) {
            $vial->setSuccessful(true);
            $this->persist($vial);
        } elseif ($vials instanceof Collection) {
            foreach ($vials as $vial) {
                $this->markSuccessful($vial);
            }
        } elseif (null === $vials) {
            throw new \ErrorException('Argument 1 must not be null');
        } else {
            throw new \ErrorException('Argument 1 must be an object of class
                Bluemesa\Bundle\FliesBundle\Entity\CrossVial or Doctrine\Common\Collections\Collection');
        }
    }

    /**
     * Mark cross(es) as failed
     *
     * @param  \Bluemesa\Bundle\FliesBundle\Entity\CrossVial|\Doctrine\Common\Collections\Collection $vials
     * @throws \ErrorException
     */
    public function markFailed($vials)
    {
        if (($vial = $vials) instanceof CrossVial) {
            $vial->setSuccessful(false);
            $this->persist($vial);
        } elseif ($vials instanceof Collection) {
            foreach ($vials as $vial) {
                $this->markFailed($vial);
            }
        } elseif (null === $vials) {
            throw new \ErrorException('Argument 1 must not be null');
        } else {
            throw new \ErrorException('Argument 1 must be an object of class
                Bluemesa\Bundle\FliesBundle\Entity\CrossVial or Doctrine\Common\Collections\Collection');
        }
    }
}
