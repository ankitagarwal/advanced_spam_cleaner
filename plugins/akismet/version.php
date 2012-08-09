<?php

/**
 * Akismet plugin for spam cleaning
 *
 * @package   advancedspamcleaner
 * @copyright 2012 Ankit Agarwal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * This is just a dummy file...Moodle doesnt support sub-plugins for tool plugins
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2012080900;
$plugin->requires = 2012080100;
$plugin->component = 'advancedspamcleaner_akismet';
$plugin->dependencies = array('tool_advancedspamcleaner' => ANY_VERSION);
