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

namespace Bluemesa\Bundle\FliesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

use Bluemesa\Bundle\CoreBundle\Entity\Entity;
use Bluemesa\Bundle\CoreBundle\Entity\NamedInterface;
use Bluemesa\Bundle\CoreBundle\Entity\NamedTrait;

use Bluemesa\Bundle\AclBundle\Entity\OwnedEntityInterface;
use Bluemesa\Bundle\StorageBundle\Entity\StorageUnitInterface;
use Bluemesa\Bundle\StorageBundle\Entity\TermocontrolledInterface;

/**
 * Incubator class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\FliesBundle\Repository\IncubatorRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class Incubator extends Entity implements
    NamedInterface,
    OwnedEntityInterface,
    StorageUnitInterface,
    TermocontrolledInterface
{
    use NamedTrait;
    
    /**
     * @ORM\OneToMany(targetEntity="Rack", mappedBy="incubator", fetch="EXTRA_LAZY")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $racks;

    /**
     * @ORM\OneToMany(targetEntity="Vial", mappedBy="incubator", fetch="EXTRA_LAZY")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $vials;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Expose
     * @Assert\NotBlank(message = "Temperature must be specified")
     * @Assert\Range(
     *      min = 4,
     *      max = 42,
     *      minMessage = "Temperature cannot be lower than 4℃",
     *      maxMessage = "Temperature cannot be higher than 42℃"
     * )
     *
     * @var float
     */
    private $temperature;

    /**
     * Construct Incubator
     *
     * @param float $temperature
     */
    public function __construct($temperature = 25)
    {
        $this->name = 'New incubator';
        $this->temperature = $temperature;
        $this->racks = new ArrayCollection();
        $this->vials = new ArrayCollection();
    }

    /**
     * Get racks
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRacks()
    {
        return $this->racks;
    }

    /**
     * Get vials
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getVials()
    {
        return $this->vials;
    }

    /**
     * Get living vials
     *
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getLivingVials()
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P2M'));
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('trashed', false))
            ->andWhere(Criteria::expr()->gt('setupDate', $date));

        return $this->getVials()->matching($criteria);
    }
    
    /**
     * {@inheritdoc}
     */    
    public function getContents() {
        $contents = array_merge($this->getRacks()->toArray(), $this->getVials()->toArray());
        return new ArrayCollection($contents);
    }
    
    /**
     * {@inheritdoc}
     */ 
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Set temperature
     *
     * @param float $temperature
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;
        
        foreach ($this->getRacks() as $rack) {
            foreach ($rack->getContents() as $vial) {
                $vial->updateStorageConditions();
            }
        }
        
        foreach ($this->getLivingVials() as $vial) {
            $vial->updateStorageConditions();
        }
    }
}
