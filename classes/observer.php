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

namespace tool_advancedspamcleaner;

use core\session\exception;

defined('MOODLE_INTERNAL') || die();

class observer {
    use eventlist;
    public function process_event(\core\event\base $event) {
        global $CFG;
        if ($event->crud != 'c') {
            // Monitor only create events. We can optionally also monitor update events but ignore that for the time being.
            return;
        }
        $eventlist = $this->get_event_list();

        $class = get_class($event);

        if (!isset($eventlist[$class])) {
            // We are not interested in this event.
            return;
        }

        $fields = isset($eventlist[$class]['fields']) ? (array) $eventlist[$class]['fields'] : array();
        foreach ($fields as $field) {
            // Detect spam and store in db if potential threat.
        }

        $snaps = isset($eventlist[$class]['snapshots']) ? (array) $eventlist[$class]['snapshots'] : array();
        foreach ($snaps as $snap) {
            $id = $snap[1]; // Id to identify the snapshot.
            $table = $snap[0]; // Table to fetch snapshot from.
            try {
                $snapshot = $event->get_record_snapshot($table, $id);
                $fields = isset($snap[2]) ? (array) $snap[2] : array();
                foreach ($fields as $field) {
                    $content = $snapshot->field;
                    // Detect spam and store in db if potential threat.
                }
            } catch (exception $e) {
                if ($CFG->debug == DEBUG_DEVELOPER) {
                    throw $e;
                } else {
                    debugging('Something went wrong when processing snapshots for ' . $class, DEBUG_DEVELOPER);
                }
            }

        }
    }
}

