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
defined('MOODLE_INTERNAL') || die();

/**
 * Class advanced_spammerlib
 * Extends basic spammerlib class and adds our customisations
 */
class tool_advancedspamcleaner_advanced_spammerlib extends tool_advancedspamcleaner_spammerlib{
    /**
     * Print a button to nuke a user or print error string if the user cannot be suspended
     *
     * @param int  $userid userid to be nuked
     * @param bool $return return or print
     *
     * @return string
     */
    public static function nuke_user_button ($userid, $return = false) {
        global $OUTPUT;
        if (!self::is_suspendable_user($userid)) {
            if ($return) {
                return get_string('cannotdelete', 'tool_advancedspamcleaner');
            } else {
                p(get_string('cannotdelete', 'tool_advancedspamcleaner'));
                return;
            }

        }

        // Add delete button.
        $urlparams = array('userid' => $userid);
        $url = new moodle_url('/admin/tool/advancedspamcleaner/confirmdelete.php', $urlparams);
        $buttontext = get_string('deletebutton', 'tool_advancedspamcleaner');
        $button = new single_button($url, $buttontext);
        $content = $OUTPUT->render($button);
        if ($return) {
            return $content;
        } else {
            echo $content;
        }


    }
}