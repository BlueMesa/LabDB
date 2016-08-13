<?php

/*
 * This file is part of the XXX.
 * 
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bluemesa\Bundle\CrudBundle\Event;


use Symfony\Component\Form\FormEvents;

final class CrudControllerEvents
{
    /**
     * This event fires before the index action is performed. It allows modification of the request before any other
     * operations are performed.
     *
     * @Event
     */
    const INDEX_INITIALIZE = 'bluemesa.controller.index_initialize';

    /**
     * @Event
     */
    const INDEX_FETCHED = 'bluemesa.controller.index_fetched';

    /**
     * @Event
     */
    const INDEX_COMPLETED = 'bluemesa.controller.index_view';


    /**
     * @Event
     */
    const NEW_INITIALIZE = 'bluemesa.controller.new_initialize';

    /**
     * @Event
     */
    const NEW_SUCCESS = 'bluemesa.controller.new_success';

    /**
     * @Event
     */
    const NEW_COMPLETED = 'bluemesa.controller.new_completed';


    /**
     * @Event
     */
    const SHOW_INITIALIZE = 'bluemesa.controller.show_initialize';

    /**
     * @Event
     */
    const SHOW_COMPLETED = 'bluemesa.controller.show_completed';


    /**
     * @Event
     */
    const EDIT_INITIALIZE = 'bluemesa.controller.edit_initialize';

    /**
     * @Event
     */
    const EDIT_SUCCESS = 'bluemesa.controller.edit_success';

    /**
     * @Event
     */
    const EDIT_COMPLETED = 'bluemesa.controller.edit_completed';


    /**
     * @Event
     */
    const DELETE_INITIALIZE = 'bluemesa.controller.delete_initialize';

    /**
     * @Event
     */
    const DELETE_SUCCESS = 'bluemesa.controller.delete_success';

    /**
     * @Event
     */
    const DELETE_COMPLETED = 'bluemesa.controller.delete_completed';
}
