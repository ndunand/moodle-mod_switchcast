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
require_once($CFG->dirroot.'/mod/switchcast/scast_clip.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_token.class.php');

$redirect_url = required_param('redirect', PARAM_URL);


if (isset($_REQUEST['enc_token'])) {

	$dec_token_arr  = scast_token::ext_auth_decode_encrypted_token($_REQUEST['enc_token']);

	$channel_id     = $dec_token_arr['channel_id'];
	$clip_id        = $dec_token_arr['clip_id'];
	$plain_token    = $dec_token_arr['plain_token'];

    if (! $switchcast = $DB->get_record('switchcast', array('ext_id' => $channel_id, 'id' => $SESSION->switchcastid))) {
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

    if (! $context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
        print_error('badcontext', null, $return_course);
    }

    require_course_login($course, false);

    $sc_obj  = new scast_obj();
    $sc_obj->doRead($switchcast->id);
    $sc_clip = new scast_clip($sc_obj, $clip_id);

    if ($sc_clip->checkPermissionBool('read') === true) {
        scast_token::ext_auth_redirect_to_vod_url($redirect_url, $plain_token);
        exit;
    }

}


// No token received, so display error

$url = new moodle_url('/mod/switchcast/checkAccess.php', array('redirect' => $redirect_url));

$PAGE->set_url($url);

require_course_login($course, false, $cm, true);

$PAGE->set_title(format_string($switchcast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

print_error('clip_no_access', 'switchcast', $return_course);

