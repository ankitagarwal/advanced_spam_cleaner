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
 * Set of functions to delete user records, making them inactive and updating
 * there record to reflect spammer.
 *
 * @package tool_advancedspamcleaner
 * @category spam_deletion
 * @copyright 2012 Rajesh Taneja
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class tool_advancedspamcleaner_spammerlib {

    /**
     * @var stdClass user whose account will be marked as spammer
     */
    private $user = null;

    /**
     * Constructor
     * @param int $userid user id for spammer
     */
    public function __construct($userid) {
        global $DB;

        if (!self::is_suspendable_user($userid)) {
            throw new moodle_exception('User passed is not suspendedable');
        }

        $this->user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    }

    /**
     * Is the passed userid able to be suspended as a user?
     *
     * @param int $userid the userid of the user being checked
     * @return bool true if not a guest/admin/currnet user.
     */
    public static function is_suspendable_user($userid) {
        global $USER;

        if (empty($userid)) {
            // Userid of 0.
            return false;
        }

        if ($userid == $USER->id) {
            // Is current user.
            return false;
        }

        if (isguestuser($userid)) {
            return false;
        }

        if (is_siteadmin($userid)) {
            return false;
        }

        return true;
    }

    /**
     * returns true is user is active
     */
    public function is_active() {
        if (($this->user->deleted == 1) || ($this->user->suspended == 1)) {
            return false;
        }
        return true;
    }

    /**
     * returns true if user has first accessed account in last one month
     */
    public function is_recentuser() {
        $timegap = time() - (30 * 24 * 60 * 60);
        if ($this->user->firstaccess > $timegap) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Mark user as spammer, by deactivating account and setting description in profile
     */
    protected function set_profile_as_spammer() {
        global $DB;


        // Remove profile picture files from users file area.
        $fs = get_file_storage();
        $context = context_user::instance($this->user->id, MUST_EXIST);
        $fs->delete_area_files($context->id, 'user', 'icon'); // Drop all images in area.

        $updateuser = new stdClass();
        $updateuser->id = $this->user->id;
        $updateuser->suspended = 1;
        $updateuser->picture = 0;
        $updateuser->imagealt = '';
        $updateuser->url = '';
        $updateuser->icq = '';
        $updateuser->skype = '';
        $updateuser->yahoo = '';
        $updateuser->aim = '';
        $updateuser->msn = '';
        $updateuser->phone1 = '';
        $updateuser->phone2 = '';
        $updateuser->department = '';
        $updateuser->institution = '';
        $updateuser->city = '-';
        $updateuser->description = get_string('spamdescription', 'tool_advancedspamcleaner', date('l jS F g:i A'));

        $DB->update_record('user', $updateuser);

        // Remove custom user profile fields.
        $DB->delete_records('user_info_data', array('userid' => $this->user->id));

        // Force logout.
        \core\session\manager::kill_user_sessions($this->user->id);
    }

    /**
     * Delete all user messages
     */
    protected function delete_user_messages() {
        global $DB;
        $userid = $this->user->id;

        // Delete message workers..
        $DB->delete_records('messages', array('useridfrom' => $userid));
    }

    /**
     * Replace user forum subject and message with spam string
     */
    protected function delete_user_forum() {
        global $DB;

        // Get discussions started by the spammer.
        $rs = $DB->get_recordset('forum_posts', array('userid' => $this->user->id, 'parent' => 0));
        foreach ($rs as $post) {
            // This is really expensive, but it should be rare iterations and i'm lazy right now.
            $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion), '*', MUST_EXIST);
            $forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);

            if ($forum->type == 'single') {
                // It's too complicated, skip.
                continue;
            }

            $course = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
            $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

            forum_delete_discussion($discussion, false, $course, $cm, $forum);
        }
        $rs->close();

        // Delete any remaining posts not discussions..
        $rs = $DB->get_recordset('forum_posts', array('userid' => $this->user->id));
        foreach ($rs as $post) {
            // This is really expensive, but it should be rare iterations and i'm lazy right now.
            $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion), '*', MUST_EXIST);
            $forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);

            if ($forum->type == 'single') {
                // It's too complicated, skip.
                continue;
            }
            $course = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
            $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

            // Recursively delete post and any children.
            forum_delete_post($post, true, $course, $cm, $forum);
        }
        $rs->close();
    }

    /**
     * Delete all user comments
     */
    protected function delete_user_comments() {
        global $DB;
        $userid = $this->user->id;
        $DB->delete_records('comments', array('userid' => $userid));
    }

    /**
     * Delete all tags
     */
    protected function delete_user_tags() {
        core_tag_tag::delete_instances("core", "user", context_user::instance($this->user->id)->id);
    }

    /**
     * Delete any spam reports from this block..
     */
    protected function delete_spam_votes() {
        global $DB;
        $DB->delete_records('tool_advancedspamcleaner_votes', array('spammerid' => $this->user->id));
    }

    /**
     * Delete user records and mark user as spammer, by doing following:
     * 1. Delete comment, message form this user
     * 2. Update forum post and blog post with spam message
     * 3. Suspend account and set profile description as spammer
     */
    public function set_spammer() {
        global $DB;
        // Make sure deletion should only happen for recently created account.
        if ($this->is_active()) {
            $transaction = $DB->start_delegated_transaction();
            try {
                $this->delete_user_comments();
                $this->delete_user_forum();
                $this->delete_user_messages();
                $this->delete_user_tags();
                $this->set_profile_as_spammer();
                $this->delete_spam_votes();
                $transaction->allow_commit();
            } catch (Exception $e) {
                $transaction->rollback($e);
                throw $e;
            }
        } else {
            throw new moodle_exception('cannotdelete', 'tool_advancedspamcleaner');
        }
    }

    /**
     * Return html to show data stats for spammer
     *
     * @return string html showing data count for spammer
     */
    public function show_data_count() {
        global $DB;
        $htmlstr = '';
        $params = array('userid' => $this->user->id);
        $userdata[] = get_string('countmessageread', 'tool_advancedspamcleaner',
                (int)$DB->count_records('messages', array('useridfrom' => $this->user->id)));
        $userdata[] = get_string('countforum', 'tool_advancedspamcleaner',
                (int)$DB->count_records('forum_posts', $params));
        $userdata[] = get_string('countcomment', 'tool_advancedspamcleaner',
                (int)$DB->count_records('comments', $params));
        $userdata[] = get_string('counttags', 'tool_advancedspamcleaner',
                (int)$DB->count_records('tag', $params));
        $htmlstr = html_writer::tag('div class="block_spam_bold block_spam_highlight"', get_string('totalcount', 'tool_advancedspamcleaner'));
        $htmlstr .= html_writer::alist($userdata);
        return $htmlstr;
    }

    /**
     * Return $this->>user.
     *
     * @return mixed|stdClass
     */
    public function get_user() {
        return $this->user;
    }
}
