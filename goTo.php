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
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


include('../../config.php');

require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');

$url_b64    = required_param('url', PARAM_RAW);
$swid       = required_param('swid', PARAM_INT);
$tk         = required_param('tk', PARAM_RAW);

if (! $switchcast = $DB->get_record('switchcast', array('id' => $swid))) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record('course', array('id' => $switchcast->course))) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', array('id' => $course->id));

if (! $module = $DB->get_record('modules', array('name' => 'switchcast'))) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (! $cm = $DB->get_record('course_modules', array('course' => $course->id, 'module' => $module->id, 'instance' => $switchcast->id))) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (! $context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

$url = base64_decode($url_b64);

if ($tk == sha1( scast_obj::getValueByKey('default_sysaccount') . $swid . $url )) {
    $SESSION->switchcastid = $swid;
    $eventparams = array(
        'context' => $context,
        'objectid' => $switchcast->id
    );
    $event = \mod_switchcast\event\clip_viewed::create($eventparams);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('switchcast', $switchcast);
    $event->trigger();
    redirect($url);
    exit;
}

print_error('redirfailed', 'switchcast');