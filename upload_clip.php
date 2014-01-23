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
require_once($CFG->dirroot.'/user/files_form.php');
require_once($CFG->dirroot.'/repository/lib.php');
require_once($CFG->dirroot.'/mod/switchcast/lib.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_clip.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_upload_form.class.php');

$id = required_param('id', PARAM_INT); // Course Module ID

$url = new moodle_url('/mod/switchcast/upload_clip.php', array('id' => $id));
$return_channel = new moodle_url('/mod/switchcast/view.php', array('id' => $id));

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

if (! $context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

$sc_obj  = new scast_obj();
$sc_obj->doRead($switchcast->id);


// Display

$PAGE->set_title(format_string($switchcast->name));
$PAGE->set_heading($course->fullname);

if ($switchcast->userupload) {
    $maxbytes = min(scast_obj::getValueByKey('userupload_maxfilesize'), $switchcast->userupload_maxfilesize);
}
else {
    $maxbytes = scast_obj::getValueByKey('userupload_maxfilesize');
}

$usercontext = context_user::instance($USER->id);

$data = new stdClass();
$data->returnurl = $return_channel;
$options = array(
    'subdirs' => 0,
    'maxbytes' => $maxbytes,
    'maxfiles' => 1,
    'accepted_types' => array('video'),
    'areamaxbytes' => $maxbytes
);
file_prepare_standard_filemanager($data, 'files', $options, $usercontext, 'mod_switchcast', 'userfiles', $id);

$mform = new scast_upload_form($url, array('data' => $data, 'options' => $options));

if ($mform->is_cancelled()) {
    redirect($return_channel);
}
else if ($formdata = $mform->get_data()) {
    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $usercontext, 'mod_switchcast', 'userfiles', $id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'mod_switchcast', 'userfiles', $id);
    foreach($files as $file) {
        $filesize = $file->get_filesize();
        if (!$filesize) {
            continue;
        }
        if ($file->get_mimetype() && substr($file->get_mimetype(), 0, 5) !== 'video') {
            $file->delete();
            print_error('fileis_notavideo', 'switchcast', $url, $file->get_mimetype());
        }

        $a_file = $file->copy_content_to_temp();
        $filename = $file->get_filename();
        $filetoupload = $CFG->dataroot.'/temp/files/mod_switchcast_'.md5(microtime()).'_'.$filename;
        rename($a_file, $filetoupload);

        list($upload_method, $upload_url, $upload_file_prefix) = $sc_obj->getUploadParams();
        try {
            $uploaded_filename = $upload_file_prefix.scast_xml::sendRequest($upload_url.$upload_file_prefix.basename($filetoupload), $upload_method, NULL, false, false, $filetoupload);
        }
        catch (Exception $e) {
            unlink($filetoupload);
            $retryurl = new moodle_url($url, array('formdata' => serialize($formdata)));
            print_error('userupload_error', 'switchcast', $retryurl);
//            $result = false;
//            continue;
        }
        $file->delete();
        $result = $sc_obj->createClip(array(
            'title' => $formdata->cliptitle,
            'subtitle' => $formdata->clipsubtitle,
            'presenter' => $formdata->clippresenter,
            'location' => $formdata->cliplocation,
            'ivt__owner' => scast_user::getExtIdFromMoodleUserId($USER->id),
            'uploaded_filename' => $uploaded_filename,
        ));
        unlink($filetoupload);
    }
}


if (isset($formdata) && isset($result)) {
    // data submitted: record file upload
    $uploaded_clip = new stdClass();
    $uploaded_clip->userid = $USER->id;
    $uploaded_clip->filename = $filename;
    $uploaded_clip->filesize = $filesize;
    $uploaded_clip->switchcastid = $switchcast->id;
    $uploaded_clip->timestamp = time();
    $uploaded_clip->title = $formdata->cliptitle;
    $uploaded_clip->subtitle = $formdata->clipsubtitle;
    $uploaded_clip->presenter = $formdata->clippresenter;
    $uploaded_clip->location = $formdata->cliplocation;
    if ($result !== false) {
        $uploaded_clip->ext_id = (string)$result->ext_id;
        $uploaded_clip->status = SWITCHCAST_CLIP_UPLOADED;
    }
    else {
        $uploaded_clip->status = SWITCHCAST_CLIP_TRYAGAIN;
    }
    if (!$DB->insert_record('switchcast_uploadedclip', $uploaded_clip)) {
        print_error('error');
    }
    redirect($url);
}
else {
    // no data submitted yet; display recap & form
    echo $OUTPUT->header();
    $renderer = $PAGE->get_renderer('mod_switchcast');
    echo html_writer::tag('h2', get_string('upload_clip', 'switchcast'));
    $renderer->display_user_pending_clips(true, true, false, false, false);
    // The following two set_context()'s are a dirty hack, but we have to do this,
    // otherwise the couse/site maxbytes limit is enforced.
    // (see MoodleQuickForm_filemanager class constructor)
    $PAGE->set_context($usercontext);
    if ($formdata = unserialize(optional_param('formdata', '', PARAM_RAW_TRIMMED))) {
        $mform->set_data($formdata);
    }
    $mform->display();
    $PAGE->set_context($context);
    echo html_writer::link($return_channel, get_string('back_to_channel','switchcast'));
    echo $OUTPUT->footer();
}

