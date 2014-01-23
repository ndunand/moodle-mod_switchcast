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
$action         = optional_param('action', '', PARAM_ALPHA);
$userid         = optional_param('userid', 0, PARAM_INT);

$url = new moodle_url('/mod/switchcast/clip_members.php', array('id' => $id, 'clip_ext_id' => $clip_ext_id));
$return_channel = new moodle_url('/mod/switchcast/view.php', array('id' => $id));

//if ($action !== '' && $userid !== 0) {
//    $url->param('action', $action);
//    $url->param('userid', $userid);
//}

$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('switchcast', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', array('id' => $course->id));

if ($userid !== 0 && ! $user = $DB->get_record('user', array('id' => $userid))) {
    print_error('invaliduser', null, $return_channel);
}

if ($action !== '' && !in_array($action, array('add', 'remove'))) {
    print_error('invalidaction', null, $return_channel);
}

require_course_login($course, false, $cm);

if (!$switchcast = switchcast_get_switchcast($cm->instance)) {
    print_error('invalidcoursemodule', null, $return_course);
}

$sc_obj  = new scast_obj();
$sc_obj->doRead($switchcast->id);
$sc_clip = new scast_clip($sc_obj, $clip_ext_id, false, $switchcast->id);

if (! $context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

if ( $sc_clip->getOwnerUserId() != $USER->id ) {
    print_error('invalidaction', null, $return_channel);
}


// Perform action ?
if (    $action !== '' && $userid !== 0
        && confirm_sesskey() && has_capability('mod/switchcast:use', $context)
        && $switchcast->inviting
        && ( $USER->id == $sc_clip->getOwnerUserId() || has_capability('mod/switchcast:isproducer', $context) )
    ) {
    /*
     * $action AND $userid are defined
     * and channel allows clip inviting
     * and $USER has rights and is clip owner
     */

    if ($action == 'add') {
        $sc_clip->addMember($userid, $course->id, $switchcast->id);
    }
    else if ($action == 'remove') {
        $sc_clip->deleteMember($userid, $course->id, $switchcast->id);
    }

    add_to_log($course->id, 'switchcast', 'modify clip members', 'clip_members.php?id='.$id.'&clip_id='.$clip_ext_id, $sc_clip->getTitle().': '.$action.' user ID '.$userid, $cm->id);

    redirect($url);

}

else if ($action !== '') {
    print_error('actionnotallowed', null, $return_channel);
}

else {
    // no action to be performed
    add_to_log($course->id, 'switchcast', 'view clip members', 'clip_members.php?id='.$id.'&clip_id='.$clip_ext_id, $sc_clip->getTitle(), $cm->id);
}


// Display

$PAGE->set_title(format_string($switchcast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_switchcast');

echo html_writer::tag('h2', get_string('editmembers_long', 'switchcast'));

echo html_writer::start_tag('table', array('class' => 'switchcast-clips'));
$renderer->display_singleclip_table_header(false, true, $switchcast->userupload, false);
$renderer->display_clip_outline($sc_clip, false, false, null, true, $switchcast->userupload, false);
echo html_writer::end_tag('table');

$renderer->display_clip_members();
$renderer->display_user_selector(false, 'clip_members.php', get_string('addmember', 'switchcast'));

echo html_writer::link($return_channel, get_string('back_to_channel','switchcast'));

echo $OUTPUT->footer();
