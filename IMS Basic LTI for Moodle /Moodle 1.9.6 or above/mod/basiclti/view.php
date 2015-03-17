<?php
// This file is part of BasicLTI4Moodle
//
// BasicLTI4Moodle is an IMS BasicLTI (Basic Learning Tools for Interoperability)
// consumer for Moodle 1.9 and Moodle 2.0. BasicLTI is a IMS Standard that allows web
// based learning tools to be easily integrated in LMS as native ones. The IMS BasicLTI
// specification is part of the IMS standard Common Cartridge 1.1 Sakai and other main LMS
// are already supporting or going to support BasicLTI. This project Implements the consumer
// for Moodle. Moodle is a Free Open source Learning Management System by Martin Dougiamas.
// BasicLTI4Moodle is a project iniciated and leaded by Ludo(Marc Alier) and Jordi Piguillem
// at the GESSI research group at UPC.
// SimpleLTI consumer for Moodle is an implementation of the early specification of LTI
// by Charles Severance (Dr Chuck) htp://dr-chuck.com , developed by Jordi Piguillem in a
// Google Summer of Code 2008 project co-mentored by Charles Severance and Marc Alier.
//
// BasicLTI4Moodle is copyright 2009 by Marc Alier Forment, Jordi Piguillem and Nikolas Galanis
// of the Universitat Politecnica de Catalunya http://www.upc.edu
// Contact info: Marc Alier Forment granludo @ gmail.com or marc.alier @ upc.edu
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains all necessary code to view a basiclti activity instance
 *
 * @package basiclti
 * @copyright 2009 Marc Alier, Jordi Piguillem, Nikolas Galanis
 *  marc.alier@upc.edu
 * @copyright 2009 Universitat Politecnica de Catalunya http://www.upc.edu
 *
 * @author Marc Alier
 * @author Jordi Piguillem
 * @author Nikolas Galanis
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once("lib.php");
	require_once("locallib.php");

    $id = optional_param('id', '', PARAM_INT);       // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);         // basic lti ID

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }

        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }

        if (! $basiclti = get_record("basiclti", "id", $cm->instance)) {
            error("Course module is incorrect");
        }

    } else {
        if (! $basiclti = get_record("basiclti", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $basiclti->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("basiclti", $basiclti->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);

    add_to_log($course->id, "basiclti", "view", "view.php?id=$cm->id", "$basiclti->id");

/*
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

            $params = array();
            $params['itemname'] = $basiclti->name;

            $grade = new object();
            $grade->userid   = $USER->id;
            $grade->rawgrade = 19.63;
            $grade->finalgrade = 29.63;
            $grade->itemname = "Fred";
print_r($basiclti);

    grade_update('mod/basiclti', $course->id, 'mod', 'basiclti', $basiclti->id, 0, $grade, $params);

*/


///naarintrint the page header
    $strbasicltis = get_string("modulenameplural", "basiclti");
    $strbasiclti  = get_string("modulename", "basiclti");

    $navlinks = array();
    $navlinks[] = array('name' => $strbasicltis, 'link' => "index.php?id=$course->id", 'type' => 'activity');
    $navlinks[] = array('name' => format_string($basiclti->name), 'link' => '', 'type' => 'activityinstance');

    $navigation = build_navigation($navlinks);

    print_header_simple(format_string($basiclti->name), "", $navigation, "", "", true,
                  update_module_button($cm->id, $course->id, $strbasiclti), navmenu($course, $cm));

	/// Print the main part of the page

    print_box($basiclti->intro, 'generalbox description', 'intro');

    if ($basiclti->instructorchoiceacceptgrades == 1) {
    	echo '<div class="reportlink">'.submittedlink($cm).'</div>';
    }

    print_box_start('generalbox activity');

    if ( $basiclti->launchinpopup > 0 ) {
        print "<script language=\"javascript\">//<![CDATA[\n";
        print "window.open('launch.php?id=".$cm->id."','window_name');";
        print "//]]\n";
        print "</script>\n";
        print "<p>".get_string("basiclti_in_new_window","basiclti")."</p>\n";
    } else {
        // Request the launch content with an iframe
        basiclti_view($basiclti,true);
    }

    print_box_end();

/// Finish the page

    print_footer($course);

?>
