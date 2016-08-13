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

use Bluemesa\Bundle\AclBundle\Entity\SecureEntity;
use Bluemesa\Bundle\CoreBundle\Entity\NamedTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Antibody class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\ConstructBundle\Repository\ConstructRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class Construct extends SecureEntity
{ 
    use NamedTrait;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Serializer\Expose
     * @Assert\NotBlank(message = "Type must be specified")
     *
     * @var string
     */
    protected $type;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Expose
     *
     * @var float
     */
    protected $size;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     * @Serializer\Expose
     *
     * @var array
     */
    protected $resistances;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Expose
     * 
     * @var string
     */
    protected $notes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     *
     * @var string
     */
    protected $vendor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     * @Assert\Url()
     *
     * @var string
     */
    protected $infoURL;

    /**
     * @ORM\OneToMany(targetEntity="ConstructTube", mappedBy="construct", cascade={"persist"}, fetch="EXTRA_LAZY")
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $tubes;

    /**
     * @ORM\OneToOne(targetEntity="CloningMethod", mappedBy="construct", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var CloningMethod
     */
    protected $method;

    
    /**
     * Construct Antibody
     * 
     */
    public function __construct()
    {
        $this->tubes = new ArrayCollection();
        $this->applications = new ArrayCollection();        
        $this->type = 'plasmid';
    }
    
    /**
     * Get type
     * 
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set type
     * 
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Get size
     * 
     * @return float
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Set size
     * 
     * @param float $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * @return array
     */
    public function getResistances()
    {
        return $this->resistances;
    }

    /**
     * @param array $resistances
     */
    public function setResistances($resistances)
    {
        $this->resistances = $resistances;
    }



    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }
    
    /**
     * Set notes
     *
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * Get vendor
     *
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Set vendor
     *
     * @param string
     */
    public function setVendor($stockVendor)
    {
        $this->vendor = $stockVendor;
    }

    /**
     * Get info URL
     *
     * @return string
     */
    public function getInfoURL()
    {
        return $this->infoURL;
    }

    /**
     * Set info URL
     *
     * @param string  $infoURL
     */
    public function setInfoURL($infoURL)
    {
        $this->infoURL = $infoURL;
    }
    
    /**
     * Get tubes
     *
     * @return ArrayCollection
     */
    public function getTubes()
    {
        return $this->tubes;
    }
    
    /**
     * Add tube
     *
     * @param ConstructTube $tube
     */
    public function addTube(ConstructTube $tube)
    {
        $tubes = $this->getTubes();
        if (null !== $tube) {
            if (! $tubes->contains($tube)) {
                $tubes->add($tube);
            }
            if ($tube->getConstruct() !== $this) {
                $tube->setConstruct($this);
            }
        }
    }

    /**
     * Remove tube
     *
     * @param ConstructTube $tube
     */
    public function removeTube(ConstructTube $tube)
    {
        $this->getTubes()->removeElement($tube);
    }

    /**
     * @return CloningMethod
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param CloningMethod $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
        if ($method->getConstruct() !== $this) {
            $method->setConstruct($this);
        }
    }
}
