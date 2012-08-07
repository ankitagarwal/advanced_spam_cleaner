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


    static function search_spammers($data, $keywords = null, $return = false) {

        global $CFG, $USER, $DB, $OUTPUT;


        if (!is_array($keywords)) {
            $keywords = array($keywords);    // Make it into an array
        }

        $params = array('userid' => $USER->id);

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

        $spamusers = array();

        // Search user profiles
        if (!empty($data->searchusers)) {
            $users = $DB->get_recordset_sql($sql, $params);
            foreach( $users as $user) {
                $spamusers[$user->id]['user'] = $user;
                if(empty($spamusers[$user->id]['spamcount'])) {
                    $spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $spamusers[$user->id]['spamcount']++;
                }
                $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('userdesc' , $user->description);
            }
        }


        // Search blogs
        if (!empty($data->searchblogs)) {
            $users = $DB->get_recordset_sql($sql2, $params);
            foreach( $users as $user) {
                $spamusers[$user->id]['user'] = $user;
                if(empty($spamusers[$user->id]['spamcount'])) {
                    $spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $spamusers[$user->id]['spamcount']++;
                }
                $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('blogsummary' , $user->summary);
            }
            $users = $DB->get_recordset_sql($sql3, $params);
            foreach( $users as $user) {
                $spamusers[$user->id]['user'] = $user;
                if(empty($spamusers[$user->id]['spamcount'])) {
                    $spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $spamusers[$user->id]['spamcount']++;
                }
                $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('blogpost' , $user->subject);
            }
        }

        // Search comments
        if (!empty($data->searchcomments)) {
            $users = $DB->get_recordset_sql($sql4, $params);
            foreach( $users as $user) {
                $spamusers[$user->id]['user'] = $user;
                if(empty($spamusers[$user->id]['spamcount'])) {
                    $spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $spamusers[$user->id]['spamcount']++;
                }
                $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('comment' , $user->comment);
            }
        }

        // Search message
        if (!empty($data->searchmsgs)) {
            $users = $DB->get_recordset_sql($sql5, $params);
            foreach( $users as $user) {
                $spamusers[$user->id]['user'] = $user;
                if(empty($spamusers[$user->id]['spamcount'])) {
                    $spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $spamusers[$user->id]['spamcount']++;
                }
                $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('message' , $user->fullmessage);
            }
        }

        // Search forums
        if (!empty($data->searchforums)) {
            $users = $DB->get_recordset_sql($sql6, $params);
            foreach( $users as $user) {
                $spamusers[$user->id]['user'] = $user;
                if(empty($spamusers[$user->id]['spamcount'])) {
                    $spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $spamusers[$user->id]['spamcount']++;
                }
                $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('forummessage' , $user->message);
            }
            $users = $DB->get_recordset_sql($sql3, $params);
            foreach( $users as $user) {
                $spamusers[$user->id]['user'] = $user;
                if(empty($spamusers[$user->id]['spamcount'])) {
                    $spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $spamusers[$user->id]['spamcount']++;
                }
                $spamusers[$user->id]['spamtext'][$spamusers[$user->id]['spamcount']] = array ('forumsubject' , $user->subject);
            }
        }
        if ($return) {
            return $spamusers;
        } else {
            echo $OUTPUT->box(get_string('spamresult', 'tool_spamcleaner').s(implode(', ', $keywords))).' ...';
            display_advanced_spam_cleaner::print_table($spamusers, $keywords, true);
        }
    }

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

