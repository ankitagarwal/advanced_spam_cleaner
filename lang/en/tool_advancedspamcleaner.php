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

/**
 * Strings for component 'tool_spamcleaner', language 'en', branch 'MOODLE_22_STABLE'
 *
 * @package    tool
 * @subpackage spamcleaner
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['akismetkey'] = 'Your akismet key';
$string['akismetkey_desc'] = 'Enter the key that you got from akismet.com';
$string['apilimit'] = 'Api limit';
$string['apilimit_help'] = 'Maximum amount of Api calls to make. (0 = unlimited)';
$string['blogpost'] = 'Blog post';
$string['blogsummar'] = 'Blog summary';
$string['commment'] = 'Comment';
$string['datelimits'] = 'Date limits';
$string['enddate'] = 'End date';
$string['forumsubject'] = 'Forum subject';
$string['forummessage'] = 'Forum message';
$string['hitlimit'] = 'Hit limit';
$string['hitlimit_help'] = 'Stop after this amount of spam entries have been detected (0 = unlimited)';
$string['keywordstouse'] = 'Keywords to use';
$string['limits'] = 'Limits';
$string['limithit'] = 'Set limit was hit. Results that follow may not be complete..';
$string['message'] = 'Message';
$string['method'] = 'Method to use';
$string['methodused'] = 'Spam detection method used: {$a}';
$string['methodoptions'] = 'Method options';
$string['missingkeywords'] = 'Keywords cannot be empty';
$string['missingmethod'] = 'Method to use cannot be empty';
$string['missingscope'] = 'No scope specified to search';
$string['noakismetkey'] = 'Set api key from settings page before using this option';
$string['nukeuser'] = 'Nuke user';
$string['pluginname'] = 'Advanced spam cleaner';
$string['pluginpage'] = 'Plugin page';
$string['pluginsettings'] = 'Advanced spam cleaner sub-plugins settings for {$a}';
$string['reportissue'] = 'Report an issue';
$string['searchblogs'] = 'Include blogs';
$string['searchcomments'] = 'Include comments';
$string['searchforums'] = 'Include forums';
$string['searchmsgs'] = 'Include messages';
$string['searchscope'] = 'Scope of spam search';
$string['searchusers'] = 'Include user profiles';
$string['settingpage'] = 'Advanced spam cleaner settings';
$string['showstats'] = 'Following number of entries were checked for spam : <br/> Blogs: {$a->blogs}, User Profiles: {$a->users}, Comments: {$a->comments},
    Messages: {$a->msgs}, Forum Posts: {$a->forums} <br/> Time used was {$a->time} seconds approximately';
$string['spamauto'] = 'Auto detect spam using common spam keywords';
$string['spamcannotdelete'] = 'Cannot delete this user';
$string['spamcannotfinduser'] = 'No users matching your search';
$string['spamcleanerintro'] = 'This script allows you to search all user profiles , comments, blogposts, forum posts and messages for certain strings and then delete those accounts which are obviously created by spammers.
    You can search for multiple keywords using commas (eg casino, porn) or use a third party system to scan your site (eg Akismet).
    Please note this can take a while based on your method of search. Use limits to reduce scope of search.';
$string['spamcount'] = 'Spam count';
$string['spamtext'] = 'Spam text';
$string['spamtype'] = 'Spam type';
$string['spamdeleteall'] = 'Delete all these user accounts';
$string['spamdeleteallconfirm'] = 'Are you sure you want to delete all these user accounts?  You can not undo this.';
$string['spamdeleteconfirm'] = 'Are you sure you want to delete this entry?  You can not undo this.';
$string['spamdesc'] = 'Description';
$string['spameg'] = 'eg:  casino, porn, xxx';
$string['spamfromblog'] = 'From blog post:';
$string['spamfromcomments'] = 'From comments:';
$string['spamfrommessages'] = 'From messages:';
$string['spamfromforumpost'] = 'From forum post:';
$string['spaminvalidresult'] = 'Unknown but invalid result';
$string['spamoperation'] = 'Operation';
$string['spamresult'] = 'Please note deleting a user doesnt delete the spammed entry <br /> Results of searching user profiles containing:';
$string['spamsearch'] = 'Search for spam';
$string['startdate'] = 'Start date';
$string['usekeywords'] = 'Use the entered keywords';
$string['uselimits'] = 'Use limits';
$string['uselimits_help'] = 'Use limits to reduce resource usage <br /> (Note that limits are not used for auto detect and keyword methods)';
$string['usedatestartlimit'] = 'Use date limits';
$string['usedatestartlimit_help'] = 'Enable to run the spam search on entities only between the selected date range';
$string['userdesc'] = 'User description';

// -------------------------------Block spam strings -----------------------------------------------------------

$string['alreadyreported'] = 'You\'ve already reported this content as spam.';
$string['cannotdelete'] = 'Cannot delete content for this user.';
$string['confirmdeletemsg'] = 'Are you sure, you want to mark <strong>{$a->firstname} {$a->lastname} ({$a->username})</strong> as spammer? Data belonging to this user will be blanked out or removed.';
$string['confirmdelete'] = 'Delete spammer';
$string['confirmspamreportmsg'] = 'Are you sure you wish to report this content as spam?';
$string['countmessageunread'] = 'Unread messages: {$a}';
$string['countmessageread'] = 'Read messages: {$a}';
$string['countforum'] = 'Forum posts: {$a}';
$string['countcomment'] = 'Comments: {$a}';
$string['counttags'] = 'Unique tags: {$a}';
$string['deletebutton'] = 'Nuke spammer';
$string['notrecentlyaccessed'] = 'Beware! The first access date of this account is more than 1 month ago. Make double sure it is really a spammer.';
$string['messageprovider:spamreport'] = 'Spam report';
$string['messageblocked'] = 'Your post has been blocked, as our spam prevention system has flagged it as possibly containing spam. If this is not the case, please see \'My post has been incorrectly flagged as containing spam\' in <a href="http://docs.moodle.org/en/Moodle.org_FAQ#My_post_has_been_incorrectly_flagged_as_containing_spam">http://docs.moodle.org/en/Moodle.org_FAQ</a>. Your message is below if you need to copy and paste it.';
$string['messageblockedtitle'] = 'Potential spam detected!';
$string['reportasspam'] = 'Report as spam';
$string['reportcontentasspam'] = 'Report content as spam';
$string['spamreportmessage'] = '{$a->spammer} may be a spammer.
View spam reports at {$a->url}';
$string['spamreportmessagetitle'] = '{$a->spammer} may be a spammer.';
$string['spam_deletion:addinstance'] = 'Add delete spammer block';
$string['spam_deletion:spamdelete'] = 'Delete Spam';
$string['spam_deletion:viewspamreport'] = 'View spam reports';
$string['spamdescription'] = 'Spammer - spam deleted and account blocked {$a}';
$string['spamreports'] = 'Spam reports: {$a}';
$string['thanksspamrecorded'] = 'Thanks, your spam report has been recorded.';
$string['totalcount'] = 'Total records by this user:-';
$string['realtime'] = 'Scan for spam content on every create action';
$string['realtime_desc'] = 'If enabled, advancedspam cleaner will scan all new content posted on the site for spam signatures in realtime';
$string['realtimeplugin'] = 'Plugin to use for real time scans';