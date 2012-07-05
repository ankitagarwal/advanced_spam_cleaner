<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
class tool_advanced_spam_cleaner extends moodleform {
    // Define the form
    function definition() {
        global $CFG;

        $mform = $this->_form;
        $options = array (
                'spamauto' => get_string('spamauto', 'tool_advancedspamcleaner'),
                'usekeywords' => get_string('usekeywords', 'tool_advancedspamcleaner'));
        $subplugins = advancedspam_get_subplugins();

        $options = array_merge($options, $subplugins);

        $mform->addElement('header', 'allowheader', get_string('roleallowheader', 'role'));

        $mform->addElement('select', 'method', get_string('method', 'tool_advancedspamcleaner'), $options);
        $mform->addRule('method', get_string('missingmethod', 'tool_advancedspamcleaner'), 'required', null, 'client');

        $mform->addElement('hidden','sesskey');
        $mform->setType('sesskey', PARAM_TEXT);
        $mform->setDefault('sesskey', sesskey());

        $mform->addElement('text','keyword');
        $mform->setType('keyword', PARAM_TEXT);



        $this->add_action_buttons(true, get_string('spamsearch', 'tool_advancedspamcleaner'));
    }
    // add validations
}
