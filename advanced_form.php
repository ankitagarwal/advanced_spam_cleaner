<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
class tool_advanced_spam_cleaner extends moodleform {
    // Define the form
    function definition() {

        $mform = $this->_form;
        $options = array (
                'spamauto' => get_string('spamauto', 'tool_advancedspamcleaner'),
                'usekeywords' => get_string('usekeywords', 'tool_advancedspamcleaner'));
        $subplugins = array();
        //$subplugins = advancedspam_get_subplugins();

        $options = array_merge($options, $subplugins);

        $mform->addElement('header', 'allowheader', get_string('roleallowheader', 'role'));

        $mform->addElement('select', 'method', get_string('method', 'tool_advancedspamcleaner'), $options);
        $mform->addRule('method', get_string('missingmethod', 'tool_advancedspamcleaner'), 'required', null, 'client');

        $mform->addElement('hidden','sesskey');
        $mform->setType('sesskey', PARAM_TEXT);
        $mform->setDefault('sesskey', sesskey());

        $mform->addElement('text','keyword', get_string('keywordstouse', 'tool_advancedspamcleaner'));
        $mform->setType('keyword', PARAM_TEXT);

        $mform->addElement('header', 'repeatedevents', get_string('searchscope', 'tool_advancedspamcleaner'));
        $mform->addElement('checkbox', 'searchblogs', get_string('searchblogs', 'tool_advancedspamcleaner'));
        $mform->addElement('checkbox', 'searchusers', get_string('searchusers', 'tool_advancedspamcleaner'));
        $mform->addElement('checkbox', 'searchcomments', get_string('searchcomments', 'tool_advancedspamcleaner'));
        $mform->addElement('checkbox', 'searchmsgs', get_string('searchmsgs', 'tool_advancedspamcleaner'));
        $mform->addElement('checkbox', 'searchforums', get_string('searchforums', 'tool_advancedspamcleaner'));
        $mform->addElement('checkbox', 'repeat', get_string('repeatevent', 'calendar'), null, 'repeat');

        $this->add_action_buttons(true, get_string('spamsearch', 'tool_advancedspamcleaner'));
    }
    // add validations
    function validation($data, $files) {
        $errors = array();
        $errors = parent::validation($data, $files);
        if ($data['method'] == 'usekeywords' && empty($data['keyword'])) {
            $errors['keyword'] = get_string('missingkeywords', 'tool_advancedspamcleaner');
        }
        return $errors;

    }
}
