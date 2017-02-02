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

$settings->add(new admin_setting_configtext('advancedspamcleaner/akismetkey',
                get_string('akismetkey', 'tool_advancedspamcleaner'), get_string('akismetkey_desc', 'tool_advancedspamcleaner'),
                null, PARAM_TEXT));
