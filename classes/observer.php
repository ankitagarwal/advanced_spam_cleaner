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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/$CFG->admin/tool/advancedspamcleaner/lib.php");

class observer {
    use eventlist;
    public static function process_event(\core\event\base $event) {
        global $CFG, $DB;

        if ($event->crud != 'c') {
            // Monitor only create events. We can optionally also monitor update events but ignore that for the time being.
            return;
        }

        if (!get_config('advancedspamcleaner', 'realtime')) {
            return; // Scanning not enabled.
        }

        if (!$plugin = get_config('advancedspamcleaner', 'realtimeplugin')) {
            return; // Plugin not selected..
        }

        $file = "$CFG->dirroot/$CFG->admin/tool/advancedspamcleaner/plugins/$plugin/api.php";
        if (!file_exists($file)) {
            return;
        }

        include_once($file);
        $pluginclassname = "$plugin" . "_advanced_spam_cleaner";
        $plugin = new $pluginclassname($plugin);
        $eventlist = self::get_events_list();

        $class = get_class($event);
        if (!isset($eventlist[$class])) {
            // We are not interested in this event.
            return;
        }

        $eventdata = $event->get_data();
        $eventdata['other'] = serialize($eventdata['other']);
        if (CLI_SCRIPT) {
            $eventdata['origin'] = 'cli';
            $eventdata['ip'] = null;
        } else {
            $eventdata['origin'] = 'web';
            $eventdata['ip'] = getremoteaddr();
        }
        $eventdata['realuserid'] = \core\session\manager::is_loggedinas() ? $_SESSION['USER']->realuser : null;

        $data = new \stdClass();
        $data->email = ''; // Expensive to get user email.
        $data->ip = $eventdata['ip'];
        $data->text = '';
        $data->type = 'event';

        $fields = isset($eventlist[$class]['fields']) ? (array) $eventlist[$class]['fields'] : array();
        foreach ($fields as $field) {
            // Detect spam and store in db if potential threat.
            $data->text = $eventdata[$field];
            try {
                if ($isspam = $plugin->detect_spam($data)) {
                    $DB->insert_record('tool_advancedspamcleaner_rts', $eventdata);
                    return; // No need of any more tests.
                }
            } catch (\Exception $e) {
                if ($CFG->debug == DEBUG_DEVELOPER) {
                    throw $e;
                } else {
                    debugging('Something went wrong when processing snapshots for ' . $class, DEBUG_DEVELOPER);
                }
            }
        }

        $snaps = isset($eventlist[$class]['snapshots']) ? (array) $eventlist[$class]['snapshots'] : array();
        foreach ($snaps as $snap) {
            $id = $snap[1]; // Id to identify the snapshot.
            $table = $snap[0]; // Table to fetch snapshot from.
            try {
                $snapshot = $event->get_record_snapshot($table, $eventdata[$id]);
                $fields = isset($snap[2]) ? (array) $snap[2] : array();
                foreach ($fields as $field) {
                    // Detect spam and store in db if potential threat.
                    $content = $snapshot->$field;
                    $data->text = $content;
                    if ($isspam = $plugin->detect_spam($data)) {
                        $DB->insert_record('tool_advancedspamcleaner_rts', $eventdata);
                        return; // No need of any more tests.
                    }
                }
            } catch (\Exception $e) {
                if ($CFG->debug == DEBUG_DEVELOPER) {
                    throw $e;
                } else {
                    debugging('Something went wrong when processing snapshots for ' . $class, DEBUG_DEVELOPER);
                }
            }

        }
    }
}

