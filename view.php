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
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_once($CFG->dirroot . '/mod/switchcast/lib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT);                 // Course Module ID

$url = new moodle_url('/mod/switchcast/view.php', ['id' => $id]);

$PAGE->set_url($url);
$PAGE->requires->jquery();

if (!$cm = get_coursemodule_from_id('switchcast', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", ["id" => $cm->course])) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', ['id' => $course->id]);

require_course_login($course, false, $cm);

if (!$switchcast = switchcast_get_switchcast($cm->instance)) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

if (!in_array($switchcast->organization_domain, mod_switchcast_series::getEnabledOrgnanizations())) {
    print_error('badorganization', 'switchcast', $return_course);
}

$PAGE->set_title(format_string($switchcast->name));
$PAGE->set_heading($course->fullname);

/// Mark as viewed
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$eventparams = ['context' => $context, 'objectid' => $switchcast->id];
$event = \mod_switchcast\event\course_module_viewed::create($eventparams);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('switchcast', $switchcast);
$event->trigger();

$allclips = [];

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_switchcast');
$renderer->display_channel_content();

echo $OUTPUT->footer();

mod_switchcast_series::processUploadedClips();
