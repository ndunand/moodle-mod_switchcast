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

require_once('../../config.php');

require_once($CFG->dirroot.'/mod/switchcast/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_user.class.php');

$id         = required_param('id', PARAM_INT);                 // Course Module ID

$url = new moodle_url('/mod/switchcast/view.php', array('id' => $id));

$PAGE->set_url($url);

if ($CFG->version >= 2013051400)
    // Moodle 2.5 or later
    $PAGE->requires->jquery();
else {
    // earlier Moodle versions
    $PAGE->requires->js('/mod/switchcast/js/jquery-1.9.1.min.js');
}

if (! $cm = get_coursemodule_from_id('switchcast', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', array('id' => $course->id));

require_course_login($course, false, $cm);

if (! $switchcast = switchcast_get_switchcast($cm->instance)) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (! $context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
    print_error('badcontext', null, $return_course);
}

if (! in_array($switchcast->organization_domain, scast_obj::getEnabledOrgnanizations())) {
    print_error('badorganization', 'switchcast', $return_course);
}

$PAGE->set_title(format_string($switchcast->name));
$PAGE->set_heading($course->fullname);

/// Mark as viewed
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

add_to_log($course->id, 'switchcast', 'view', 'view.php?id='.$cm->id, $switchcast->name, $cm->id);

echo $OUTPUT->header();

// Check to see if groups are being used in this module
//$groupmode = groups_get_activity_groupmode($cm);
//if ($groupmode) {
//    groups_get_activity_group($cm, true);
//    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/switchcast/view.php?id='.$id);
//}

$renderer = $PAGE->get_renderer('mod_switchcast');
$renderer->display_channel_content();

echo $OUTPUT->footer();

