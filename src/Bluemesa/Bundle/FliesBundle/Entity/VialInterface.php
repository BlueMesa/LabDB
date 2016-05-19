<?php

/*
 * This file is part of the BluemesaAclBundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\Bundle\FliesBundle\Entity;

use Bluemesa\Bundle\AclBundle\Entity\OwnedEntityInterface;
use Bluemesa\Bundle\StorageBundle\Entity\StorageUnitContentInterface;
use Bluemesa\Bundle\StorageBundle\Entity\TermocontrolledInterface;
use Bluemesa\Bundle\FliesBundle\Label\LabelDateInterface;

/**
 * Secured entity interface
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
interface VialInterface extends
    OwnedEntityInterface,
    StorageUnitContentInterface,
    TermocontrolledInterface,
    LabelDateInterface
{
}
