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

$url = new moodle_url('/mod/switchcast/clip_setowner.php', array('id' => $id, 'clip_ext_id' => $clip_ext_id));
$return_channel = new moodle_url('/mod/switchcast/view.php', array('id' => $id));

if ($userid > 0) {
//    $url->param('setowner', $userid);
    if (! $setuser = $DB->get_record('user', array('id' => $userid))) {
        print_error('invaliduser', null, $url);
    }
}

$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('switchcast', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', array('id' => $course->id));

require_course_login($course, false, $cm);

if (!$switchcast = switchcast_get_switchcast($cm->instance)) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (! $context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
    print_error('badcontext', null, $return_course);
}

$sc_obj  = new scast_obj();
$sc_obj->doRead($switchcast->id);
$sc_clip = new scast_clip($sc_obj, $clip_ext_id);


// Perform action ?
if (    in_array($action, array('add', 'remove'))
        && $userid !== 0
        && confirm_sesskey()
        && has_capability('mod/switchcast:isproducer', $context)
    ) {
    /*
     * $confirm
     * AND sesskey() ok
     * AND has producer rights
     */
    $sc_user = new scast_user(null, $userid);
    $newowner_aaiUniqueId = $sc_user->getExternalAccount();
    if ($action === 'add') {
        if ($newowner_aaiUniqueId) {
            $newowner = new scast_user($newowner_aaiUniqueId);
            $sc_obj->registerUser($newowner);
            $sc_clip->setOwner($newowner_aaiUniqueId);
            $sc_clip->doUpdate();
            add_to_log($course->id, 'switchcast', 'set clip owner', 'clip_setowner.php?id='.$id.'&clip_id='.$clip_ext_id, $sc_clip->getOwner());
        }
        else {
            print_error('owner_no_switch_account', 'switchcast', $url, $setuser->lastname.', '.$setuser->firstname);
        }
    }
    else if ($action === 'remove') {
        $sc_clip->setOwner('');
        $sc_clip->doUpdate();
        add_to_log($course->id, 'switchcast', 'remove clip owner', 'clip_setowner.php?id='.$id.'&clip_id='.$clip_ext_id, $sc_clip->getOwner());
    }
    redirect($url);
}


// Display

$PAGE->set_title(format_string($switchcast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_switchcast');

echo html_writer::tag('h2', get_string('set_clip_owner', 'switchcast'));
echo html_writer::start_tag('table', array('class' => 'switchcast-clips'));
$renderer->display_singleclip_table_header();
$renderer->display_clip_outline($sc_clip, false);
echo html_writer::end_tag('table');

if ( $USER->id == $sc_clip->getOwnerUserId() || has_capability('mod/switchcast:isproducer', $context) ) {
    $renderer->display_user_selector(true, 'clip_setowner.php', get_string('setnewowner', 'switchcast'), true);
    echo html_writer::start_tag('form', array('method' => 'post', 'action' => 'clip_setowner.php'));
    echo html_writer::input_hidden_params($url, array('action', 'userid'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'remove'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'userid', 'value' => '-1'));
    echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('removeowner', 'switchcast')));
    echo html_writer::end_tag('form');
}

$url = new moodle_url('/mod/switchcast/view.php', array('id' => $id));
echo html_writer::link($url, get_string('back_to_channel','switchcast'));

echo $OUTPUT->footer();

