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

require_once("$CFG->libdir/formslib.php");
class tool_advanced_spam_cleaner extends moodleform {
    // Define the form.
    function definition() {
        global $CFG;

        $mform = $this->_form;
        $options = array (
                'spamauto' => get_string('spamauto', 'tool_advancedspamcleaner'),
                'usekeywords' => get_string('usekeywords', 'tool_advancedspamcleaner'));
        $subplugins = (array)$this->_customdata['pluginlist'];

        $options = array_merge($options, $subplugins);

        $mform->addElement('header', 'methodoptions', get_string('methodoptions', 'tool_advancedspamcleaner'));

        $mform->addElement('select', 'method', get_string('method', 'tool_advancedspamcleaner'), $options);
        $mform->addRule('method', get_string('missingmethod', 'tool_advancedspamcleaner'), 'required', null, 'client');

        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_TEXT);
        $mform->setDefault('sesskey', sesskey());

        $mform->addElement('text', 'keyword', get_string('keywordstouse', 'tool_advancedspamcleaner'));
        $mform->setType('keyword', PARAM_TEXT);

        $mform->addElement('header', 'searchscope', get_string('searchscope', 'tool_advancedspamcleaner'));
        $mform->addElement('advcheckbox', 'searchblogs', get_string('searchblogs', 'tool_advancedspamcleaner'));
        $mform->addElement('advcheckbox', 'searchusers', get_string('searchusers', 'tool_advancedspamcleaner'));
        $mform->addElement('advcheckbox', 'searchcomments', get_string('searchcomments', 'tool_advancedspamcleaner'));
        $mform->addElement('advcheckbox', 'searchmsgs', get_string('searchmsgs', 'tool_advancedspamcleaner'));
        $mform->addElement('advcheckbox', 'searchforums', get_string('searchforums', 'tool_advancedspamcleaner'));

        $mform->addElement('header', 'limits', get_string('limits', 'tool_advancedspamcleaner'));
        $mform->addElement('advcheckbox', 'uselimits', get_string('uselimits', 'tool_advancedspamcleaner'));
        $mform->addHelpButton('uselimits', 'uselimits', 'tool_advancedspamcleaner');
        $mform->addElement('text', 'apilimit', get_string('apilimit', 'tool_advancedspamcleaner'));
        $mform->setType('apilimit', PARAM_INT);
        $mform->setDefault('apilimit', 500);
        $mform->disabledif('apilimit', 'uselimits');
        $mform->addHelpButton('apilimit', 'apilimit', 'tool_advancedspamcleaner');
        $mform->addElement('text', 'hitlimit', get_string('hitlimit', 'tool_advancedspamcleaner'));
        $mform->setType('hitlimit', PARAM_INT);
        $mform->setDefault('hitlimit', 1000);
        $mform->disabledif('hitlimit', 'uselimits');
        $mform->addHelpButton('hitlimit', 'hitlimit', 'tool_advancedspamcleaner');

        // Custom date range.
        $mform->addElement('header', 'datelimits', get_string('datelimits', 'tool_advancedspamcleaner'));
        $mform->addElement('advcheckbox', 'usedatestartlimit', get_string('usedatestartlimit', 'tool_advancedspamcleaner'));
        $mform->addHelpButton('usedatestartlimit', 'usedatestartlimit', 'tool_advancedspamcleaner');
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'tool_advancedspamcleaner'));
        $mform->disabledif('startdate', 'usedatestartlimit');
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'tool_advancedspamcleaner'));
        $mform->disabledif('enddate', 'usedatestartlimit');

        $this->add_action_buttons(false, get_string('spamsearch', 'tool_advancedspamcleaner'));
        if ($CFG->version >= 2013042500) {
            // Expand all section to force user see all settings.
            $mform->setExpanded('searchscope');
            $mform->setExpanded('limits');
            $mform->setExpanded('datelimits');
        }
    }
    // Add validations.
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['method'] == 'usekeywords' && empty($data['keyword'])) {
            $errors['keyword'] = get_string('missingkeywords', 'tool_advancedspamcleaner');
        }
        if (empty($data['searchblogs']) && empty($data['searchusers']) && empty($data['searchcomments'])
                && empty($data['searchmsgs']) && empty($data['searchforums'])) {
            $errors['searchblogs'] = get_string('missingscope', 'tool_advancedspamcleaner');
        }
        return $errors;
    }
}
