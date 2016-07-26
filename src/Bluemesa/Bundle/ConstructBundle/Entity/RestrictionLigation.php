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

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * RestrictionLigation class
 *
 * @ORM\Entity(repositoryClass="Bluemesa\Bundle\ConstructBundle\Repository\CloningMethodRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class RestrictionLigation extends CloningMethod
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     *
     * @var string
     */
    protected $vector;

    /**
     * @ORM\Column(name="c_insert", type="string", length=255, nullable=true)
     * @Serializer\Expose
     *
     * @var string
     */
    protected $insert;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Expose
     *
     * @var float
     */
    protected $vectorSize;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Expose
     *
     * @var float
     */
    protected $insertSize;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose
     *
     * @var boolean
     */
    protected $insertOrientation;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     * @Serializer\Expose
     *
     * @var string
     */
    protected $vectorUpstreamSite;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     * @Serializer\Expose
     *
     * @var string
     */
    protected $vectorDownstreamSite;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     * @Serializer\Expose
     *
     * @var string
     */
    protected $insertUpstreamSite;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     * @Serializer\Expose
     *
     * @var string
     */
    protected $insertDownstreamSite;


    /**
     * @return string
     */
    public function getVector()
    {
        return $this->vector;
    }

    /**
     * @param string $vector
     */
    public function setVector($vector)
    {
        $this->vector = $vector;
    }

    /**
     * @return string
     */
    public function getInsert()
    {
        return $this->insert;
    }

    /**
     * @param string $insert
     */
    public function setInsert($insert)
    {
        $this->insert = $insert;
    }

    /**
     * @return float
     */
    public function getVectorSize()
    {
        return $this->vectorSize;
    }

    /**
     * @param float $vectorSize
     */
    public function setVectorSize($vectorSize)
    {
        $this->vectorSize = $vectorSize;
    }

    /**
     * @return float
     */
    public function getInsertSize()
    {
        return $this->insertSize;
    }

    /**
     * @param float $insertSize
     */
    public function setInsertSize($insertSize)
    {
        $this->insertSize = $insertSize;
    }

    /**
     * @return boolean
     */
    public function isInsertOrientation()
    {
        return $this->insertOrientation;
    }

    /**
     * @param boolean $insertOrientation
     */
    public function setInsertOrientation($insertOrientation)
    {
        $this->insertOrientation = $insertOrientation;
    }

    /**
     * @return string
     */
    public function getVectorUpstreamSite()
    {
        return $this->vectorUpstreamSite;
    }

    /**
     * @param string $vectorUpstreamSite
     */
    public function setVectorUpstreamSite($vectorUpstreamSite)
    {
        $this->vectorUpstreamSite = $vectorUpstreamSite;
    }

    /**
     * @return string
     */
    public function getVectorDownstreamSite()
    {
        return $this->vectorDownstreamSite;
    }

    /**
     * @param string $vectorDownstreamSite
     */
    public function setVectorDownstreamSite($vectorDownstreamSite)
    {
        $this->vectorDownstreamSite = $vectorDownstreamSite;
    }

    /**
     * @return string
     */
    public function getInsertUpstreamSite()
    {
        return $this->insertUpstreamSite;
    }

    /**
     * @param string $insertUpstreamSite
     */
    public function setInsertUpstreamSite($insertUpstreamSite)
    {
        $this->insertUpstreamSite = $insertUpstreamSite;
    }

    /**
     * @return string
     */
    public function getInsertDownstreamSite()
    {
        return $this->insertDownstreamSite;
    }

    /**
     * @param string $insertDownstreamSite
     */
    public function setInsertDownstreamSite($insertDownstreamSite)
    {
        $this->insertDownstreamSite = $insertDownstreamSite;
    }
}