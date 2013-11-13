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

/**
 * Manager class for advanced spam cleaner.
 *
 * Class tool_advancedspamcleaner_manager
 */
class tool_advancedspamcleaner_manager {

    /* bool Use api limits? */
    public $uselimits = false;

    /* int $apilimit Max api calls allowed */
    public $apilimit = 0;

    /* int $hitlimit Max positive hits allowed */
    public $hitlimit = 0;

    /* int Api calls made so far */
    public $apicount = 0;

    /* int Positive hits registered so far */
    public $hitcount = 0;

    /* Start boundry to look for spam content */
    public $starttime = 0;

    /* End boundry to look for spam content */
    public $endtime = 0;

    // List of known spammy keywords, please add more here.
    public $autokeywords = array(
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
    /** @var array keywords to use during search */
    public $keywords = array();

    /** @var string search method to use */
    public $method = 'spamauto';

    /** @var advanced_spam_cleaner reference */
    public $spamcleaner;

    /** @var array form data*/
    public $formdata;

    /** @var  array list of installed plugins */
    public $pluginlist;

    /** @var array stats */
    public $stats = array('time' => 0, 'users' => 0, 'comments' => 0, 'msgs' => 0, 'forums' => 0, 'blogs' => 0);

    /** @var bool if any limit is reached or not */
    public $limitflag = false;

    public $spamusers = array();

    /**
     * Constructor
     *
     * @param null|stdClass $formdata
     */
    public function __construct($formdata = null) {
        $this->endtime = time();
        $this->spamcleaner = new advanced_spam_cleaner();
        $this->pluginlist = $this->spamcleaner->plugin_list(context_system::instance());
        if ($formdata) {
            $this->set_form_data($formdata);
        }
    }

    /**
     * Populates manager's fields based on the provided form data
     *
     * @param $formdata
     */
    public function set_form_data($formdata) {
        $this->formdata = $formdata;
        // Set API limits.
        if (!empty($formdata->uselimits)) {
            $this->uselimits = true;
            if (is_number($formdata->apilimit)) {
                $this->apilimit = $formdata->apilimit;
            }

            if (is_number($formdata->hitlimit)) {
                $this->hitlimit = $formdata->hitlimit;
            }
        }

        // Date limits.
        if (!empty($formdata->usedatestartlimit)) {
            if (is_number($formdata->startdate)) {
                $this->starttime = $formdata->startdate;
            }

            if (is_number($formdata->enddate)) {
                $this->endtime = $formdata->enddate;
            }
        }

        // Set method and keywords.
        $this->method = $formdata->method;
        if ($this->method == 'usekeywords') {
            $this->keywords = array_map('trim', explode(',', $formdata->keyword));
            if (empty($this->keywords)) {
                print_error(get_string('missingkeywords', 'tool_advancedspamcleaner'));
            }
        } else if ($this->method == 'spamauto') {
            $this->keywords = array_map('trim', $this->autokeywords);
        } else {
            if (!in_array($this->method, array_keys($this->pluginlist))) {
                print_error("Invalid sub plugin");
            }
        }
    }

    /**
     * Trigger appropriate spam search and display method, based on the method requested.
     */
    public function spam_search() {
        switch($this->method) {
            case 'spamauto':
            case 'usekeywords':
                $this->spamcleaner->search_spammers($this->formdata, $this->keywords, $this->starttime, $this->endtime, false);
                break;
            default : $this->spam_serach_by_plugin();
        }

    }

    /**
     * Search for spam using the specified plugin.
     */
    public function spam_serach_by_plugin() {
        global $OUTPUT;
        $time = time();
        $plugin = $this->method;
        $pluginclassname = "$plugin" . "_advanced_spam_cleaner";
        $plugin = new $pluginclassname($plugin);
        $this->plugin_user_search($plugin);
        $this->plugin_blogs_search($plugin);
        $this->plugin_forums_search($plugin);
        $this->plugin_comments_search($plugin);

        // Calculate time taken.
        $this->stats['time'] = time() - $time;

        // Print the results.
        echo $OUTPUT->box(get_string('methodused', 'tool_advancedspamcleaner', $plugin->pluginname));
        echo $OUTPUT->box(get_string('showstats', 'tool_advancedspamcleaner', $this->stats));
        $this->spamcleaner->print_table($this->spamusers, '', true, $this->limitflag);
    }

    /**
     * Search for spam in user profiles, using given sub-plugin
     *
     * @param $plugin
     */
    public function plugin_user_search($plugin) {
        if (!empty($this->formdata->searchusers)) {
            $sql  = "SELECT * FROM {user} u
                      WHERE deleted = 0
                        AND id <> :userid
                        AND description != ''
                        AND u.timemodified > :start
                        AND u.timemodified < :end ";  // Exclude oneself.
            $this->plugin_spam_search($plugin, $sql, 'description', 'userdesc', 'users', 'id');
        }
    }

    /**
     * Search for spam in comments, using the given sub-plugin
     *
     * @param $plugin
     */
    public function plugin_comments_search($plugin) {
        if (!empty($this->formdata->searchcomments)) {
            $sql  = "SELECT u.*, c.id as cid, c.content
                        FROM {user} u, {comments} c
                      WHERE u.deleted = 0
                         AND u.id=c.userid
                         AND u.id <> :userid
                         AND c.timecreated > :start AND c.timecreated < :end";
            $this->plugin_spam_search($plugin, $sql, 'content', 'comment', 'comments', 'cid');
        }
    }

    /**
     * Search for spam in private messages, using the given sub-plugin
     *
     * @param $plugin
     */
    public function plugin_msgs_search($plugin) {
        if (!empty($this->formdata->searchmsgs)) {
            $sql  = "SELECT u.*, m.id as mid, m.fullmessage
                        FROM {user} u, {message} m
                      WHERE u.deleted = 0
                         AND u.id=m.useridfrom
                         AND u.id <> :userid
                         AND m.timecreated > :start
                         AND m.timecreated < :end";
            $this->plugin_spam_search($plugin, $sql, 'fullmessage', 'message', 'msgs', 'mid');
        }
    }

    /**
     * Search for spam in forums, using the given sub-plugin
     *
     * @param $plugin
     */
    public function plugin_forums_search($plugin) {
        if (!empty($this->formdata->searchforums)) {
            $sql = "SELECT u.*, fp.id as fid, fp.message
                        FROM {user} u, {forum_posts} fp
                     WHERE u.deleted = 0
                         AND u.id=fp.userid
                         AND u.id <> :userid
                         AND fp.modified > :start
                         AND fp.modified < :end";
            $this->plugin_spam_search($plugin, $sql, 'message', 'forummessage', 'forums', 'fid');
        }
    }

    /**
     * Search for spam in blogs, using the given sub-plugin
     *
     * @param $plugin
     */
    public function plugin_blogs_search($plugin) {
        if (!empty($this->formdata->searchblogs)) {
            $sql = "SELECT u.*, p.id as pid, p.summary
                        FROM {user} u, {post} p
                     WHERE u.deleted = 0
                         AND u.id=p.userid
                         AND u.id <> :userid
                         AND p.lastmodified > :start
                         AND p.lastmodified < :end";
            $this->plugin_spam_search($plugin, $sql, 'summary', 'blogpost', 'blogs', 'pid');

        }
    }

    /**
     * Structures data and triggers requested plugin's spam detection api. Populates stats, and spam users array.
     *
     * @param $plugin      string Requested plugin method.
     * @param $sql         string Sql to get the data to pass onto the plugin for spam checks.
     * @param $text        string Which field to check for spam in the given sql.
     * @param $type        string What is the type of data that is checked.
     * @param $statfield   string Which field in the stat array to increase for this check.
     * @param $id          string Identifier for the spam entry.
     */
    public function plugin_spam_search($plugin, $sql, $text, $type, $statfield, $id) {
        global $USER, $DB;
        $params = array('userid' => $USER->id, 'start' => $this->starttime, 'end' => $this->endtime);
        $users = $DB->get_recordset_sql($sql, $params);
        foreach ($users as $user) {
            // Limit checks.
            if (($this->apilimit != 0 && $this->apilimit <= $this->apicount) || ($this->hitlimit != 0 && $this->hitlimit <= $this->hitcount)) {
                $this->limitflag = true;
                return;
            }
            $this->apicount++;

            // Data should be consistent for the sub-plugins.
            $data = new stdClass();
            $data->email = $user->email;
            $data->ip = $user->lastip;
            $data->text = $user->$text;
            $data->type = $type;
            $isspam = $plugin->detect_spam($data);
            if ($isspam) {
                $this->spamusers[$user->id]['user'] = $user;
                if (empty($this->spamusers[$user->id]['spamcount'])) {
                    $this->spamusers[$user->id]['spamcount'] = 1;
                } else {
                    $this->spamusers[$user->id]['spamcount']++;
                }
                $this->spamusers[$user->id]['spamtext'][$this->spamusers[$user->id]['spamcount']] = array ($type , $data->text, $user->$id);
                $this->hitcount++;
            }
            $this->stats[$statfield]++;
        }
    }

}