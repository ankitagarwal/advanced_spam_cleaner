<?php

/* Base sub-plugin class
 * All sub -plugins must extend this class
 * The name of the extend class should be $pluginname_advanced_spam_cleaner
 */
class base_advanced_spam_cleaner {

    /* Detect if the supplied data is probable spam or not
     * @param stdClass $data data to be examined
     *
     * @return bool true if $data is probable spam else false
     */
    function detect_spam ($data) {
        // Implement wrapper for your sub-plugins api in here
        return true;
    }
}




class display_advanced_spam_cleaner {

    static function print_table($users_rs = null, $keywords = null, $resetsession = false) {
        global $CFG, $SESSION, $OUTPUT, $PAGE;
        /*if ($resetsession) {
            // reset session
            $SESSION->users_result = array();
            $SESSION->users_result = $users_rs;
        } else {
            if (is_array($SESSION->users_result)) {
                $users_rs = $SESSION->users_result;
            } else {
                $users_rs = array();
            }
        }
        $count = 0;*/

        // Define table columns
        $columns = array();
        $headers = array();
        $columns[]= 'checkbox';
        $headers[]= null;
        $columns[]= 'picture';
        $headers[]= '';
        $columns[]= 'fullname';
        $headers[]= get_string('name');


        $columns[]= 'spamcount';
        $headers[]= get_string('spamcount', 'tool_advancedspamcleaner');
        $columns[]= 'spamtext';
        $headers[]= get_string('spamtext', 'tool_advancedspamcleaner');
        $columns[]= 'spamtype';
        $headers[]= get_string('spamtype', 'tool_advancedspamcleaner');

        $columns[]= 'deleteuser';
        $headers[]= get_string('deleteuser', 'admin');
        $columns[]= 'ignoreuser';
        $headers[]= get_string('ignore', 'admin');
        //$columns[]= 'spamtype';;
        //$headers[]= get_string('spamtype', 'tool_advancedspamcleaner');


        $table = new flexible_table('advanced-spam-cleaner');

        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->define_baseurl($PAGE->url);

        $table->sortable(false);
        $table->collapsible(true);

        // This is done to prevent redundant data, when a user has multiple attempts
        $table->column_suppress('picture');
        $table->column_suppress('fullname');

        $table->column_class('picture', 'picture');

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->setup();
        foreach ($users_rs as $userid => $userdata) {
            $user = (object)$userdata['user'];

            foreach($userdata['spamtext'] as $spamcount => $spamdata) {
                $row = array();
                $row[] = '<input type="checkbox" name="userid[]" value="'. $userid .'" />';
                $row[] = $OUTPUT->user_picture($user);
                $row[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$userid.'">'.fullname($user).'</a>';
                $row[] = $userdata['spamcount'];
                $row[] = array_pop($spamdata);
                $row[] = array_pop($spamdata);
                $row[] .= '<button onclick="M.tool_spamcleaner.del_user(this,'.$userid.')">'.get_string('deleteuser', 'admin').'</button><br />';
                $row[] .= '<button onclick="M.tool_spamcleaner.ignore_user(this,'.$userid.')">'.get_string('ignore', 'admin').'</button>';
                $table->add_data($row);
            }
        }
        $table->finish_output();
    }
}



function search_spammers($keywords) {

    global $CFG, $USER, $DB, $OUTPUT;

    if (!is_array($keywords)) {
        $keywords = array($keywords);    // Make it into an array
    }

    $params = array('userid'=>$USER->id);

    $keywordfull = array();
    $i = 0;
    foreach ($keywords as $keyword) {
        $keywordfull[] = $DB->sql_like('description', ':descpat'.$i, false);
        $params['descpat'.$i] = "%$keyword%";
        $keywordfull2[] = $DB->sql_like('p.summary', ':sumpat'.$i, false);
        $params['sumpat'.$i] = "%$keyword%";
        $keywordfull3[] = $DB->sql_like('p.subject', ':subpat'.$i, false);
        $params['subpat'.$i] = "%$keyword%";
        $keywordfull4[] = $DB->sql_like('c.content', ':contpat'.$i, false);
        $params['contpat'.$i] = "%$keyword%";
        $keywordfull5[] = $DB->sql_like('m.fullmessage', ':msgpat'.$i, false);
        $params['msgpat'.$i] = "%$keyword%";
        $keywordfull6[] = $DB->sql_like('fp.message', ':forumpostpat'.$i, false);
        $params['forumpostpat'.$i] = "%$keyword%";
        $keywordfull7[] = $DB->sql_like('fp.subject', ':forumpostsubpat'.$i, false);
        $params['forumpostsubpat'.$i] = "%$keyword%";
        $i++;
    }
    $conditions = '( '.implode(' OR ', $keywordfull).' )';
    $conditions2 = '( '.implode(' OR ', $keywordfull2).' )';
    $conditions3 = '( '.implode(' OR ', $keywordfull3).' )';
    $conditions4 = '( '.implode(' OR ', $keywordfull4).' )';
    $conditions5 = '( '.implode(' OR ', $keywordfull5).' )';
    $conditions6 = '( '.implode(' OR ', $keywordfull6).' )';
    $conditions7 = '( '.implode(' OR ', $keywordfull7).' )';

    $sql  = "SELECT * FROM {user} WHERE deleted = 0 AND id <> :userid AND $conditions";  // Exclude oneself
    $sql2 = "SELECT u.*, p.summary FROM {user} AS u, {post} AS p WHERE $conditions2 AND u.deleted = 0 AND u.id=p.userid AND u.id <> :userid";
    $sql3 = "SELECT u.*, p.subject as postsubject FROM {user} AS u, {post} AS p WHERE $conditions3 AND u.deleted = 0 AND u.id=p.userid AND u.id <> :userid";
    $sql4 = "SELECT u.*, c.content FROM {user} AS u, {comments} AS c WHERE $conditions4 AND u.deleted = 0 AND u.id=c.userid AND u.id <> :userid";
    $sql5 = "SELECT u.*, m.fullmessage FROM {user} AS u, {message} AS m WHERE $conditions5 AND u.deleted = 0 AND u.id=m.useridfrom AND u.id <> :userid";
    $sql6 = "SELECT u.*, fp.message FROM {user} AS u, {forum_posts} AS fp WHERE $conditions6 AND u.deleted = 0 AND u.id=fp.userid AND u.id <> :userid";
    $sql7 = "SELECT u.*, fp.subject FROM {user} AS u, {forum_posts} AS fp WHERE $conditions7 AND u.deleted = 0 AND u.id=fp.userid AND u.id <> :userid";

    $spamusers_desc = $DB->get_recordset_sql($sql, $params);
    $spamusers_blog = $DB->get_recordset_sql($sql2, $params);
    $spamusers_blogsub = $DB->get_recordset_sql($sql3, $params);
    $spamusers_comment = $DB->get_recordset_sql($sql4, $params);
    $spamusers_message = $DB->get_recordset_sql($sql5, $params);
    $spamusers_forumpost = $DB->get_recordset_sql($sql6, $params);
    $spamusers_forumpostsub = $DB->get_recordset_sql($sql7, $params);

    $keywordlist = implode(', ', $keywords);
    echo $OUTPUT->box(get_string('spamresult', 'tool_spamcleaner').s($keywordlist)).' ...';

    print_user_list(array($spamusers_desc,
                          $spamusers_blog,
                          $spamusers_blogsub,
                          $spamusers_comment,
                          $spamusers_message,
                          $spamusers_forumpost,
                          $spamusers_forumpostsub
                         ),
                         $keywords);
}



function print_user_list($users_rs, $keywords) {
    global $CFG, $SESSION;

    // reset session everytime this function is called
    $SESSION->users_result = array();
    $count = 0;

    foreach ($users_rs as $rs){
        foreach ($rs as $user) {
            if (!$count) {
                echo '<table border="1" width="100%" id="data-grid"><tr><th>&nbsp;</th><th>'.get_string('user','admin').'</th><th>'.get_string('spamdesc', 'tool_spamcleaner').'</th><th>'.get_string('spamoperation', 'tool_spamcleaner').'</th></tr>';
            }
            $count++;
            filter_user($user, $keywords, $count);
        }
    }

    if (!$count) {
        echo get_string('spamcannotfinduser', 'tool_spamcleaner');

    } else {
        echo '</table>';
        echo '<div class="mld-align">
              <button id="removeall_btn">'.get_string('spamdeleteall', 'tool_spamcleaner').'</button>
              </div>';
    }
}
function filter_user($user, $keywords, $count) {
    global $CFG;
    $image_search = false;
    if (in_array('<img', $keywords)) {
        $image_search = true;
    }
    if (isset($user->summary)) {
        $user->description = '<h3>'.get_string('spamfromblog', 'tool_spamcleaner').'</h3>'.$user->summary;
        unset($user->summary);
    } else if (isset($user->postsubject)) {
        $user->description = '<h3>'.get_string('spamfromblog', 'tool_spamcleaner').'</h3>'.$user->postsubject;
        unset($user->postsubject);
    } else if (isset($user->content)) {
        $user->description = '<h3>'.get_string('spamfromcomments', 'tool_spamcleaner').'</h3>'.$user->content;
        unset($user->content);
    } else if (isset($user->fullmessage)) {
        $user->description = '<h3>'.get_string('spamfrommessages', 'tool_spamcleaner').'</h3>'.$user->fullmessage;
        unset($user->fullmessage);
    } else if (isset($user->message)) {
        $user->description = '<h3>'.get_string('spamfromforumpost', 'tool_spamcleaner').'</h3>'.$user->message;
        unset($user->message);
    } else if (isset($user->subject)) {
        $user->description = '<h3>'.get_string('spamfromforumpost', 'tool_spamcleaner').'</h3>'.$user->subject;
        unset($user->subject);
    }

    if (preg_match('#<img.*src=[\"\']('.$CFG->wwwroot.')#', $user->description, $matches)
        && $image_search) {
        $result = false;
        foreach ($keywords as $keyword) {
            if (preg_match('#'.$keyword.'#', $user->description)
                && ($keyword != '<img')) {
                $result = true;
            }
        }
        if ($result) {
            echo print_user_entry($user, $keywords, $count);
        } else {
            unset($user);
        }
    } else {
        echo print_user_entry($user, $keywords, $count);
    }
}


function print_user_entry($user, $keywords, $count) {

    global $SESSION, $CFG;

    $smalluserobject = new stdClass();      // All we need to delete them later
    $smalluserobject->id = $user->id;
    $smalluserobject->email = $user->email;
    $smalluserobject->auth = $user->auth;
    $smalluserobject->firstname = $user->firstname;
    $smalluserobject->lastname = $user->lastname;
    $smalluserobject->username = $user->username;

    if (empty($SESSION->users_result[$user->id])) {
        $SESSION->users_result[$user->id] = $smalluserobject;
        $html = '<tr valign="top" id="row-'.$user->id.'" class="result-row">';
        $html .= '<td width="10">'.$count.'</td>';
        $html .= '<td width="30%" align="left"><a href="'.$CFG->wwwroot."/user/view.php?course=1&amp;id=".$user->id.'" title="'.s($user->username).'">'.fullname($user).'</a>';

        $html .= "<ul>";
        $profile_set = array('city'=>true, 'country'=>true, 'email'=>true);
        foreach ($profile_set as $key=>$value) {
            if (isset($user->$key)){
                $html .= '<li>'.$user->$key.'</li>';
            }
        }
        $html .= "</ul>";
        $html .= '</td>';

        foreach ($keywords as $keyword) {
            $user->description = highlight($keyword, $user->description);
        }

        if (!isset($user->descriptionformat)) {
            $user->descriptionformat = FORMAT_MOODLE;
        }

        $html .= '<td align="left">'.format_text($user->description, $user->descriptionformat, array('overflowdiv'=>true)).'</td>';
        $html .= '<td width="100px" align="center">';
        $html .= '<button onclick="M.tool_spamcleaner.del_user(this,'.$user->id.')">'.get_string('deleteuser', 'admin').'</button><br />';
        $html .= '<button onclick="M.tool_spamcleaner.ignore_user(this,'.$user->id.')">'.get_string('ignore', 'admin').'</button>';
        $html .= '</td>';
        $html .= '</tr>';
        return $html;
    } else {
        return null;
    }


}
