<?php

defined('MOODLE_INTERNAL') || die();

require_once('akismet.class.php');

class akismet_advanced_spam_cleaner extends base_advanced_spam_cleaner {
    function detect_spam ($data) {
        global $CFG;

        $apikey = get_config('advancedspamcleaner', 'akismetkey');
        if (!$apikey) {
            print_error("noakismetkey", 'tool_advancedspamcleaner', new moodle_url($CFG->wwwroot . '/admin/settings.php', array('section' => 'advancedspamcleaner')));
        }

        $akismet = new Akismet($CFG->wwwroot, $apikey);
        $akismet->setCommentAuthorEmail($data->email);
        $akismet->setCommentContent($data->text);
        $akismet->setUserIP($data->ip);

        if($akismet->isCommentSpam()) {
            return true;
        } else {
            return false;
        }
    }
}
