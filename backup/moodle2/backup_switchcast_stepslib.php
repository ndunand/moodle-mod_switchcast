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
 * @copyright  2013-2015 Université de Lausanne
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the backup steps that will be used by the backup_switchcast_activity_task
 */

/**
 * Define the complete switchcast structure for backup, with file and id annotations
 */
class backup_switchcast_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated
        $switchcast = new backup_nested_element('switchcast', ['id'],
                ['ext_id', 'name', 'intro', 'introformat', 'is_ivt', 'inviting', 'organization_domain']);

        // Define sources
        $switchcast->set_source_table('switchcast', ['id' => backup::VAR_ACTIVITYID]);

        // Define file annotations
        $switchcast->annotate_files('mod_switchcast', 'intro', null); // This file area hasn't itemid

        // Return the root element (switchcast), wrapped into standard activity structure
        return $this->prepare_activity_structure($switchcast);
    }
}
