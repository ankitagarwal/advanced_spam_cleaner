advanced_spam_cleaner
=====================
Advanced Spam Cleaner tool for Moodle

This plugin helps moodle administrators to find and remove spamming users in Moodle. It supports various methods for spam detections, including akismet apis,
which is used by most wordpress sites for spam detection. Also the plugin provides hooks, if you want to implement any other thrid party api,
spam detection methods.

Installation Instruction
=====================

* Extrat the folder from zip and copy it to your moodle/admin/tool
* Login as admin and go to site admin>reports>advanced spam cleaner settings and change the settings as required
* Goto site admin > reports > advanced spam cleaner page to run the tool
* Due to destructive nature of this tool, only a moodle administrator is allowed to run/manage this tool.

Disclaimer
=====================
This tool can be destructive if not used properly. I recomend you backup your install before deleting users using this plugin. Use this plugin at your own
risk.

Things to do
=====================
* Adding paging support
* Adding support to remember last run time and search since last run
* Highlight spam keywords in the results
* Redo the ajax methods
* Pro-active marking of spam!

Maturity
====================
This version is a BETA release.
Please use version 1.6 of the plugin if you want a STABLE release.

Features
====================
* Supports keyword based search
* Can stop based on a specificed spam hit limit or api call limit
* Can search for specified date range
* Easy to implement any third party apis
* Akismet Api support included by default
* Ability to completly delete all contents from spamming users, including blogs, forums, profile cleanup, comments and private messages.

Change log
=====================
* 2012070801 - First public release - 1.0
* 2012070816 - Adding support for custom date range - 1.1
* 2012070821 - Minor bug fixes - 1.2
* 2012121900 - Minor bug fixes and improvments - 1.3
* 2013032200 - Minor bug fixes, sats report, and other minor improvments - 1.4
* 2013041000 - Turn off debug prints - 1.5
* 2013042900 - Bug fixes, form improvments - 1.6
* 2013072600 - Code cleanup, nuke user support, massive refactoring,unit tests - 2.0-beta
* 2013091800 - Minor bug fixes - 2.0-beta
* 2013111300 - $CFG->admin support, autoloading for unittests, - 2.0
* 2015011900 - more unit tests, oracle bug fixes, autloading of plugin base class, travis support - 2.1-beta
* 2017013100 - Cleanup, remove deprecated apis from core, make phpunit tests phpunit 6.0 compatible, change versions - 2.2-beta


About Author
=====================
Ankit Kumar Agarwal

Moodle HQ developer

https://github.com/ankitagarwal

http://ankitkumaragarwal.com

License
=====================

GPL 3 or later

Report bugs
=====================
http://tracker.moodle.org/browse/CONTRIB/component/12336

Credits
====================
Jason Fowler for the awesome logo.
