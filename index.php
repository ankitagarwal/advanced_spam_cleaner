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

$spamcleaner = new advanced_spam_cleaner();
$pluginlist = $spamcleaner->plugin_list(get_system_context());

$mform = new tool_advanced_spam_cleaner(null, array ('pluginlist' => $pluginlist));
$mform->display();

if( $formdata = $mform->get_data()) {
    echo '<div id="result" class="mdl-align">';
    $keywords = explode(',', $formdata->keyword);
    if ($formdata->method == 'usekeywords' && empty($formdata->keyword)) {
        print_error(get_string('missingkeywords', 'tool_advancedspamcleaner'));
    }

    // Set limits
    if(!empty($formdata->uselimits)) {
        if (is_number($formdata->apilimit)) {
            $apilimit = $formdata->apilimit;
        } else {
            $apilimit = 0;
        }

        if (is_number($formdata->hitlimit)) {
            $hitlimit = $formdata->hitlimit;
        } else {
            $hitlimit = 0;
        }
    } else {
        $apilimit = 0;
        $hitlimit = 0;
    }
    $apicount = 0;
    $hitcount = 0;
    $limitflag = false;

    // Date limits
    if(!empty($formdata->usedatestartlimit)) {
        if (is_number($formdata->startdate)) {
            $starttime = $formdata->startdate;
        } else {
            $starttime = 0;
        }

        if (is_number($formdata->enddate)) {
            $endtime = $formdata->enddate;
        } else {
            $endtime = time();
        }
    } else {
        $starttime = 0;
        $endtime = time();
    }

    // Find spam using keywords
    if($formdata->method == 'usekeywords' || $formdata->method == 'spamauto') {
        if (empty($keywords)) {
            $keywords = $autokeywords;
        }
        $spamcleaner->search_spammers($formdata, $keywords, $starttime, $endtime, false );
    // use the specified sub-plugin
    } else {
        $plugin = $formdata->method;
        if (in_array($plugin, $pluginlist)) {
            print_error("Invalid sub plugin");
        }
        $pluginclassname = "$plugin" . "_advanced_spam_cleaner";
        $plugin = new $pluginclassname($plugin);
        $params = array('userid' => $USER->id);
        $spamusers = array();

        if(isset($formdata->searchusers)) {
            $sql  = "SELECT * FROM {user} WHERE deleted = 0 AND id <> :userid AND description != ''";  // Exclude oneself
            $users = $DB->get_recordset_sql($sql, $params);
            foreach ($users as $user) {
                // Limit checks
                if(($apilimit != 0 && $apilimit <= $apicount) || ($hitlimit !=0 && $hitlimit <= $hitcount)) {
                    $limitflag = true;
                    break;
                }
                $apicount++;

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
                    $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('userdesc' , $data->text);
                    $hitcount++;
                }
            }
        }
        if(isset($formdata->searchcomments)) {
            $sql  = "SELECT u.*, c.content FROM {user} AS u, {comments} AS c WHERE u.deleted = 0 AND u.id=c.userid AND u.id <> :userid";
            $users = $DB->get_recordset_sql($sql, $params);
            foreach ($users as $user) {
                // Limit checks
                if(($apilimit != 0 && $apilimit <= $apicount) || ($hitlimit !=0 && $hitlimit <= $hitcount)) {
                    $limitflag = true;
                    break;
                }
                $apicount++;

                // Data should be consistent for the sub-plugins
                $data = new stdClass();
                $data->email = $user->email;
                $data->ip = $user->lastip;
                $data->text = $user->comments;
                $data->type = 'comment';
                $is_spam = $plugin->detect_spam($data);
                if ($is_spam) {
                    $spamusers[$user->id]['user'] = $user;
                    if(empty($spamusers[$user->id]['spamcount'])) {
                        $spamusers[$user->id]['spamcount'] = 1;
                    } else {
                        $spamusers[$user->id]['spamcount']++;
                    }
                    $hitcount++;
                    $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('comment' , $data->text);
                }
            }
        }
        if(isset($formdata->searchmsgs)) {
            $sql  = "SELECT u.*, m.fullmessage FROM {user} AS u, {message} AS m WHERE u.deleted = 0 AND u.id=m.useridfrom AND u.id <> :userid";
            $users = $DB->get_recordset_sql($sql, $params);
            foreach ($users as $user) {
                // Limit checks
                if(($apilimit != 0 && $apilimit <= $apicount) || ($hitlimit !=0 && $hitlimit <= $hitcount)) {
                    $limitflag = true;
                    break;
                }
                $apicount++;

                // Data should be consistent for the sub-plugins
                $data = new stdClass();
                $data->email = $user->email;
                $data->ip = $user->lastip;
                $data->text = $user->fullmessage;
                $data->type = 'message';
                $is_spam = $plugin->detect_spam($data);
                if ($is_spam) {
                    $spamusers[$user->id]['user'] = $user;
                    if(empty($spamusers[$user->id]['spamcount'])) {
                        $spamusers[$user->id]['spamcount'] = 1;
                    } else {
                        $spamusers[$user->id]['spamcount']++;
                    }
                    $hitcount++;
                    $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('message' , $data->text);
                }
            }
        }
        if(isset($formdata->searchforums)) {
            $sql = "SELECT u.*, fp.message FROM {user} AS u, {forum_posts} AS fp WHERE u.deleted = 0 AND u.id=fp.userid AND u.id <> :userid";
            $users = $DB->get_recordset_sql($sql, $params);
            foreach ($users as $user) {
                // Limit checks
                if(($apilimit != 0 && $apilimit <= $apicount) || ($hitlimit !=0 && $hitlimit <= $hitcount)) {
                    $limitflag = true;
                    break;
                }
                $apicount++;

                // Data should be consistent for the sub-plugins
                $data = new stdClass();
                $data->email = $user->email;
                $data->ip = $user->lastip;
                $data->text = $user->message;
                $data->type = 'forummessage';
                $is_spam = $plugin->detect_spam($data);
                if ($is_spam) {
                    $spamusers[$user->id]['user'] = $user;
                    if(empty($spamusers[$user->id]['spamcount'])) {
                        $spamusers[$user->id]['spamcount'] = 1;
                    } else {
                        $spamusers[$user->id]['spamcount']++;
                    }
                    $hitcount++;
                    $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('forummessage' , $data->text);
                }
            }
        }
        if(isset($formdata->searchblogs)) {
            $sql = "SELECT u.*, p.summary FROM {user} AS u, {post} AS p WHERE u.deleted = 0 AND u.id=p.userid AND u.id <> :userid";
            $users = $DB->get_recordset_sql($sql, $params);
            foreach ($users as $user) {
                // Limit checks
                if(($apilimit != 0 && $apilimit <= $apicount) || ($hitlimit !=0 && $hitlimit <= $hitcount)) {
                    $limitflag = true;
                    break;
                }
                $apicount++;

                // Data should be consistent for the sub-plugins
                $data = new stdClass();
                $data->email = $user->email;
                $data->ip = $user->lastip;
                $data->text = $user->summary;
                $data->type = 'blogpost';
                $is_spam = $plugin->detect_spam($data);
                if ($is_spam) {
                    $spamusers[$user->id]['user'] = $user;
                    if(empty($spamusers[$user->id]['spamcount'])) {
                        $spamusers[$user->id]['spamcount'] = 1;
                    } else {
                        $spamusers[$user->id]['spamcount']++;
                    }
                    $hitcount++;
                    $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('blogpost' , $data->text);
                }
            }
        }
        echo $OUTPUT->box(get_string('methodused', 'tool_advancedspamcleaner', $plugin->pluginname));
        $spamcleaner->print_table($spamusers, '', true, $limitflag);
    }
    echo '</div>';
}

echo $OUTPUT->footer();
