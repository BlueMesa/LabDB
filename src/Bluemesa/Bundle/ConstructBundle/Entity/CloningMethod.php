<?php

/*
 * This file is part of the ConstructBundle.
 *
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\Bundle\ConstructBundle\Entity;

use Bluemesa\Bundle\CoreBundle\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * CloningMethod class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\ConstructBundle\Repository\CloningMethodRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CloningMethod extends Entity
{
    /**
     * @ORM\OneToOne(targetEntity="Construct", inversedBy="method")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Serializer\Expose
     * @Assert\NotBlank(message = "Construct must be specified")
     *
     * @var Construct
     */
    protected $construct;

    
    /**
     * Construct CloningMethod
     */ 
    public function __construct()
    {

    }

    /**
     * Get construct
     * 
     * @return Construct
     */
    public function getConstruct() {
        return $this->construct;
    }

    /**
     * Set construct
     * 
     * @param Construct $construct
     */
    public function setConstruct(Construct $construct) {
        $this->construct = $construct;
        if ($construct->getMethod() !== $this) {
            $construct->setMethod($this);
        }
    }
}
