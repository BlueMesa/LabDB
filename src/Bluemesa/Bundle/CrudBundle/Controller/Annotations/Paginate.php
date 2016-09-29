<?php

/*
 * This file is part of the CRUD Bundle.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\Controller\Annotations;


/**
 * Paginated Annotation
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class Paginate
{
    /**
     * @var integer
     */
    public $maxResults;

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters
     */
    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $this->setMaxResults($data['value']);
        } else {
            $this->setMaxResults(20);
        }
    }

    /**
     * @return integer
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param integer $maxResults
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }
}
