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

use Bluemesa\Bundle\StorageBundle\Entity\RackPosition;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * BoxPosition class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\ConstructBundle\Repository\BoxPositionRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ConstructBoxPosition extends RackPosition
{
    /**
     * @ORM\ManyToOne(targetEntity="ConstructBox", inversedBy="positions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Serializer\Expose
     * @Assert\NotBlank(message = "Box must be specified")
     *
     * @var ConstructBox
     */
    protected $box;

    /**
     * @ORM\OneToOne(targetEntity="ConstructTube", mappedBy="position")
     * @Serializer\Expose
     *
     * @var ConstructTube
     */
    protected $content;
    
    
    protected function getContentProperty() {
        return 'content';
    }

    protected function getRackProperty() {
        return 'box';
    }
}
