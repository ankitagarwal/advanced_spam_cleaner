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
 * Akismet plugin for spam cleaning
 *
 * @package   advancedspamcleaner
 * @copyright 2012 Ankit Agarwal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * This is just a dummy file...Moodle doesnt support sub-plugins for tool plugins
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2013072200;
$plugin->requires = 2012080100;
$plugin->component = 'advancedspamcleaner_akismet';
$plugin->dependencies = array('tool_advancedspamcleaner' => ANY_VERSION);
