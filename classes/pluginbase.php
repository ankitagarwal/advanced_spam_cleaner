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
 * Base sub-plugin class
 * All sub -plugins must extend this class
 * The name of the extend class should be $pluginname_advanced_spam_cleaner
 *
 * Class tool_advancedspamcleaner_pluginbase
 */
class tool_advancedspamcleaner_pluginbase {
    public $pluginname;

    /**
     * tool_advancedspamcleaner_pluginbase constructor.
     * @param $pluginname
     */
    public function __construct($pluginname) {
        $this->pluginname = $pluginname;
    }

    /* Detect if the supplied data is probable spam or not
     * @param stdClass $data data to be examined
     *
     * @return bool true if $data is probable spam else false
     */
    public function detect_spam ($data) {
        // Implement wrapper for your sub-plugins api in here.
        return false;
    }

    /**
     * Can view this plugin or not.
     *
     * @param $context
     * @return bool
     */
    public function canview($context) {
        // Implement your custom cap checks here.
        return true;
    }
}
