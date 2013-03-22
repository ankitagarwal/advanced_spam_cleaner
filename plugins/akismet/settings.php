<?php
defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext('advancedspamcleaner/akismetkey',
                get_string('akismetkey', 'tool_advancedspamcleaner'), get_string('akismetkey_desc', 'tool_advancedspamcleaner'),
                null, PARAM_TEXT));