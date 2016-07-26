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

use Bluemesa\Bundle\StorageBundle\Entity\Rack;
use Bluemesa\Bundle\StorageBundle\Entity\StorageUnitInterface;
use Bluemesa\Bundle\StorageBundle\Entity\StorageUnitContentInterface;
use Bluemesa\Bundle\StorageBundle\Entity\TermocontrolledInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;


/**
 * ConstructBox class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\ConstructBundle\Repository\ConstructBoxRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ConstructBox extends Rack implements StorageUnitContentInterface, TermocontrolledInterface
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="ConstructBoxPosition", mappedBy="box", cascade={"persist", "remove"},
     *     orphanRemoval=true, fetch="EXTRA_LAZY"
     * )
     *
     * @var ArrayCollection
     */
    protected $positions;

    /**
     * @ORM\ManyToOne(targetEntity="ConstructStore", inversedBy="boxes")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * 
     * @var ConstructStore
     */
    protected $store;

    
    /**
     * {@inheritdoc}
     */
    public function getStorageUnit()
    {
        return $this->store;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setStorageUnit(StorageUnitInterface $unit = null)
    {
        $this->store = $unit;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemperature()
    {
        return (($unit = $this->getStorageUnit()) instanceof TermocontrolledInterface) ? 
            $unit->getTemperature() : 21.00;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLocation() {
        return (null !== ($unit = $this->getStorageUnit())) ? (string) $unit : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPositionClass() {
        return ConstructBoxPosition::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPositionsProperty() {
        return 'positions';
    }

}
