<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    mod
 * @subpackage switchcast
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_switchcast_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

/*
    if ($oldversion < 2012042500) {

    /// remove the no longer needed switchcast_answers DB table
        $switchcast_answers = new xmldb_table('switchcast_answers');
        $dbman->drop_table($switchcast_answers);

    /// change the switchcast_options.text (text) field as switchcast_options.groupid (int)
        $switchcast_options =  new xmldb_table('switchcast_options');
        $field_text =           new xmldb_field('text', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'switchcastid');
        $field_groupid =        new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'switchcastid');

        $dbman->rename_field($switchcast_options, $field_text, 'groupid');
        $dbman->change_field_type($switchcast_options, $field_groupid);

    /// switchcast savepoint reached
        upgrade_mod_savepoint(true, 2012042500, 'switchcast');
    }
*/

    return true;
}


