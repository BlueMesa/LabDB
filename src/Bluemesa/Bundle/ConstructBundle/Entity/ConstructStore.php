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

namespace Bluemesa\Bundle\ConstructBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

use Bluemesa\Bundle\StorageBundle\Entity\StorageUnit;
use Bluemesa\Bundle\StorageBundle\Entity\TermocontrolledInterface;


/**
 * Store class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\ConstructBundle\Repository\StoreRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ConstructStore extends StorageUnit implements TermocontrolledInterface
{
    /**
     * @ORM\OneToMany(targetEntity="ConstructBox", mappedBy="store", fetch="EXTRA_LAZY")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $boxes;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Expose
     * @Assert\NotBlank(message = "Temperature must be specified")
     * @Assert\Range(
     *      min = -80,
     *      max = 8,
     *      minMessage = "Temperature cannot be lower than -80℃",
     *      maxMessage = "Temperature cannot be higher than 8℃"
     * )
     *
     * @var float
     */
    private $temperature;

    /**
     * {@inheritdoc}
     */ 
    public function __construct($temperature = 4)
    {
        parent::__construct();
        $this->temperature = $temperature;
    }
    
    /**
     * Get temperature
     *
     * @return float
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
    }

    protected function getContentsProperty() {
        return 'boxes';
    }
}
