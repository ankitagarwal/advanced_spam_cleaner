<?php

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
require_once($CFG->libdir.'/tablelib.php');
require_once('advanced_form.php');
require_once('lib.php');

// List of known spammy keywords, please add more here
$autokeywords = array(
                    "<img",
                    "fuck",
                    "casino",
                    "porn",
                    "xxx",
                    "cialis",
                    "viagra",
                    "poker",
                    "warcraft"
                );


$del = optional_param('del', '', PARAM_RAW);
$delall = optional_param('delall', '', PARAM_RAW);
$ignore = optional_param('ignore', '', PARAM_RAW);
$id = optional_param('id', '', PARAM_INT);

require_login();
admin_externalpage_setup('tooladvancedspamcleaner');

// Delete one user
if (!empty($del) && confirm_sesskey() && ($id != $USER->id)) {
    if (isset($SESSION->users_result[$id])) {
        $user = $SESSION->users_result[$id];
        if (delete_user($user)) {
            unset($SESSION->users_result[$id]);
            echo json_encode(true);
        } else {
            echo json_encode(false);
        }
    } else {
        echo json_encode(false);
    }
    exit;
}

// Delete lots of users
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

if (!empty($ignore)) {
    unset($SESSION->users_result[$id]);
    echo json_encode(true);
    exit;
}



$PAGE->requires->js_init_call('M.tool_spamcleaner.init', array(me()), true);
$strings = Array('spaminvalidresult','spamdeleteallconfirm','spamcannotdelete','spamdeleteconfirm');
$PAGE->requires->strings_for_js($strings, 'tool_spamcleaner');

echo $OUTPUT->header();

// Print headers and things
echo $OUTPUT->box(get_string('spamcleanerintro', 'tool_spamcleaner'));

$mform = new tool_advanced_spam_cleaner();
$mform->display();
if( $data = $mform->get_data()) {
    print_r($data);
    echo '<div id="result" class="mdl-align">';
    $keywords = explode(',', $data->keyword);
    if ($data->method == 'usekeywords' && empty($data->keyword)) {
        echo "dd";
        print_error(get_string('missingkeywords', 'tool_advancedspamcleaner'));
    }
    // Find spam using keywords
    if($data->method == 'usekeywords' || $data->method == 'spamauto') {
        if (empty($keywords)) {
            $keywords = $autokeywords;
        }
        search_spammers($keywords);
    // use the specified sub-plugin
    } else {
        $plugin = $data->method;
        $pluginclassname = "$plugin" . "_advanced_spam_cleaner";
        $plugin = new $pluginclassname();
        $params = array('userid' => $USER->id);

        if(isset($data->searchusers)) {
            $sql  = "SELECT * FROM {user} WHERE deleted = 0 AND id <> :userid";  // Exclude oneself
            $users = $DB->get_recordset_sql($sql, $params);
            foreach ($users as $user) {
                // Data should be consistent for the sub-plugins
                $data = new stdClass();
                $data->email = $user->email;
                $data->ip = $user->lastip;
                $data->text = $user->description;
                $data->type = 'userdesc';
                $is_spam = $plugin->detect_spam($data);
                if ($is_spam) {
                    $spamusers[$user->id]['user'] = $user;
                    if(empty($spamusers[$user->id]['spamcount'])) {
                        $spamusers[$user->id]['spamcount'] = 1;
                    } else {
                        $spamusers[$user->id]['spamcount']++;
                    }
                    $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('userdesc' => $user->description);
                }
            }
            display_advanced_spam_cleaner::print_table($spamusers);
        }

    }
    echo '</div>';
}

echo $OUTPUT->footer();
