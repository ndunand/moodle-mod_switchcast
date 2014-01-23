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

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/switchcast/lib.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_clip.class.php');

$id             = required_param('id', PARAM_INT);                 // Course Module ID
$clip_ext_id    = required_param('clip_ext_id', PARAM_ALPHANUM);   // Clip ext_id
$confirm        = optional_param('confirm', 0, PARAM_INT);

$url = new moodle_url('/mod/switchcast/clip_delete.php', array('id' => $id, 'clip_ext_id' => $clip_ext_id));
$return_channel = new moodle_url('/mod/switchcast/view.php', array('id' => $id));

if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}

$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('switchcast', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);

if (!$switchcast = switchcast_get_switchcast($cm->instance)) {
    print_error('invalidcoursemodule');
}

if (! $context = context_module::instance($cm->id)) {
    print_error('badcontext');
}

if (!has_capability('mod/switchcast:isproducer', $context)) {
    print_error('feature_forbidden', 'switchcast', $return_channel);
}

$sc_obj  = new scast_obj();
$sc_obj->doRead($switchcast->id);
$sc_clip = new scast_clip($sc_obj, $clip_ext_id);


// Perform action ?
if (    $confirm === 1
        && confirm_sesskey()
        && has_capability('mod/switchcast:isproducer', $context)
    ) {
    /*
     * $confirm
     * AND sesskey() ok
     * AND $USER has producer rights
     */
    $sc_clip->doDelete();

    add_to_log($course->id, 'switchcast', 'delete clip', 'clip_delete.php?id='.$id.'&clip_id='.$clip_ext_id, $sc_clip->getTitle());

    redirect($return_channel);

}


// Display

$PAGE->set_title(format_string($switchcast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_switchcast');

echo html_writer::tag('h2', get_string('delete_clip', 'switchcast'));
echo html_writer::start_tag('table', array('class' => 'switchcast-clips'));
$renderer->display_singleclip_table_header(false, $sc_obj->getIvt(), $switchcast->userupload, false);
$renderer->display_clip_outline($sc_clip, false, false, null, $sc_obj->getIvt(), $switchcast->userupload, false);
echo html_writer::end_tag('table');

$delete_url = new moodle_url('/mod/switchcast/clip_delete.php', array('sesskey' => sesskey(), 'confirm' => 1, 'id' => $id, 'clip_ext_id' => $clip_ext_id));
$button = new single_button($delete_url, get_string('delete_clip','switchcast'), 'post');
echo $OUTPUT->confirm(get_string('delete_clip_confirm', 'switchcast'), $button, $return_channel);
echo $OUTPUT->footer();

