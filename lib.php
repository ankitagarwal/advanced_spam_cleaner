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

require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');
require_once($CFG->dirroot.'/tag/lib.php');
require_once($CFG->libdir .'/tablelib.php');
require_once($CFG->dirroot . '/comment/lib.php');

class advanced_spam_cleaner {

    protected $spamusers = array();

    /* Generates and returns list of available Advanced spam cleaner sub-plugins
     *
    * @param context context level to check caps against
    * @return array list of valid reports present
    */
    public function plugin_list($context) {
        global $CFG;
        static $pluginlist;
        if (!empty($pluginlist)) {
            return $pluginlist;
        }
        $installed = get_list_of_plugins('', '', "$CFG->dirroot/$CFG->admin/tool/advancedspamcleaner/plugins");
        foreach ($installed as $pluginname) {
            $pluginfile = "$CFG->dirroot/$CFG->admin/tool/advancedspamcleaner/plugins/$pluginname/api.php";
            if (is_readable($pluginfile)) {
                include_once($pluginfile);
                $pluginclassname = "{$pluginname}_advanced_spam_cleaner";
                if (class_exists($pluginclassname)) {
                    $plugin = new $pluginclassname($pluginname);

                    if ($plugin->canview($context)) {
                        $pluginlist[$pluginname] = ucfirst($pluginname);
                    }
                }
            }
        }
        return $pluginlist;
    }

