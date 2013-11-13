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
 * Class test_tool_advancedspamcleaner_manager Test calss for tool_advancedspamcleaner_manager
 */
class tool_advancedspamcleaner_manager_testcase extends advanced_testcase {

    protected function setUp() {
        global $CFG;
        require_once("$CFG->dirroot/$CFG->admin/tool/advancedspamcleaner/lib.php");
    }

    public function test_set_form_data() {

        $this->resetAfterTest();
        $this->setAdminUser();

        $formdata = new stdClass();

        // Limit tests.
        $manager = new tool_advancedspamcleaner_manager();
        $formdata->method = 'spamauto';
        $formdata->uselimits = true;
        $formdata->apilimit = 10;
        $formdata->hitlimit = 5;
        $manager->set_form_data($formdata);
        $this->assertEquals(10, $manager->apilimit);
        $this->assertEquals(5, $manager->hitlimit);
        $this->assertTrue($manager->uselimits);

        $manager = new tool_advancedspamcleaner_manager();
        $formdata->uselimits = false;
        $formdata->apilimit = 10;
        $formdata->hitlimit = 5;
        $manager->set_form_data($formdata);
        $this->assertEquals(0, $manager->apilimit);
        $this->assertEquals(0, $manager->hitlimit);
        $this->assertEquals(0, $manager->apicount);
        $this->assertEquals(0, $manager->hitcount);
        $this->assertFalse($manager->uselimits);

        // Time limits.
        $time = time();
        $manager = new tool_advancedspamcleaner_manager();
        $formdata->usedatestartlimit = true;
        $formdata->startdate = $time;
        $formdata->usedateendlimit = true;
        $formdata->enddate = $time;
        $manager->set_form_data($formdata);
        $this->assertEquals($time, $manager->starttime);
        $this->assertEquals($time, $manager->endtime);

        $time = time();
        $manager = new tool_advancedspamcleaner_manager();
        $formdata->usedatestartlimit = false;
        $formdata->startdate = $time;
        $formdata->usedateendlimit = false;
        $formdata->enddate = $time;
        $manager->set_form_data($formdata);
        $this->assertEquals(0, $manager->starttime);
        $this->assertEquals(time(), $manager->endtime); // Assuming it is fast enough for that!

        // Method test.
        $manager = new tool_advancedspamcleaner_manager();
        $formdata->method = 'usekeywords';
        $formdata->keyword = 'Windows, isnt, a, virus, viruses, do, something';
        $manager->set_form_data($formdata);
        $this->assertEquals('usekeywords', $manager->method);
        $arr = array('Windows', 'isnt', 'a', 'virus', 'viruses', 'do', 'something');
        $this->assertSame($arr, $manager->keywords);

        $formdata->method = 'random';
        $this->setExpectedException('moodle_exception');
        $manager->set_form_data($formdata);
    }

}