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
 * This file contains the script used to clone Moodle admin setting page.
 * It is used to create a new form used to pre-configure basiclti
 * activities
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

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/pagelib.php');
require_once('edit_form.php');
require_once('locallib.php');

$section      = 'modsettingbasiclti';
$return       = optional_param('return','', PARAM_ALPHA);
$adminediting = optional_param('adminedit', -1, PARAM_BOOL);
$action       = optional_param('action',null, PARAM_TEXT);
$id 	      = optional_param('id',null, PARAM_INT);

/// no guest autologin
require_login(0, false);

$adminroot =& admin_get_root(); // need all settings
$page      =& $adminroot->locate($section);

if (empty($page) or !is_a($page, 'admin_settingpage')) {
    print_error('sectionerror', 'admin', "$CFG->wwwroot/$CFG->admin/");
    die;
}

if (!($page->check_access())) {
    print_error('accessdenied', 'admin');
    die;
}

/// WRITING SUBMITTED DATA (IF ANY) -------------------------------------------------------------------------------

$statusmsg = '';
$errormsg  = '';
$focus = '';

if ($data = data_submitted() and confirm_sesskey() and isset($data->submitbutton)) {


	if (isset($id)){
		$type = new StdClass();
		$type->id = $id;
		$type->name = $data->lti_typename;
		$type->rawname = eregi_replace('[^a-zA-Z]', '', $type->name);
	    if (update_record('basiclti_types', $type)) {
			unset ($data->lti_typename);
			//@TODO: update work
			foreach ($data as $key => $value){
				if (substr($key,0,4)=='lti_' && !is_null($value)){
					$record = new StdClass();
					$record->typeid = $id;
					$record->name = substr($key,4);
					$record->value = $value;
					if (basiclti_update_config($record)){
				        $statusmsg = get_string('changessaved');
					} else {
				        $errormsg = get_string('errorwithsettings', 'admin');
					}
				}
		    }
	    }
	} else {
		$type = new StdClass();
		$type->name = $data->lti_typename;
		$type->rawname = eregi_replace('[^a-zA-Z]', '', $type->name);
		if ($id = insert_record('basiclti_types', $type)){
			unset ($data->lti_typename);
		    foreach ($data as $key => $value){
				if (substr($key,0,4)=='lti_' && !is_null($value)){
					$record = new StdClass();
					$record->typeid = $id;
					$record->name = substr($key,4);
					$record->value = $value;
					if (basiclti_add_config($record)){
				        $statusmsg = get_string('changessaved');
					} else {
				        $errormsg = get_string('errorwithsettings', 'admin');
					}
				}
		    }
		} else {
	        $errormsg = get_string('errorwithsettings', 'admin');
		}
	}
    if (empty($adminroot->errors)) {
        switch ($return) {
            case 'site':  redirect("$CFG->wwwroot/");
            case 'admin': redirect("$CFG->wwwroot/$CFG->admin/");
        }
    } else {
        $errormsg = get_string('errorwithsettings', 'admin');
        $firsterror = reset($adminroot->errors);
        $focus = $firsterror->id;
    }
    $adminroot =& admin_get_root(true); //reload tree
    $page      =& $adminroot->locate($section);
}

/// very hacky page setup
page_map_class(PAGE_ADMIN, 'page_admin');
$PAGE = page_create_object(PAGE_ADMIN, 0); // there must be any constant id number
$PAGE->init_extra($section);
$CFG->pagepath = 'admin/setting/'.$section;

if (!isset($USER->adminediting)) {
    $USER->adminediting = false;
}

if ($PAGE->user_allowed_editing()) {
    if ($adminediting == 1) {
        $USER->adminediting = true;
    } elseif ($adminediting == 0) {
        $USER->adminediting = false;
    }
}

$pageblocks = blocks_setup($PAGE);

$preferred_width_left  = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]),
                                        BLOCK_L_MAX_WIDTH);
$preferred_width_right = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]),
                                        BLOCK_R_MAX_WIDTH);

$PAGE->print_header('', $focus);
echo '<table id="layout-table"><tr>';
$lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
foreach ($lt as $column) {
    switch ($column) {
        case 'left':
echo '<td style="width: '.$preferred_width_left.'px;" id="left-column">';
print_container_start();
blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
print_container_end();
echo '</td>';
        break;
        case 'middle':
echo '<td id="middle-column">';
print_container_start();
echo '<a name="startofcontent"></a>';

if ($errormsg !== '') {
    notify ($errormsg);

} else if ($statusmsg !== '') {
    notify ($statusmsg, 'notifysuccess');
}


print_heading($page->visiblename);

if ($action == 'add'){
    $form = new mod_basiclti_edit_types_form();
    $form->display();
} else if ($action == 'update'){
    $form = new mod_basiclti_edit_types_form('typessettings.php?id='.$id);
    $type = basiclti_get_type_type_config($id);
    $form->set_data($type);
    $form->display();
} else if ($action == 'delete'){
    basiclti_delete_type($id);
    echo '<fieldset>';
    echo '<h4 class="main"><a href="typessettings.php?action=add&amp;sesskey='.$USER->sesskey.'">'.get_string('addtype','basiclti').'</a></h4>';
    basiclti_filter_print_types();
    echo '</fieldset>';
    echo '<div class="mdl-align"><a href="'.$CFG->wwwroot.'/admin/settings.php?section=modsettingbasiclti">'.get_string('back').'</a></div>';

} else {
    echo '<fieldset>';
    echo '<h4 class="main"><a href="typessettings.php?action=add&amp;sesskey='.$USER->sesskey.'">'.get_string('addtype','basiclti').'</a></h4>';
    basiclti_filter_print_types();
    echo '</fieldset>';
    echo '<div class="mdl-align"><a href="'.$CFG->wwwroot.'/admin/settings.php?section=modsettingbasiclti">'.get_string('back').'</a></div>';

}

print_container_end();
echo '</td>';
        break;
        case 'right':
if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT)) {
    echo '<td style="width: '.$preferred_width_right.'px;" id="right-column">';
    print_container_start();
    blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
    print_container_end();
    echo '</td>';
}
        break;
    }
}
echo '</tr></table>';


if (!empty($CFG->adminusehtmleditor)) {
    use_html_editor();
}

print_footer();

?>
