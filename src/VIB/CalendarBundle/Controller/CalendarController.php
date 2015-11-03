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

namespace VIB\CalendarBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sabre\VObject\Component\VCalendar;
use Bluemesa\Bundle\CoreBundle\Controller\AbstractController;

/**
 * Controller for online ics calendars
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CalendarController extends AbstractController
{
    /**
     * Create online calendar for user
     *
     * @Route("/{username}.ics")
     *
     * @param  string                                    $username User to create the calendar for
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function calendarAction($username)
    {
        $user = $this->get('user_provider')->loadUserByUsername($username);
        $om = $this->getObjectManager('VIB\FliesBundle\Entity\Vial');
        $calendar = new VCalendar();
        $calendar->VERSION = '2.0';
        $field = 'X-WR-CALNAME';
        $calendar->$field = $user->getShortName() . '\'s flywork';

        $stockDates =  $om->getRepository('VIB\FliesBundle\Entity\StockVial')->getFlipDates($user);
        foreach ($stockDates as $stockDate) {
            $event = $calendar->createComponent('VEVENT');
            $calendar->add($event);
            $event->SUMMARY = 'Transfer stocks';
            $dtstart = $calendar->createProperty('DTSTART');
            $dtstart->setDateTime($stockDate);
            $event->DTSTART = $dtstart;
            $alarm = $calendar->createComponent('VALARM');
            $event->add($alarm);
            $alarm->TRIGGER = 'PT8H';
            $alarm->ACTION = 'DISPLAY';
        }

        $crossDates =  $om->getRepository('VIB\FliesBundle\Entity\CrossVial')->getFlipDates($user);
        foreach ($crossDates as $crossDate) {
            $crossDates[] = $crossDate;
            $event = $calendar->createComponent('VEVENT');
            $calendar->add($event);
            $event->SUMMARY = 'Check crosses';
            $calendar->createProperty('DTSTART');
            $dtstart->setDateTime($crossDate);
            $event->DTSTART = $dtstart;
            $alarm = $calendar->createComponent('VALARM');
            $event->add($alarm);
            $alarm->TRIGGER = 'PT8H';
            $alarm->ACTION = 'DISPLAY';
        }

        return new Response($calendar->serialize(),200,
                array(
                    'Content-Type' => 'text/calendar; charset=utf-8',
                    'Content-Disposition' => 'inline; filename="calendar.ics"'));
    }
}
