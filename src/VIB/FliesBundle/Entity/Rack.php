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

namespace VIB\FliesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation as Serializer;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as UniqueEntity;

use VIB\BaseBundle\Entity\Entity;


/**
 * Rack class
 * 
 * @ORM\Entity(repositoryClass="VIB\FliesBundle\Repository\RackRepository")
 * @Serializer\ExclusionPolicy("all")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class Rack extends Entity {

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     * 
     * @var string
     */
    protected $description;
    
    /**
     * @ORM\OneToMany(targetEntity="RackPosition", mappedBy="rack", cascade={"persist", "remove"}, orphanRemoval=true)
     * 
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $positions;
    
    /**
     * @var integer
     */
    private $rows;
    
    /**
     * @var integer
     */
    private $columns;
    
    
    /**
     * Construct Rack
     *
     * @param integer $rows
     * @param integer $columns
     */    
    public function __construct($rows = null, $columns = null) {
        $this->setGeometry($rows, $columns);
    }
    
    /**
     * Return string representation of Rack
     *
     * @return string
     */
    public function __toString() {
        return sprintf("R%06d",$this->getId());
    }
    
    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Get label
     *
     * @return string
     */
    public function getLabel() {
        return $this->getDescription();
    }
    
    /**
     * Get positions
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    protected function getPositions() {
        return $this->positions;
    }
    
    /**
     * Get position
     *
     * @param string $row
     * @param integer $column
     * @return \VIB\FliesBundle\Entity\RackPosition
     * @throws \OutOfBoundsException
     */
    protected function getPosition($row, $column) {
        foreach ($this->getPositions() as $position) {
            if($position->isAt($row, $column)) {
                return $position;
            }
        }
        throw new \OutOfBoundsException();
    }
        
    /**
     * Get first empty position
     *
     * @param string $row
     * @param integer $column
     * @return \VIB\FliesBundle\Entity\RackPosition
     * @throws \OutOfBoundsException
     */
    protected function getFirstEmptyPosition($row = null, $column = null) {
        foreach ($this->getPositions() as $position) {
            if($position->isAt($row, $column) && $position->isEmpty()) {
                return $position;
            }
        }
        return null;
    }
    
    /**
     * Set position
     *
     * @param string $row
     * @param integer $column
     * @param mixed $contents
     */
    protected function setPosition($row, $column, $contents = null) {
        $this->getPosition($row, $column)->setContents($contents);
    }

    /**
     * Update counters for rows and columns
     * 
     */
    private function updateGeometry() {
        if ((null === $this->rows)||(null === $this->columns)) {
            $rows = array();
            $columns = array();
            foreach ($this->getPositions() as $position) {
                $rows[$position->getRow()] = true;
                $columns[$position->getColumn()] = true;
            }
            $this->rows = count($rows);
            $this->columns = count($columns);
        }
    }
    
    /**
     * Count rows in rack
     * 
     * @return integer
     */
    public function getRows() {
        $this->updateGeometry();
        return $this->rows;
    }
    
    /**
     * Count columns in rack
     * 
     * @return integer
     */
    public function getColumns() {
        $this->updateGeometry();
        return $this->columns;
    }
    
    /**
     * Get geometry
     * 
     * @return string
     */
    public function getGeometry() {
        $this->updateGeometry();
        return $this->rows . " ✕ " . $this->columns;
    }
    
    /**
     * Set geometry
     * 
     * @param integer $rows
     * @param integer $columns
     */
    public function setGeometry($rows, $columns) {
        $this->positions = new ArrayCollection();
        if ((null !== $rows)&&(null !== $columns)) {
            for($row = 1; $row <= $rows; $row++) {
                for($column = 1; $column <= $columns; $column++) {
                    $this->positions[] = new RackPosition($row,$column);
                }
            }
            $this->rows = $rows;
            $this->columns = $columns;
        }
    }
    
    /**
     * Get vial
     * 
     * @param string $row
     * @param integer $column
     * @return \VIB\FliesBundle\Entity\Vial
     */
    public function getVial($row, $column) {
        return $this->getPosition($row, $column)->getContents();
    }
    
    /**
     * Get vials
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVials() {
        $vials = new ArrayCollection();
        foreach($this->getPositions() as $position) {
            $vials[] = $position->getContents();
        }
        return $vials;
    }
    
    /**
     * Add vial to first empty position
     *
     * @param \VIB\FliesBundle\Entity\Vial $vial
     * @param string $row
     * @param integer $column
     */
    public function addVial(Vial $vial, $row = null, $column = null) {
        $position = $this->getFirstEmptyPosition($row, $column);
        if ($position != null) {
            $position->setContents($vial);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Remove vial
     *
     * @param \VIB\FliesBundle\Entity\Vial $vial
     */
    public function removeVial(Vial $vial) {
        foreach ($this->getPositions() as $position ){
            if ($position->getContents() == $vial) {
                $position->setContents(null);
            }
        }
    }
    
    /**
     * Replace vial at given position
     * 
     * @param string $row
     * @param integer $column
     * @param \VIB\FliesBundle\Entity\Vial $vial
     */
    public function replaceVial($row, $column, Vial $vial = null) {
        $this->setPosition($row, $column, $vial);
    }
    
    /**
     * Clear all vial
     *
     */
    public function clearVials() {
        foreach ($this->getPositions() as $position ){
            $position->setContents(null);
        }
    }
}