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
 * This file keeps track of upgrades to the basiclti module
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


/**
 * xmldb_basiclti_upgrade is the function that upgrades Moodle's
 * database when is needed
 *
 * This function is automaticly called when version number in
 * version.php changes.
 *
 * @param int $oldversion New old version number.
 *
 * @return boolean
 */

function xmldb_basiclti_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    if ($result && $oldversion < 2008090201) {

        $table = new XMLDBTable('basiclti_types');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $result = $result && create_table($table);

        $table = new XMLDBTable('basiclti_types_config');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('typeid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('value', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $result = $result && create_table($table);

        $table = new XMLDBTable('basiclti');
        $field = new XMLDBField('typeid');

        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null , null, null, null, null, null);
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2008091201) {
        $table = new XMLDBTable('basiclti_types');
        $field = new XMLDBField('rawname');

        $field->setAttributes(XMLDB_TYPE_CHAR, '100', null,  XMLDB_NOTNULL, null, null, null, null, null);
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2010091500) {
        $table = new XMLDBTable('basiclti');

        $field = new XMLDBField('acceptgrades');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('instructorchoiceacceptgrades');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('gradesecret');
        $field->setAttributes(XMLDB_TYPE_CHAR, '1024', null,  XMLDB_NOTNULL, null, null, null, '', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('timegradesecret');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('oldgradesecret');
        $field->setAttributes(XMLDB_TYPE_CHAR, '1024', null,  XMLDB_NOTNULL, null, null, null, '', null);
        $result = $result && add_field($table, $field);

        // Set gradesecret and timegradesecret values for already configured tools
        $tools = get_records('basiclti', 'gradesecret', '');
        if ($tools) {
            foreach ($tools as $tool) {
                set_field('basiclti', 'gradesecret', uniqid('',true), 'id', $tool->id);
                set_field('basiclti', 'timegradesecret', time(), 'id', $tool->id);
            }
        }
    }

    if ($result && $oldversion < 2010121601) {
        $table = new XMLDBTable('basiclti');

        $field = new XMLDBField('allowroster');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('instructorchoiceallowroster');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('allowsetting');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('instructorchoiceallowsetting');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('setting');
        $field->setAttributes(XMLDB_TYPE_CHAR, '8192', null, null, null, null, null, '', null);
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('gradesecret');
        $field->setAttributes(XMLDB_TYPE_CHAR, '1024', null,  null, null, null, null, '', null);
        $result = $result && rename_field($table, $field, 'placementsecret', false);

        $field = new XMLDBField('timegradesecret');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '0', null);
        $result = $result && rename_field($table, $field, 'timeplacementsecret', false);

        $field = new XMLDBField('oldgradesecret');
        $field->setAttributes(XMLDB_TYPE_CHAR, '1024', null,  null, null, null, null, '', null);
        $result = $result && rename_field($table, $field, 'oldplacementsecret', false);
    }


    if ($result && $oldversion < 2011041103) {
        $table = new XMLDBTable('basiclti');
        $field = new XMLDBField('grade');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, '100', null);
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2011062800) {
        $table = new XMLDBTable('basiclti');

        $field = new XMLDBField('resourcekey');
        if (field_exists($table, $field)) {
            $result = $result && drop_field($table, $field);
        }

        $field = new XMLDBField('password');
        if (field_exists($table, $field)) {
            $result = $result && drop_field($table, $field);
        }

        $field = new XMLDBField('sendname');
        if (field_exists($table, $field)) {
            $result = $result && drop_field($table, $field);
        }

        $field = new XMLDBField('sendemailaddr');
        if (field_exists($table, $field)) {
            $result = $result && drop_field($table, $field);
        }

        $field = new XMLDBField('allowroster');
        if (field_exists($table, $field)) {
            $result = $result && drop_field($table, $field);
        }

        $field = new XMLDBField('allowsetting');
        if (field_exists($table, $field)) {
            $result = $result && drop_field($table, $field);
        }

        $field = new XMLDBField('acceptgrades');
        if (field_exists($table, $field)) {
            drop_field($table, $field);
        }

        $field = new XMLDBField('customparameters');
        if (field_exists($table, $field)) {
            $result = $result && drop_field($table, $field);
        }
    }

    if ($result && $oldversion < 2011070400) {
        $table = new XMLDBTable('basiclti');

        $field = new XMLDBField('instructorcustomparameters');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, '', null);
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2012031500) {
        $table = new XMLDBTable('basiclti');

        $field = new XMLDBField('setting');
        $field->setAttributes(XMLDB_TYPE_CHAR, '1024', null, null, null, '', null);

        /// Launch change of precision for field setting
        $result = $result && change_field_precision($table, $field);
    }

    return $result;
}
?>
