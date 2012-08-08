<?php

require_once('akismet.class.php');

class akismet_advanced_spam_cleaner extends base_advanced_spam_cleaner {
    function detect_spam ($data) {
        global $CFG;
        $APIKey = 'xxxxxxx';

        $akismet = new Akismet($CFG->wwwurl ,$APIKey);
        $akismet->setCommentAuthorEmail($data->email);
        $akismet->setCommentContent($data->text);
        $akismet->setUserIP($data->ip);

        if($akismet->isCommentSpam()) {
            return true;
        } else {
            return false;
        }
}