    public function search_spammers($data, $keywords = null, $starttime = 0, $endtime = 0, $return = false) {

        global $USER, $DB, $OUTPUT;

        if (!is_array($keywords)) {
            $keywords = array($keywords);    // Make it into an array.
        }
        if ($endtime == 0) {
            $endtime = time();
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

        $sql  = "SELECT * FROM {user} u
                  WHERE deleted = 0
                    AND id <> :userid
                    AND $conditions";  // Exclude oneself.
        $sql2 = "SELECT u.*, p.id as pid, p.summary FROM {user} u, {post} p
                  WHERE $conditions2
                    AND u.deleted = 0
                    AND u.id=p.userid
                    AND u.id <> :userid";
        $sql3 = "SELECT u.*, p.id as pid, p.subject as subject FROM {user} u, {post} p
                  WHERE $conditions3
                    AND u.deleted = 0
                    AND u.id=p.userid
                    AND u.id <> :userid";
        $sql4 = "SELECT u.*, c.id as cid, c.content as comments FROM {user} u, {comments} c
                  WHERE $conditions4
                    AND u.deleted = 0
                    AND u.id=c.userid
                    AND u.id <> :userid";
        $sql5 = "SELECT u.*, m.id as mid, m.fullmessage FROM {user} u, {message} m
                  WHERE $conditions5
                    AND u.deleted = 0
                    AND u.id=m.useridfrom
                    AND u.id <> :userid";
        $sql6 = "SELECT u.*, fp.id as fid, fp.message FROM {user} u, {forum_posts} fp
                  WHERE $conditions6
                    AND u.deleted = 0
                    AND u.id=fp.userid
                    AND u.id <> :userid";
        $sql7 = "SELECT u.*, fp.id as fid, fp.subject FROM {user} u, {forum_posts} fp
                  WHERE $conditions7
                    AND u.deleted = 0
                    AND u.id=fp.userid
                    AND u.id <> :userid";

        $this->spamusers = array();

        // Search user profiles.
        if (!empty($data->searchusers)) {
            $this->keyword_spam_search($sql, $params, 'userdesc', 'description', 'id');
        }

        // Search blogs.
        if (!empty($data->searchblogs)) {
            $this->keyword_spam_search($sql2, $params, 'blogsummary', 'summary', 'pid');
            $this->keyword_spam_search($sql3, $params, 'blogpost', 'subject', 'pid');
        }

        // Search comments.
        if (!empty($data->searchcomments)) {
            $this->keyword_spam_search($sql4, $params, 'comment', 'comments', 'cid');
        }

        // Search message.
        if (!empty($data->searchmsgs)) {
            $this->keyword_spam_search($sql5, $params, 'message', 'fullmessage', 'mid');
        }

        // Search forums.
        if (!empty($data->searchforums)) {
            $this->keyword_spam_search($sql6, $params, 'forummessage', 'message', 'fid');
            $this->keyword_spam_search($sql7, $params, 'forumsubject', 'subject', 'fid');
        }
        if ($return) {
            return $this->spamusers;
        } else {
            echo $OUTPUT->box(get_string('spamresult', 'tool_advancedspamcleaner').s(implode(', ', $keywords))).' ...';
            self::print_table($this->spamusers, $keywords, true);
        }
    }

    static public function print_table($usersrs = null, $keywords = null, $resetsession = false, $limitflag = false) {
        global $CFG, $OUTPUT, $PAGE;
        // TODO: Highlight $keywords
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

        // Define table columns.
        $columns = array();
        $headers = array();
        // Checkbox is not used atm.
        //$columns[]= 'checkbox';
        //$headers[]= null;
        $columns[] = 'picture';
        $headers[] = '';
        $columns[] = 'fullname';
        $headers[] = get_string('name');

        $columns[] = 'spamcount';
        $headers[] = get_string('spamcount', 'tool_advancedspamcleaner');
        $columns[] = 'spamtext';
        $headers[] = get_string('spamtext', 'tool_advancedspamcleaner');
        $columns[] = 'spamtype';
        $headers[] = get_string('spamtype', 'tool_advancedspamcleaner');

        $columns[] = 'deleteuser';
        $headers[] = get_string('deleteuser', 'admin');
        $columns[] = 'ignoreuser';
        $headers[] = get_string('ignore', 'admin');
        $columns[] = 'nukeuser';
        $headers[] = get_string('nukeuser', 'tool_advancedspamcleaner');

        $table = new flexible_table('advanced-spam-cleaner');

        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->define_baseurl($PAGE->url);

        $table->sortable(false);
        $table->collapsible(true);

        // This is done to prevent redundant data, when a user has multiple attempts
        // We cannot supress spam count, since it may be the same for two diff users,
        // altough ideally we dont want it shown again and again.
        // TODO: Figure out a way to do this.
        $table->column_suppress('picture');
        $table->column_suppress('fullname');

        $table->column_class('picture', 'picture');

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->setup();
        if ($limitflag) {
            echo $OUTPUT->box(get_string('limithit', 'tool_advancedspamcleaner'));
        }
        $replace = self::get_replace_keywords($keywords);
        foreach ($usersrs as $userid => $userdata) {
            $user = (object)$userdata['user'];

            foreach ($userdata['spamtext'] as $spamcount => $spamdata) {
                $row = array();
               // $row[] = '<input type="checkbox" name="userid[]" value="'. $userid .'" />';
                $row[] = $OUTPUT->user_picture($user);
                $row[] = html_writer::link(new moodle_url('/user/view.php?id='.$userid), fullname($user));
                $row[] = $userdata['spamcount'];
                $row[] = self::get_spam_content($spamdata[1], $keywords, $replace);
                $row[] = self::get_spam_url($spamdata[0], $spamdata[2]);
                $row[] .= '<button onclick="M.tool_spamcleaner.del_user(this,'.$userid.')">'.get_string('deleteuser', 'admin').'</button><br />';
                $row[] .= '<button onclick="M.tool_spamcleaner.ignore_user(this,'.$userid.')">'.get_string('ignore', 'admin').'</button>';
                $row[] .= tool_advancedspamcleaner_advanced_spammerlib::nuke_user_button($userid, true);
                $table->add_data($row);
            }
        }
        $table->finish_output();
    }

    /**
     * Get url for a spam entry
     *
     * @param string  $type Type of spam
     * @param int     $id   Id corrosponding to the spam entry
     * @return string html to display
     * @since V1.3
     */
    public static function get_spam_url($type, $id) {
        global $CFG;

        // Comments do not have url.
        switch($type) {
            case 'userdesc': $url = new moodle_url($CFG->wwwroot.'/user/profile.php', array('id' => $id));
                             break;
            case 'blogsummary':
            case 'blogpost': $url = new moodle_url($CFG->wwwroot.'/blog/index.php', array('entryid' => $id));
                             break;
            case 'forumsubject':
            case 'forummessage': $url = new moodle_url($CFG->wwwroot.'/mod/forum/discuss.php', array('d' => $id));
                                 break;
            default: $url = null;
                    break;
        }

        if (!empty($url)) {
            return html_writer::link($url, get_string($type, 'tool_advancedspamcleaner'), array('target' => '_blank'));
        } else {
            return get_string($type, 'tool_advancedspamcleaner');
        }
    }

    /**
     * Get Spam content
     *
     * @param string $content spam content to display
     * @param array $keywords keywords to highlight
     * @param array $replace replace content that replaces keywords
     *
     * @return string html to display
     * @since V2.0
     */
    public static function get_spam_content($content, $keywords, $replace) {

        $html = str_ireplace($keywords, $replace, $content);

        return $html;
    }

    /**
     * Generates a replace content for the keywords passed by adding a span to highlight the spam keyword
     *
     * @param array $keywords list of keywords
     * @since V2.0
     *
     * @return array replace array
     */
    protected static function get_replace_keywords($keywords) {
        return array_map('self::add_spam_span', $keywords);
    }

    /**
     * Add a span of spam class to the given item by reference.
     *
     * @since V2.0
     * @param $item
     */
    protected static function add_spam_span(&$item) {
        $item = '<span class=spamkeyword>' . $item . '</span>';
    }

    /**
     * Structures data and triggers requested plugin's spam detection api. Populates stats, and spam users array.
     *.
     * @param $sql         string Sql to get the data to pass onto the plugin for spam checks.
     * @param $params      string params for the $sql
     * @param $type        string What is the type of data that is checked.
     * @param $text        string Which field to check for spam in the given sql.
     * @param $id          string identifier for the spam entry.
     */
    protected function keyword_spam_search($sql, $params, $type, $text, $id) {
        global $DB;
        $users = $DB->get_recordset_sql($sql, $params);
        foreach ($users as $user) {
            $this->spamusers[$user->id]['user'] = $user;
            if (empty($this->spamusers[$user->id]['spamcount'])) {
                $this->spamusers[$user->id]['spamcount'] = 1;
            } else {
                $this->spamusers[$user->id]['spamcount']++;
            }
            $this->spamusers[$user->id]['spamtext'][$this->spamusers[$user->id]['spamcount']] = array ($type , $user->$text, $user->$id);
        }
    }
}