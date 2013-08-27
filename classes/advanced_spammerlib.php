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
 * Class advanced_spammerlib
 * Extends basic spammerlib class and adds our customisations
 */
class tool_advancedspamcleaner_advanced_spammerlib extends tool_advancedspamcleaner_spammerlib{
    /**
     * Print a button to nuke a user or print error string if the user cannot be suspended
     *
     * @param int  $userid userid to be nuked
     * @param bool $return return or print
     *
     * @return string
     */
    public static function nuke_user_button ($userid, $return = false) {
        if (!self::is_suspendable_user($userid)) {
            if ($return) {
                return get_string('cannotdelete', 'tool_advancedspamcleaner');
            } else {
                p(get_string('cannotdelete', 'tool_advancedspamcleaner'));
                return;
            }

        }

        // Add delete button.
        $urlparams = array('userid' => $userid);
        $url = new moodle_url('/admin/tool/advancedspamcleaner/confirmdelete.php', $urlparams);
        $buttontext = get_string('deletebutton', 'tool_advancedspamcleaner');
        $button = new single_button($url, $buttontext);
        $content = self::render_single_button($button);
        if ($return) {
            return $content;
        } else {
            echo $content;
        }
    }

    /**
     * Delete user records and mark user as spammer, by doing following:
     * 1. Delete comment, message form this user
     * 2. Update forum post and blog post with spam message
     * 3. Suspend account and set profile description as spammer
     *
     * Extenstion was required to remove references to votes table, present in upstream.
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
     * Renders a single button widget.
     *
     * This will return HTML to display a form containing a single button.
     * We need this to add target = _blank. Moodle doesn't support it.
     *
     * @param single_button $button
     * @return string HTML fragment
     */
    private static function render_single_button(single_button $button) {
        $attributes = array('type'     => 'submit',
                            'value'    => $button->label,
                            'disabled' => $button->disabled ? 'disabled' : null,
                            'title'    => $button->tooltip);

        // First the input element.
        $output = html_writer::empty_tag('input', $attributes);

        // Then hidden fields.
        $params = $button->url->params();
        if ($button->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $var => $val) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
        }

        // Then div wrapper for xhtml strictness.
        $output = html_writer::tag('div', $output);

        // Now the form itself around it.
        if ($button->method === 'get') {
            $url = $button->url->out_omit_querystring(true); // url without params, the anchor part allowed
        } else {
            $url = $button->url->out_omit_querystring();     // url without params, the anchor part not allowed
        }
        if ($url === '') {
            $url = '#'; // There has to be always some action.
        }
        $attributes = array('method' => $button->method,
                            'action' => $url,
                            'id'     => $button->formid,
                            'target' => '_blank');
        $output = html_writer::tag('form', $output, $attributes);

        // And finally one more wrapper with class.
        return html_writer::tag('div', $output, array('class' => $button->class));
    }

}