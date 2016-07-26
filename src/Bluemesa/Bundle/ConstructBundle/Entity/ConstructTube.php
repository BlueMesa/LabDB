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

namespace Bluemesa\Bundle\ConstructBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

use Bluemesa\Bundle\StorageBundle\Entity\RackContent;
use Bluemesa\Bundle\StorageBundle\Entity\TermocontrolledInterface;

/**
 * Tube class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\ConstructBundle\Repository\TubeRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ConstructTube extends RackContent implements TermocontrolledInterface
{
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose
     */
    protected $type;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(message = "Date must be specified")
     * @Serializer\Expose
     */
    protected $date;
    
    /**
     * @ORM\OneToOne(targetEntity="ConstructBoxPosition", inversedBy="content")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="ConstructBoxPosition")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $prevPosition;
    
    /**
     * @ORM\ManyToOne(targetEntity="Construct", inversedBy="tubes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotBlank(message = "Construct must be specified")
     * @Serializer\Expose
     */
    protected $construct;

    /**
     * Construct new tube
     * 
     */
    public function __construct()
    {
        $this->type = '1.5mL Eppendorf';
        $this->date = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return sprintf("%06d",$this->getId());
    }

    /**
     * Get antibody
     * 
     * @return Construct
     */
    public function getConstruct() {
        return $this->construct;
    }

    /**
     * Set antibody
     * 
     * @param Construct $construct
     */
    public function setConstruct(Construct $construct = null) {
        $this->construct = $construct;
    }
        
    /**
     * {@inheritdoc}
     */
    public function getTemperature()
    {
        $box = $this->getRack();
        
        return ($box instanceof TermocontrolledInterface) ? $box->getTemperature() : 21;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getPositionProperty() {
        return 'position';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPreviousPositionProperty() {
        return 'prevPosition';
    }

}
