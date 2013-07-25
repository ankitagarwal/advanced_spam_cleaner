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

/**
 * Class test_tool_advancedspamcleaner Test calss for advanced_spam_cleaner
 */
class test_tool_advancedspamcleaner extends advanced_testcase {

    protected function setUp() {
        global $CFG;
        require_once($CFG->dirroot.'/admin/tool/advancedspamcleaner/lib.php');
    }

    public function test_plugin_list() {

        $spamcleaner = new advanced_spam_cleaner();
        $list = $spamcleaner->plugin_list(context_system::instance());

        // Update this if more plugins are added.
        $this->assertSame(array('akismet'), array_keys($list));
    }
}