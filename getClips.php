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

if (!isloggedin()) {
    $error = array('error' => get_string('loggedout', 'switchcast'));
    echo json_encode($error);
    exit;
}

require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_xml.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_user.class.php');
require_once($CFG->dirroot.'/mod/switchcast/lib.php');

$id             = required_param('id', PARAM_INT);
$filterstr      = optional_param('filterstr', '', PARAM_RAW_TRIMMED);
$sortkey        = optional_param('sortkey', 'sortablerecordingdate', PARAM_ALPHAEXT);
$sortdir        = optional_param('sortdir', 'asc', PARAM_ALPHA);
$offset         = optional_param('offset', 0, PARAM_INT);
if (!isset($SESSION->modswitchcast_clipsperpage)) {
    $SESSION->modswitchcast_clipsperpage = 10;
}
$length         = optional_param('length', $SESSION->modswitchcast_clipsperpage, PARAM_INT);
$SESSION->modswitchcast_clipsperpage = $length;

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

if (! $context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

$sc_obj = new scast_obj();
$sc_obj->doRead($switchcast->id, true);

$sc_user = new scast_user();

$arr_filter = array();
$filters = explode('&', urldecode($filterstr));
foreach ($filters as $filter) {
    $parts = explode('=', $filter);
    if (count($parts) == 2) {
        $arr_filter[$parts[0]] = $parts[1];
    }
}

$xml_clips = $sc_obj->getClips($arr_filter);
$xml_clips_access_allowed = $sc_obj->checkAccess($xml_clips);
$clips = array();
foreach ($xml_clips_access_allowed as $xml_clip) {
    $clips[] = (array)$xml_clip;
}

if (scast_obj::getValueByKey('display_select_columns')) {
    $xml_all_clips = $sc_obj->getClips();
    $xml_all_clips_access_allowed = $sc_obj->checkAccess($xml_all_clips);
    $all_clips = array();
    foreach ($xml_all_clips_access_allowed as $xml_all_clip) {
        $all_clips[] = (array)$xml_all_clip;
    }
}

$clip_objs = array();
foreach ($clips as $clip) {
    $scast_clip = new scast_clip($sc_obj, $clip['ext_id'], false, $switchcast->id);
    $title = $scast_clip->getTitle();
    if ($title == '') {
        $scast_clip->setTitle(get_string('untitled_clip', 'switchcast'));
    }
    $scast_clip->editdetails_page = '#switchcast-inactive';
//    $scast_clip->editclip_page = '#switchcast-inactive';
    $scast_clip->deleteclip_page = '#switchcast-inactive';
    $scast_clip->clipmembers_page = '#switchcast-inactive';
    if (has_capability('mod/switchcast:isproducer', $context)) {
        // current USER is channel producer in Moodle (i.e. Teacher)
        $scast_clip->editdetails_page = $CFG->wwwroot.'/mod/switchcast/clip_editdetails.php?id='.$cm->id.'&clip_ext_id='.$scast_clip->getExtId();
        if ($sc_obj->isProducer($sc_user->getExternalAccount())) {
            // current user is actual SwitchCast producer
            $scast_clip->deleteclip_page = $CFG->wwwroot.'/mod/switchcast/clip_delete.php?id='.$cm->id.'&clip_ext_id='.$scast_clip->getExtId();
        }
    }
    if ($scast_clip->getOwnerUserId() == $USER->id) {
        // current USER is clip owner
        if ($sc_obj->getIvt() && $sc_obj->getInvitingPossible()) {
            $scast_clip->clipmembers_page = $CFG->wwwroot.'/mod/switchcast/clip_members.php?id='.$cm->id.'&clip_ext_id='.$scast_clip->getExtId();
        }
    }
    $owner = $scast_clip->getOwner();
    unset($scast_clip->owner); // we don't want SWITCHaai uniqueID to appear in the JSON
    if ($owner == '') {
        $scast_clip->owner_name = '';
    }
    else {
        $owner_moodle_id = scast_user::getMoodleUserIdFromExtId($owner);
        if ($owner_moodle_user = $DB->get_record('user', array('id' => $owner_moodle_id))) {
            $scast_clip->owner_name = $owner_moodle_user->lastname.', '.$owner_moodle_user->firstname;
        }
        else {
            $scast_clip->owner_name = get_string('owner_not_in_moodle', 'switchcast');
        }
    }
//    if (!$scast_clip->AnnotationLink) {
//        // hack because if present it will fill our template
//        unset($scast_clip->AnnotationLink);
//    }
    $clip_objs[] = $scast_clip;
}

if (scast_obj::getValueByKey('display_select_columns')) {
    $all_clip_objs = array();
    foreach ($all_clips as $clip) {
        $scast_clip = new scast_clip($sc_obj, $clip['ext_id'], false, $switchcast->id);
        $scast_clip->editdetails_page = '#switchcast-inactive';
        $scast_clip->deleteclip_page = '#switchcast-inactive';
        $scast_clip->clipmembers_page = '#switchcast-inactive';
        if (has_capability('mod/switchcast:isproducer', $context)) {
            // current USER is channel producer in Moodle (i.e. Teacher)
            if ($sc_obj->getIvt()) {
                $scast_clip->editdetails_page = '#some-page';
            }
            if ($sc_obj->isProducer($sc_user->getExternalAccount())) {
                // current user is actual SwitchCast producer
                $scast_clip->deleteclip_page = '#some-page';
            }
        }
        if ($scast_clip->getOwnerUserId() == $USER->id) {
            // current USER is clip owner
            if ($sc_obj->getIvt() && $sc_obj->getInvitingPossible()) {
                $scast_clip->clipmembers_page = '#some-page';
            }
        }
        $owner = $scast_clip->getOwner();
        unset($scast_clip->owner); // we don't want SWITCHaai uniqueID to appear in the JSON
        if ($owner == '') {
            $scast_clip->owner_name = '';
        }
        else {
            $scast_clip->owner_name = 'SOME_NAME';
        }
        $all_clip_objs[] = $scast_clip;
    }
}

usort($clip_objs, 'switchcast_clip_sort');

if ($sortdir == 'desc') {
    $clip_objs = array_reverse($clip_objs);
}

$visible_clips = array_slice($clip_objs, $offset, $length);

$json = array(
    'count' => count($clip_objs),
    'clips' => $visible_clips
);

if (scast_obj::getValueByKey('display_select_columns')) {
    $json['allclips'] = $all_clip_objs;
}

echo json_encode($json);


function switchcast_clip_sort($a, $b) {
    global $sortkey;
    if ($a->$sortkey > $b->$sortkey) {
        return 1;
    }
    else {
        return -1;
    }
    return 0;
}

