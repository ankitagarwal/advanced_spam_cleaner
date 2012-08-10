<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Link to spamcleaner.
 *
 * For now keep in Reports folder, we should move it elsewhere once we deal with contexts in general reports and navigation
 *
 * @package    tool_advancedspamcleaner
 * @copyright  2012 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once ( $CFG->dirroot . '/admin/tool/advancedspamcleaner/lib.php');

// Implementing own parser as Moodle doesnt support subplugins to an admin tool

$spamcleaner = new advanced_spam_cleaner();
$pluginlist = $spamcleaner->plugin_list(get_system_context());

$settings = new admin_settingpage('advancedspamcleaner', get_string('settingpage', 'tool_advancedspamcleaner'), 'moodle/site:config');
foreach ($pluginlist as $plugin => $pluginname) {
    $settingfile = $CFG->dirroot.'/admin/tool/advancedspamcleaner/plugins/'.$plugin.'/settings.php';
    if (is_readable($settingfile)) {
        $settings->add(new admin_setting_heading('advancedspamcleaner/settings',
                        get_string('pluginsettings', 'tool_advancedspamcleaner', $pluginname), ''));
        include_once($settingfile);
    }
}

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage('tooladvancedspamcleaner', get_string('pluginname', 'tool_advancedspamcleaner'), "$CFG->wwwroot/$CFG->admin/tool/advancedspamcleaner/index.php", 'moodle/site:config'));
    $ADMIN->add('reports', $settings);
}

