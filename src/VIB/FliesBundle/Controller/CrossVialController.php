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

namespace VIB\FliesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use VIB\FliesBundle\Doctrine\CrossVialManager;
use VIB\FliesBundle\Entity\CrossVial;
use VIB\FliesBundle\Form\CrossVialType;
use VIB\FliesBundle\Form\CrossVialNewType;

/**
 * StockVialController class
 *
 * @Route("/crosses")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrossVialController extends VialController
{
    const ENTITY_CLASS = 'VIB\FliesBundle\Entity\CrossVial';
    const ENTITY_NAME = 'cross|crosses';

    /**
     * {@inheritdoc}
     */
    protected function getCreateForm()
    {
        return CrossVialNewType::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditForm()
    {
        return CrossVialType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function expandAction(Request $request, $id = null)
    {
        throw $this->createNotFoundException();
    }

    /**
     * Statistics for cross
     *
     * @Route("/stats/{id}")
     * @Template()
     *
     * @param mixed $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statsAction($id)
    {
        /** @var CrossVial $cross */
        $cross = $this->getEntity($id);
        $total = $this->getObjectManager()->getRepository(self::ENTITY_CLASS)->findSimilar($cross);
        $sterile = new ArrayCollection();
        $success = new ArrayCollection();
        $fail = new ArrayCollection();
        $ongoing =  new ArrayCollection();
        $stocks = new ArrayCollection();
        $crosses = new ArrayCollection();
        $temps = new ArrayCollection();

        if (count($total) == 0) {
            throw $this->createNotFoundException();
        }

        /** @var CrossVial $vial */
        foreach ($total as $vial) {
            $temp = (float) $vial->getTemperature();
            if (! $temps->contains($temp)) {
                $temps->add($temp);
            }
            switch ($vial->getOutcome()) {
                case 'successful':
                    $success->add($vial);
                    foreach ($vial->getStocks() as $childStock) {
                        if (! $stocks->contains($childStock)) {
                            $stocks->add($childStock);
                        }
                    }
                    foreach ($vial->getCrosses() as $childCross) {
                        if (! $crosses->contains($childCross)) {
                            $crosses->add($childCross);
                        }
                    }
                    break;
                case 'failed':
                    $fail->add($vial);
                    break;
                case 'sterile':
                    $sterile->add($vial);
                    break;
                default:
                    $ongoing->add($vial);
                    break;
            }
        }

        return array('cross' => $cross,
                     'total' => $total,
                     'sterile' => $sterile,
                     'fail' => $fail,
                     'success' => $success,
                     'ongoing' => $ongoing,
                     'stocks' => $stocks,
                     'crosses' => $crosses,
                     'temps' => $temps);
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatchAction(Request $request, $data)
    {
        $action = $data['action'];
        $vials = new ArrayCollection($data['items']);

        switch ($action) {
            case 'marksterile':
                $this->markSterile($vials);
                $response = $this->getBackBatchResponse($request);
                break;
            case 'marksuccessful':
                $this->markSuccessful($vials);
                $response = $this->getBackBatchResponse($request);
                break;
            case 'markfailed':
                $this->markFailed($vials);
                $response = $this->getBackBatchResponse($request);
                break;
            default:
                return parent::handleBatchAction($request, $data);
        }

        return $response;
    }

    /**
     * Mark crosses as sterile and trash them
     *
     * @param \Doctrine\Common\Collections\Collection $vials
     */
    public function markSterile(Collection $vials)
    {
        /** @var CrossVialManager $om */
        $om = $this->getObjectManager();
        $om->markSterile($vials);
        $om->flush();
        if (($count = count($vials)) == 1) {
            $this->addSessionFlash('success', '1 cross was marked as sterile and trashed.');
        } else {
            $this->addSessionFlash('success', $count . ' crosses were marked as sterile and trashed.');
        }
    }

    /**
     * Mark crosses as successful
     *
     * @param \Doctrine\Common\Collections\Collection $vials
     */
    public function markSuccessful(Collection $vials)
    {
        /** @var CrossVialManager $om */
        $om = $this->getObjectManager();
        $om->markSuccessful($vials);
        $om->flush();
        if (($count = count($vials)) == 1) {
            $this->addSessionFlash('success', '1 cross was marked as successful.');
        } else {
            $this->addSessionFlash('success', $count . ' crosses were marked as successful.');
        }
    }

    /**
     * Mark crosses as successful
     *
     * @param \Doctrine\Common\Collections\Collection $vials
     */
    public function markFailed(Collection $vials)
    {
        /** @var CrossVialManager $om */
        $om = $this->getObjectManager();
        $om->markFailed($vials);
        $om->flush();
        if (($count = count($vials)) == 1) {
            $this->addSessionFlash('success', '1 cross was marked as failed.');
        } else {
            $this->addSessionFlash('success', $count . ' crosses were marked as failed.');
        }
    }
}
