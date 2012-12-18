<?php
/* Base sub-plugin class
 * All sub -plugins must extend this class
 * The name of the extend class should be $pluginname_advanced_spam_cleaner
 */
class base_advanced_spam_cleaner {
    public $pluginname;

    function __construct($pluginname) {
        $this->pluginname = $pluginname;
    }
    /* Detect if the supplied data is probable spam or not
     * @param stdClass $data data to be examined
     *
     * @return bool true if $data is probable spam else false
     */
    function detect_spam ($data) {
        // Implement wrapper for your sub-plugins api in here
        return false;
    }

    function canview($context) {
        // Implement your custom cap checks here
        return true;
    }
}




class advanced_spam_cleaner {


    /* Generates and returns list of available Advanced spam cleaner sub-plugins
     *
    * @param context context level to check caps against
    * @return array list of valid reports present
    */
    function plugin_list($context) {
        global $CFG;
        static $pluginlist;
        if (!empty($pluginlist)) {
            return $pluginlist;
        }
        $installed = get_list_of_plugins('', '', $CFG->dirroot.'/admin/tool/advancedspamcleaner/plugins');
        foreach ($installed as $pluginname) {
            $pluginfile = $CFG->dirroot.'/admin/tool/advancedspamcleaner/plugins/'.$pluginname.'/api.php';
            if (is_readable($pluginfile)) {
                include_once($pluginfile);
                $pluginclassname = "{$pluginname}_advanced_spam_cleaner";
                if (class_exists($pluginclassname)) {
                    $plugin = new $pluginclassname($pluginname);

                    if ($plugin->canview($context)) {
                        $pluginlist[$pluginname] =  ucfirst($pluginname);
                    }
                }
            }
        }
        return $pluginlist;
    }

    static function search_spammers($data, $keywords = null, $starttime = 0, $endtime = 0, $return = false) {

        global $CFG, $USER, $DB, $OUTPUT;


        if (!is_array($keywords)) {
            $keywords = array($keywords);    // Make it into an array
        }
        if ($endtime == 0) {
            $enttime = time();
        }

        $params = array('userid' => $USER->id, 'start' => $starttime, 'end' => $endtime);

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
        $conditions = '( '.implode(' OR ', $keywordfull).' ) AND u.timemodified > :start AND u.timemodified < :end';
        $conditions2 = '( '.implode(' OR ', $keywordfull2).' ) AND p.lastmodified > :start AND p.lastmodified < :end';
        $conditions3 = '( '.implode(' OR ', $keywordfull3).' ) AND p.lastmodified > :start AND p.lastmodified < :end';
        $conditions4 = '( '.implode(' OR ', $keywordfull4).' ) AND c.timecreated > :start AND c.timecreated < :end';
        $conditions5 = '( '.implode(' OR ', $keywordfull5).' ) AND m.timecreated > :start AND m.timecreated < :end';
        $conditions6 = '( '.implode(' OR ', $keywordfull6).' ) AND fp.modified > :start AND fp.modified < :end';
        $conditions7 = '( '.implode(' OR ', $keywordfull7).' ) AND fp.modified > :start AND fp.modified < :end';

        $sql  = "SELECT * FROM {user} AS u WHERE deleted = 0 AND id <> :userid AND $conditions";  // Exclude oneself
        $sql2 = "SELECT u.*, p.summary FROM {user} AS u, {post} AS p WHERE $conditions2 AND u.deleted = 0 AND u.id=p.userid AND u.id <> :userid";
        $sql3 = "SELECT u.*, p.subject as subject FROM {user} AS u, {post} AS p WHERE $conditions3 AND u.deleted = 0 AND u.id=p.userid AND u.id <> :userid";
        $sql4 = "SELECT u.*, c.content as comment FROM {user} AS u, {comments} AS c WHERE $conditions4 AND u.deleted = 0 AND u.id=c.userid AND u.id <> :userid";
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
            echo $OUTPUT->box(get_string('spamresult', 'tool_advancedspamcleaner').s(implode(', ', $keywords))).' ...';
            advanced_spam_cleaner::print_table($spamusers, $keywords, true);
        }
    }

    static function print_table($users_rs = null, $keywords = null, $resetsession = false, $limitflag = false) {
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
        // checkbox is not used atm
        //$columns[]= 'checkbox';
        //$headers[]= null;
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
        $table->column_suppress('spamcount');

        $table->column_class('picture', 'picture');

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->setup();
        if ($limitflag) {
            echo $OUTPUT->box(get_string('limithit', 'tool_advancedspamcleaner'));
        }
        foreach ($users_rs as $userid => $userdata) {
            $user = (object)$userdata['user'];

            foreach($userdata['spamtext'] as $spamcount => $spamdata) {
                $row = array();
               // $row[] = '<input type="checkbox" name="userid[]" value="'. $userid .'" />';
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

