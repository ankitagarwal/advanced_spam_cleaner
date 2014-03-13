<?php

// This file is part of Advanced Spam Cleaner tool for Moodle
//
// Advanced Spam Cleaner is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Advanced Spam Cleaner is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// For a copy of the GNU General Public License, see <http://www.gnu.org/licenses/>.

/* array('property', array('propertyname1', 'propertyname2')
         'snapshot', array(array('tablename', 'id', array('propertyname')))
 *)
 */
namespace tool_advancedspamcleaner;

defined('MOODLE_INTERNAL') || die();

trait eventlist{
    protected $eventlist = array(
        'block_comments\event\comment_created' => array('snapshot' => array('comments', 'objectid', 'content'))
    );

    /**
     * List a set of events to monitor.
     *
     * @return array
     */
    protected function get_events_list() {
        return $this->eventlist;
    }
}