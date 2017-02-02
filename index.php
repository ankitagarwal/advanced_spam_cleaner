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
 * Advanced Spam Cleaner
 *
 * Helps an admin to clean up spam in Moodle
 *
 * @copytight Ankit Agarwal
 * @license GPL3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('advanced_form.php');
require_once('lib.php');

$del = optional_param('del', '', PARAM_RAW);
$delall = optional_param('delall', '', PARAM_RAW);
$ignore = optional_param('ignore', '', PARAM_RAW);
$id = optional_param('id', '', PARAM_INT);

require_login();
admin_externalpage_setup('tooladvancedspamcleaner');

// Delete one user // sessions are not supported atm.
if (!empty($del) && confirm_sesskey() && ($id != $USER->id)) {
    if ($user = $DB->get_record("user", array('id' => $id))) {
        if (delete_user($user)) {
            //unset($SESSION->users_result[$id]);
            echo json_encode(true);
        } else {
            echo json_encode(false);
        }
    } else {
        echo json_encode(false);
    }
    exit;
}

// Delete lots of users.
if (!empty($delall) && confirm_sesskey()) {
    if (!empty($SESSION->users_result)) {
        foreach ($SESSION->users_result as $userid => $user) {
            if ($userid != $USER->id) {
                if (delete_user($user)) {
                    unset($SESSION->users_result[$userid]);
                }
            }
        }
    }
    echo json_encode(true);
    exit;
}

// Ignore a user.
if (!empty($ignore)) {
    unset($SESSION->users_result[$id]);
    echo json_encode(true);
    exit;
}

$PAGE->requires->js_init_call('M.tool_spamcleaner.init', array(me()), true);
$strings = array('spaminvalidresult', 'spamdeleteallconfirm', 'spamcannotdelete', 'spamdeleteconfirm');
$PAGE->requires->strings_for_js($strings, 'tool_spamcleaner');

echo $OUTPUT->header();

// Print headers and things.
echo $OUTPUT->box(get_string('spamcleanerintro', 'tool_advancedspamcleaner'));

$manager = new tool_advancedspamcleaner_manager();
$pluginlist = $manager->spamcleaner->plugin_list(context_system::instance());

$links = array();
$links[] = html_writer::link("https://github.com/ankitagarwal/advanced_spam_cleaner/issues", get_string('reportissue', 'tool_advancedspamcleaner'),
    array('target' => '_blank'));
$links[] = html_writer::link("https://moodle.org/plugins/view.php?plugin=tool_advancedspamcleaner", get_string('pluginpage', 'tool_advancedspamcleaner'),
    array('target' => '_blank'));
$links = html_writer::alist($links);
echo $OUTPUT->box($links);

$mform = new tool_advanced_spam_cleaner(null, array ('pluginlist' => $pluginlist));

if ( $formdata = $mform->get_data()) {
    $mform->display();
    $manager->set_form_data($formdata);

    echo html_writer::start_div('mdl-align', array('id' => 'result'));
    $limitflag = false;
    // Search for spam.
    $manager->spam_search();
    echo html_writer::end_div();
} else {
    $mform->display();
}

echo $OUTPUT->footer();
