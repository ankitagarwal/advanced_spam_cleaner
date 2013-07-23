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

require_once('akismet.class.php');

class akismet_advanced_spam_cleaner extends base_advanced_spam_cleaner {
    public function detect_spam ($data) {
        global $CFG;

        $apikey = get_config('advancedspamcleaner', 'akismetkey');
        if (!$apikey) {
            print_error("noakismetkey", 'tool_advancedspamcleaner', new moodle_url($CFG->wwwroot . '/admin/settings.php', array('section' => 'advancedspamcleaner')));
        }

        $akismet = new Akismet($CFG->wwwroot, $apikey);
        $akismet->setCommentAuthorEmail($data->email);
        $akismet->setCommentContent($data->text);
        $akismet->setUserIP($data->ip);

        return $akismet->isCommentSpam();
    }
}
