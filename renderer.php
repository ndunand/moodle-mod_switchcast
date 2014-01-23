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

defined('MOODLE_INTERNAL') || die();

define ('DISPLAY_HORIZONTAL_LAYOUT', 0);
define ('DISPLAY_VERTICAL_LAYOUT', 1);

require_once($CFG->dirroot.'/mod/switchcast/scast_clip.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_user.class.php');


class mod_switchcast_renderer extends plugin_renderer_base {


    protected $displayed_userids;


    /**
     * Constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        global $switchcast, $SESSION;
        $SESSION->switchcastid = $switchcast->id;
        $this->scobj = new scast_obj();
        $this->scobj->doRead($switchcast->id);
        $this->scuser = new scast_user();
        $this->displayed_userids = array();
        parent::__construct($page, $target);
    }


    /**
     * Displays channel header + content
     *
     */
    function display_channel_content() {

        global $context, $PAGE;
        $PAGE->requires->js('/mod/switchcast/js/pure.js');
        $PAGE->requires->js('/mod/switchcast/js/get_clips.js');

        /*
         * Register the User as SwitchCast producer if necessary,
         * and remove him from the producers list if needed.
         */
        if ($this->scuser->getExternalAccount()) {
            if (has_capability('mod/switchcast:isproducer', $context)) {
                // add as producer, if not already
                if (!$this->scobj->isProducer($this->scuser->getExternalAccount())) {
                    $this->scobj->registerUser($this->scuser);
                    $this->scobj->addProducer($this->scuser->getExternalAccount());
                }
            }
            else {
                // remove from producers
                if($this->scobj->isProducer($this->scuser->getExternalAccount())) {
                    $this->scobj->removeProducer($this->scuser->getExternalAccount());
                }
            }
        }

        $this->display_user_pending_clips(false, true);
        $this->display_channel_outline();

        $nonverified_clips = $this->scobj->getClips();
        if (!count($nonverified_clips)) {
            print_string('noclipsinchannel', 'switchcast');
            return;
        }
        $this->clips = $this->scobj->checkAccess($nonverified_clips);

        if (count($this->clips)) {
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'table-params'));
            echo html_writer::start_tag('table', array('class' => 'switchcast-clips-table switchcast-clips', 'id' => 'switchcast-clips-table'));
            echo html_writer::start_tag('tr');
            $title_th = html_writer::tag('a', get_string('cliptitle', 'switchcast'), array('href' => '#'));
            $title_th .= html_writer::empty_tag('br');
            $title_th .= html_writer::empty_tag('input', array('type' => 'checkbox', 'id' => 'clip-show-subtitle'));
            $title_th .= html_writer::tag('label', get_string('showsubtitles', 'switchcast'), array('for' => 'clip-show-subtitle'));
            echo html_writer::tag('th', $title_th, array('class' => 'switchcast-sortable', 'data-sortkey' => 'title'));
            echo html_writer::tag('th', html_writer::tag('a', get_string('presenter', 'switchcast'), array('href' => '#')), array('class' => 'switchcast-presenter switchcast-sortable', 'data-sortkey' => 'presenter'));
            echo html_writer::tag('th', html_writer::tag('a', get_string('location', 'switchcast'), array('href' => '#')), array('class' => 'switchcast-location switchcast-sortable', 'data-sortkey' => 'location'));
            echo html_writer::tag('th', html_writer::tag('a', get_string('recordingstation', 'switchcast'), array('href' => '#')), array('class' => 'switchcast-recordingstation switchcast-sortable', 'data-sortkey' => 'recordingstation'));
            echo html_writer::tag('th', get_string('date', 'switchcast'));
            echo html_writer::tag('th', html_writer::tag('a', get_string('owner', 'switchcast'), array('href' => '#')), array('class' => 'switchcast-owner switchcast-sortable', 'data-sortkey' => 'owner_name'));
            echo html_writer::tag('th', get_string('actions', 'switchcast'), array('class' => 'switchcast-actions'));
            echo html_writer::end_tag('tr');
            foreach($this->clips as $clip) {
                $sc_clip = new scast_clip($this->scobj, (string)$clip->ext_id);
                $this->display_clip_outline($sc_clip, true, true);
                break;
                // NOTE ND : we display only one row, that we'll use as a template
                // TODO : it's ugly, there must be a better way
            }
            echo html_writer::end_tag('table');
            echo html_writer::tag('div', '', array('class' => 'loading'));
        }
        else {
            print_string('novisibleclipsinchannel', 'switchcast');
        }

    }


    /**
     * Display a SWITCHcast channel activity's header
     *
     */
    function display_channel_outline() {
        global $CFG, $OUTPUT, $switchcast, $cm, $context, $SESSION;

        if (has_capability('mod/switchcast:isproducer', $context) || ($switchcast->userupload && has_capability('mod/switchcast:uploadclip', $context))) {
            echo html_writer::tag('a', get_string('upload_clip', 'switchcast'), array('href' => $CFG->wwwroot.'/mod/switchcast/upload_clip.php?id='.$cm->id, 'class' => 'upload button'));
        }
        if (has_capability('mod/switchcast:isproducer', $context)) {
            echo html_writer::tag('a', get_string('view_useruploads', 'switchcast'), array('href' => $CFG->wwwroot.'/mod/switchcast/uploads.php?id='.$cm->id, 'class' => 'upload button'));
        }
        if ($this->scobj->isProducer($this->scuser->getExternalAccount())) {
//            echo html_writer::tag('a', get_string('upload_clip', 'switchcast'), array('href' => $this->scobj->getUploadForm(), 'class' => 'upload button', 'target' => '_blank'));
            echo html_writer::tag('a', get_string('edit_at_switch', 'switchcast'), array('href' => $this->scobj->getEditLink(), 'class' => 'editchannel button', 'target' => '_blank'));
            if ($this->scobj->hasReferencedChannels() > 1) {
                echo html_writer::tag('div', get_string('channel_several_refs', 'switchcast'), array('class' => 'switchcast-notice'));
            }
        }

        echo html_writer::tag('h2', $switchcast->name);

        if ($switchcast->intro) {
            echo $OUTPUT->box(format_module_intro('switchcast', $switchcast, $cm->id), 'generalbox', 'intro');
        }

        echo html_writer::tag('a', get_string('filters', 'switchcast'), array('href' => '#', 'class' => 'switchcast-filters-toggle'));

        echo html_writer::start_tag('div', array('class' => 'switchcast-pagination'));
        echo html_writer::tag('input', '', array('type' => 'hidden', 'id' => 'switchcast-cmid-hidden-input', 'value' => $cm->id));
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-perpage'));
        echo html_writer::tag('span', get_string('itemsperpage', 'switchcast'));
        $perpage_values = array(5, 10, 20, 50, 100);
        $perpage_options = array_combine($perpage_values, $perpage_values);
        $perpage_option_selected = isset($SESSION->modswitchcast_clipsperpage) ? ($SESSION->modswitchcast_clipsperpage) : (10);
        echo html_writer::select($perpage_options, 'switchcast-perpage', $perpage_option_selected);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-pageno'));
        echo html_writer::tag('span', get_string('pageno', 'switchcast'));
        $pages = array(1);
        $pages_options = array_combine($pages, $pages);
        echo html_writer::select($pages_options, 'switchcast-pageno', '1');
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-pagination'));
        echo html_writer::tag('span', get_string('pagination', 'switchcast'));
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');

        echo html_writer::start_tag('div', array('class' => 'switchcast-filters'));
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-title'));
        echo html_writer::tag('span', get_string('title', 'switchcast'));
        echo html_writer::empty_tag('input', array('type' => 'text', 'name' => 'switchcast-title'));
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-presenter'));
        echo html_writer::tag('span', get_string('presenter', 'switchcast'));
        echo html_writer::empty_tag('input', array('type' => 'text', 'name' => 'switchcast-presenter'));
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-location switchcast-location'));
        echo html_writer::tag('span', get_string('location', 'switchcast'));
        echo html_writer::empty_tag('input', array('type' => 'text', 'name' => 'switchcast-location'));
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-recordingstation switchcast-recordingstation'));
        echo html_writer::tag('span', get_string('recordingstation', 'switchcast'));
        echo html_writer::empty_tag('input', array('type' => 'text', 'name' => 'switchcast-recordingstation'));
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-owner switchcast-owner'));
        echo html_writer::tag('span', get_string('owner', 'switchcast'));
        $owners_records = get_users_by_capability($context, 'mod/switchcast:use', 'u.id, u.firstname, u.lastname', 'u.lastname, u.firstname');
        $owners_options = array();
        foreach ($owners_records as $owner_record) {
            if ($aaiuniqueid = scast_user::getExtIdFromMoodleUserId($owner_record->id)) {
                $owners_options[$aaiuniqueid] = $owner_record->lastname.', '.$owner_record->firstname;
            }
        }
        echo html_writer::select($owners_options, 'switchcast-owner');
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'ajax-controls-withoutowner switchcast-owner'));
        echo html_writer::tag('span', get_string('withoutowner', 'switchcast'));
        echo html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'switchcast-withoutowner'));
        echo html_writer::end_tag('div');

        echo html_writer::start_tag('div');
        echo html_writer::tag('span', '&nbsp;');
        echo html_writer::tag('button', get_string('resetfilters', 'switchcast'), array('class' => 'cancel'));
        echo html_writer::tag('span', '&nbsp;');
        echo html_writer::tag('button', get_string('ok'), array('class' => 'ok'));
        echo html_writer::end_tag('div');

        echo html_writer::end_tag('div');

        echo html_writer::tag('div', '', array('class' => 'clearer'));

    }


    /**
     * Displays a header for singleclip display
     *
     * @param bool $withactions show actions column
     * @param bool $with_owner
     * @param bool $with_uploader
     * @param bool $with_recordingstation
     */
    function display_singleclip_table_header($withactions = false, $with_owner = true, $with_uploader = false, $with_recordingstation = true) {

        echo html_writer::start_tag('tr');

        echo html_writer::tag('th', get_string('cliptitle', 'switchcast'));
        echo html_writer::tag('th', get_string('presenter', 'switchcast'), array('class' => 'switchcast-presenter'));
        echo html_writer::tag('th', get_string('location', 'switchcast'), array('class' => 'switchcast-location'));
        if ($with_recordingstation) {
            echo html_writer::tag('th', get_string('recording_station', 'switchcast'), array('class' => 'switchcast-recordingstation'));
        }
        echo html_writer::tag('th', get_string('date', 'switchcast'), array('class' => 'switchcast-recordingdate'));
        if ($with_owner) {
            echo html_writer::tag('th', get_string('owner', 'switchcast'), array('class' => 'switchcast-owner'));
        }
        if ($with_uploader) {
            echo html_writer::tag('th', get_string('uploader', 'switchcast'), array('class' => 'switchcast-owner'));
        }
        if ($withactions) {
            echo html_writer::tag('th', get_string('actions', 'switchcast'), array('class' => 'switchcast-actions'));
        }

        echo html_writer::end_tag('tr');
    }


    /**
     * Displays a clip outline in a table row
     *
     * @param scast_clip $sc_clip a SWITCHcast clip object
     * @param bool $with_actions display action buttons
     * @param bool $is_template use row as template
     * @param string $allowed_actions comma separated list of allowed actions, used if $with_actions is true
     * @param bool $with_owner display owner column even if not is_ivt()
     * @param bool $with_uploader display uploader column
     * @param bool $with_recordingstation
     */
    function display_clip_outline(scast_clip $sc_clip, $with_actions = true, $is_template = false, $allowed_actions = 'all', $with_owner = false, $with_uploader = false, $with_recordingstation = true) {
        global $CFG, $DB, $cm;

        $title = $sc_clip->getTitle();
        if ($title == '') {
            $title = get_string('untitled_clip', 'switchcast');
        }
        $subtitle = $sc_clip->getSubtitle();
        $title  = html_writer::tag('span', $title, array('class' => 'title'));
        $title .= html_writer::tag('div', $subtitle, array('class' => 'subtitle'));

        $owner = $sc_clip->getOwner();
        if ($owner == '') {
            $owner = get_string('no_owner', 'switchcast');
        }
        else {
            $owner_moodle_id = scast_user::getMoodleUserIdFromExtId($owner);
            if ($owner_moodle_user = $DB->get_record('user', array('id' => $owner_moodle_id))) {
                $owner = $owner_moodle_user->lastname.', '.$owner_moodle_user->firstname;
            }
            else {
                $owner = get_string('owner_not_in_moodle', 'switchcast');
            }
        }

        $uploader = '';
        if ($with_uploader) {
            $uploaded_clip = $DB->get_record('switchcast_uploadedclip', array('ext_id' => $sc_clip->getExtId()));
            if ($uploaded_clip) {
                if ($uploader_moodle_user = $DB->get_record('user', array('id' => $uploaded_clip->userid))) {
                    $uploader = $uploader_moodle_user->lastname.', '.$uploader_moodle_user->firstname;
                }
            }
        }

        if ($is_template) {
            $extraclass = ($this->scobj->getIvt()) ? ('with-owner') : ('without-owner');
            echo html_writer::start_tag('tr', array('class' => 'switchcast-clip-template-row '.$extraclass));
        }
        else {
            echo html_writer::start_tag('tr');
        }

        echo html_writer::start_tag('td');
        echo html_writer::start_tag('div', array('class' => 'cliplabel', 'title' => $subtitle));
        echo html_writer::empty_tag('img', array('src' => $sc_clip->getCover()));
        echo html_writer::tag('h3', $title);
        echo html_writer::start_tag('div', array('class' => 'linkbar'));
//        echo html_writer::tag('span', $sc_clip->getLinkBox());
        if ($is_template) {
            echo html_writer::tag('a', '', array('href' => '#switchcast-inactive', 'title' => get_string('annotations', 'switchcast'), 'class' => 'annotate', 'target' => '_blank'));
        }
        else if ($this->scobj->getAllowAnnotations()) {
            echo html_writer::tag('a', '', array('href' => $sc_clip->getAnnotationLink(), 'title' => get_string('annotations', 'switchcast'), 'class' => 'annotate', 'target' => '_blank'));
        }
        echo html_writer::tag('a', '', array('href' => $sc_clip->getLinkFlash(), 'title' => get_string('flash', 'switchcast'), 'class' => 'flash', 'target' => '_blank'));
//        echo html_writer::tag('span', $sc_clip->getLinkMp4());
        echo html_writer::tag('a', '', array('href' =>$sc_clip->getLinkMov(), 'title' => get_string('mov', 'switchcast'), 'class' => 'mov', 'target' => '_blank'));
        echo html_writer::tag('a', '', array('href' => $sc_clip->getLinkM4v(), 'title' => get_string('m4v', 'switchcast'), 'class' => 'm4v', 'target' => '_blank'));
//        echo html_writer::tag('span', $sc_clip->getSubtitle());
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('td');

        echo html_writer::start_tag('td', array('class' => 'switchcast-presenter'));
        echo html_writer::tag('span', $sc_clip->getPresenter());
        echo html_writer::end_tag('td');

        echo html_writer::start_tag('td', array('class' => 'switchcast-location'));
        echo html_writer::tag('span', $sc_clip->getLocation());
        echo html_writer::end_tag('td');

        if ($with_recordingstation) {
            echo html_writer::start_tag('td', array('class' => 'switchcast-recordingstation'));
            echo html_writer::tag('span', $sc_clip->getRecordingStation());
            echo html_writer::end_tag('td');
        }

        echo html_writer::start_tag('td', array('class' => 'switchcast-recordingdate'));
        echo html_writer::tag('span', $sc_clip->getRecordingDate());
        echo html_writer::end_tag('td');

        if (($this->scobj->getIvt() || $is_template || $with_owner) && ! $with_uploader) {
            echo html_writer::start_tag('td', array('class' => 'switchcast-owner'));
            echo html_writer::tag('span', $owner);
            echo html_writer::end_tag('td');
        }

        if ($with_uploader) {
            echo html_writer::start_tag('td', array('class' => 'switchcast-uploader'));
            echo html_writer::tag('span', $uploader);
            echo html_writer::end_tag('td');
        }

        $allowed_actions = explode(',', $allowed_actions);
        if ($with_actions && count($allowed_actions)) {
            echo html_writer::start_tag('td', array('class' => 'switchcast-actions'));
            echo html_writer::start_tag('div', array('class' => 'switchcast-hidden-actions'));
            if (in_array('editdetails', $allowed_actions) || in_array('all', $allowed_actions)) {
                echo html_writer::tag('a', get_string('editdetails', 'switchcast'), array('href' => $CFG->wwwroot.'/mod/switchcast/clip_editdetails.php?id='.$cm->id.'&clip_ext_id='.$sc_clip->getExtId(), 'class' => 'button switchcast-editdetails'));
            }
            if (in_array('invite', $allowed_actions) || in_array('all', $allowed_actions)) {
                echo html_writer::tag('a', get_string('editmembers', 'switchcast'), array('href' => $CFG->wwwroot.'/mod/switchcast/clip_members.php?id='.$cm->id.'&clip_ext_id='.$sc_clip->getExtId(), 'class' => 'button switchcast-clipmembers'));
            }
            if (in_array('delete', $allowed_actions) || in_array('all', $allowed_actions)) {
                echo html_writer::tag('a', get_string('delete_clip', 'switchcast'), array('href' => $CFG->wwwroot.'/mod/switchcast/clip_delete.php?id='.$cm->id.'&clip_ext_id='.$sc_clip->getExtId(), 'class' => 'button switchcast-deleteclip'));
            }
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('td');
        }

        echo html_writer::end_tag('tr');

    }


    /**
     * Displays user outlines of each channel teacher (for the clip members table)
     *
     */
    function display_channel_teachers() {
        global $context;
        $teachers = get_users_by_capability($context, 'mod/switchcast:seeallclips', 'u.id');
        foreach ($teachers as $teacher) {
            $this->display_user_outline($teacher->id, false, true);
        }
    }


    /**
     * Displays user outlines of the clip owner (for the clip members table)
     *
     */
    function display_clip_owner() {
        global $sc_clip;
        $owner_moodle_id = scast_user::getMoodleUserIdFromExtId($sc_clip->getOwner());
        if ($owner_moodle_id) {
            $this->display_user_outline($owner_moodle_id, false, false, false);
        }
    }


    /**
     * Displays user outlines of the clip uploader (for the clip members table)
     *
     */
    function display_clip_uploader() {
        global $sc_clip, $DB;
        $record = $DB->get_record('switchcast_uploadedclip', array('ext_id' => $sc_clip->getExtId()));
        if ($record) {
            $this->display_user_outline($record->userid, false, false, false, false, true);
        }
    }


    /**
     * Displays user outlines of each group member (for the clip members table)
     *
     */
    function display_group_members() {
        global $sc_obj, $sc_clip, $cm, $context;
        if (    groups_get_activity_groupmode($cm) == NOGROUPS
                || $sc_obj->getIvt() == false
                || $sc_clip->getOwner() == false
                ) {
            return;
        }
        $users = get_users_by_capability($context, 'mod/switchcast:use', 'u.id');
        foreach ($users as $userid => $user) {
            if (scast_user::checkSameGroup(scast_user::getMoodleUserIdFromExtId($sc_clip->getOwner()), $userid)) {
                $this->display_user_outline($userid, false, false, false, true);
            }
        }
    }


    /**
     * Displays a list of a user's pending and uploaded clips
     *
     * @param bool $show_uploaded
     * @param bool $show_pending
     * @param bool $allusers
     * @param bool $with_uploader display uploader instead of owner
     */
    function display_user_pending_clips($show_uploaded = true, $show_pending = true, $allusers = false, $with_uploader = false) {
        global $DB, $switchcast, $USER, $context;

        scast_obj::processUploadedClips();
        $isproducer = has_capability('mod/switchcast:isproducer', $context);

        if ($allusers && $isproducer) {
            // display for all users
            $uploaded_title = 'uploadedclips';
            $pending_title = 'pendingclips';
            $records = $DB->get_records('switchcast_uploadedclip', array('switchcastid' => $switchcast->id));
        }
        else {
            // display for current user
            $uploaded_title = 'myuploadedclips';
            $pending_title = 'mypendingclips';
            $records = $DB->get_records('switchcast_uploadedclip', array('userid' => $USER->id, 'switchcastid' => $switchcast->id));
        }

        $sc_obj = new scast_obj();
        $sc_obj->doRead($switchcast->id);
        $pending = array();
        $uploaded = array();
        foreach ($records as $record) {
            if ($record->status == SWITCHCAST_CLIP_READY) {
                // encoding finished
                $uploaded[] = $record;
            }
            else if ($record->status == SWITCHCAST_CLIP_UPLOADED) {
                // encoding in progress
                $pending[] = $record;
            }
        }
        // display clips uploaded by this user:
        if ($show_uploaded && count($uploaded)) {
            echo html_writer::tag('h3', get_string($uploaded_title, 'switchcast', count($uploaded)));
            echo html_writer::start_tag('table', array('class' => 'switchcast-clips'));
            $this->display_singleclip_table_header(false, !$with_uploader, $with_uploader, false);
            foreach ($uploaded as $uploaded_record) {
                $sc_clip = new scast_clip($sc_obj, $uploaded_record->ext_id);
                $this->display_clip_outline($sc_clip, false, false, null, !$with_uploader, $with_uploader, false);
            }
            echo html_writer::end_tag('table');
        }
        // display this user's pending clips (uploaded but not yet available):
        if ($show_pending && count($pending)) {
            echo html_writer::tag('h3', get_string($pending_title, 'switchcast', count($pending)));
            echo html_writer::start_tag('table', array('class' => 'switchcast-clips'));
            $this->display_singleclip_table_header(false, !$with_uploader, $with_uploader, false);
            foreach ($pending as $pending_record) {
                try {
                    $sc_clip = new scast_clip($sc_obj, $pending_record->ext_id);
                }
                catch (Exception $e) {
                    if ($e->errorcode == 'xml_fail' && $e->module == 'switchcast' && preg_match('/not found/', $e->a)) {
                        $DB->delete_records('switchcast_uploadedclip', array('id' => $pending_record->id));
                    }
                    continue;
                }
                $this->display_clip_outline($sc_clip, false, false, null, !$with_uploader, $with_uploader, false);
            }
            echo html_writer::end_tag('table');
        }
        if ($allusers && !count($records)) {
            echo html_writer::tag('p', get_string('nouploadedclips', 'switchcast'));
        }
    }


    /**
     * Displays user details in a table row (for the clip members page)
     *
     * @param int $userid Moodle user ID
     * @param bool $isdeleteable whether user is removeable (button shown)
     * @param bool $isteacher
     * @param bool $isowner
     * @param bool $isgroupmember
     * @param bool $isuploader
     * @return bool true if user was displayed
     */
    function display_user_outline($userid, $isdeleteable = false, $isteacher = false, $isowner = false, $isgroupmember = false, $isuploader = false) {
        global $course, $cm, $OUTPUT, $DB, $context, $url;

        if (in_array($userid, $this->displayed_userids)) {
            return;
        }

        $user = $DB->get_record('user', array('id' => $userid));
        if ($user === false) {
            return;
        }

        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('td');
        // Note ND : output logic copied from user/index.php
        echo $OUTPUT->user_picture($user, array('size' => 50, 'courseid' => $course->id));
        $email = '';
        if (    $user->maildisplay == 1
                or ($user->maildisplay == 2 and ($course->id != SITEID) and !isguestuser())
                or has_capability('moodle/course:viewhiddenuserfields', $context)
            ) {
            $email = ' '.$user->email;
        }
        echo html_writer::tag('div', $user->lastname.', '.$user->firstname);
        echo html_writer::end_tag('td');
        echo html_writer::tag('td', $email);
        if ($isteacher === true) {
            echo html_writer::tag('td', get_string('channel_teacher', 'switchcast'));
        }
        else if ($isowner === true) {
            echo html_writer::tag('td', get_string('clip_owner', 'switchcast'));
        }
        else if ($isgroupmember === true) {
            echo html_writer::tag('td', get_string('group_member', 'switchcast'));
        }
        else if ($isuploader === true) {
            echo html_writer::tag('td', get_string('clip_uploader', 'switchcast'));
        }
        else {
            echo html_writer::tag('td', get_string('clip_member', 'switchcast'));
        }
        echo html_writer::start_tag('td');
        if ($isdeleteable === true) { // the user is an invited member
            echo html_writer::start_tag('form', array('method' => 'post', 'action' => 'clip_members.php', 'onsubmit' => 'return confirm(\''.  get_string('confirm_removeuser', 'switchcast').'\');'));
            echo html_writer::input_hidden_params($url, array('action', 'userid'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'remove'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'userid', 'value' => $user->id));
            echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('remove')));
            echo html_writer::end_tag('form');
        }
        echo html_writer::end_tag('td');
        echo html_writer::end_tag('tr');
        $this->displayed_userids[] = $userid;
        return true;
    }


    /**
     * Displays a list of the invited members of a SWITCHcast clip
     *
     */
    function display_clip_members() {
        global $sc_clip;
        echo html_writer::start_tag('table', array('class' => 'switchcast-clips switchcast-clips-members'));
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', get_string('name'));
        echo html_writer::tag('th', get_string('email'));
        echo html_writer::tag('th', get_string('context', 'switchcast'));
        echo html_writer::tag('th', get_string('actions'));
        echo html_writer::end_tag('tr');
        $this->display_channel_teachers();
        $this->display_clip_owner();
        $this->display_clip_uploader();
        $this->display_group_members();
        $members = $sc_clip->getMembers();
        foreach ($members as $member) {
            $this->display_user_outline($member, true, false);
        }
        echo html_writer::end_tag('table');
    }


    /**
     * Displays a user selector
     *
     * @param bool $withproducers shall the producers be included ?
     * @param string $action_url where the form shall be posted
     * @param string $buttonlabel value attribute of the submit button
     * @param bool $switchaaionly display users with ExternalAccount only
     * @param bool $with_emtpyoption display 'remove user' option or not
     * @param bool $selectonly display HTML SELECT element only
     * @param int $selected_id if not zero, select OPTION with this index
     */
    function display_user_selector($withproducers = false, $action_url = '', $buttonlabel = 'OK', $switchaaionly = false, $with_emtpyoption = false, $selectonly = false, $selected_id = 0) {
        global $context, $url, $course;
        if ($withproducers === false) {
            $producers = get_users_by_capability($context, 'mod/switchcast:isproducer', 'u.id');
        }
        $possible_users = get_users_by_capability($context, 'mod/switchcast:use', 'u.id, u.lastname, u.firstname, u.maildisplay, u.email', 'u.lastname, u.firstname');
        $options = array();
        if ($with_emtpyoption) {
            $options[-1] = '('.get_string('removeowner', 'switchcast').')';
        }
        foreach ($possible_users as $possible_user_id => $possible_user) {
            if (in_array($possible_user_id, $this->displayed_userids)) {
                continue;
            }
            if ($withproducers === false && array_key_exists($possible_user_id, $producers)) {
                continue;
            }
            if ($switchaaionly && !scast_user::getExtIdFromMoodleUserId($possible_user_id)) {
                continue;
            }
            $option_text = $possible_user->lastname . ', ' . $possible_user->firstname;
            if (
                    $possible_user->maildisplay == 1
                    or ($possible_user->maildisplay == 2 and ($course->id != SITEID) and !isguestuser())
                    or has_capability('moodle/course:viewhiddenuserfields', $context)
                ) {
                $option_text .= ' (' . $possible_user->email . ')';
            }
            $options[$possible_user_id] = $option_text;
        }
        if (count($options)) {
            if (!$selectonly) {
                echo html_writer::start_tag('form', array('method' => 'post', 'action' => $action_url, 'onsubmit' => 'return document.getElementById(\'menuuserid\').selectedIndex != 0;'));
                echo html_writer::input_hidden_params($url, array('action', 'userid'));
                echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
                echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'add'));
            }
            echo html_writer::select($options, 'userid', $selected_id);
            if (!$selectonly) {
                echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => $buttonlabel));
                echo html_writer::end_tag('form');
            }
        }
        else {
            if (!$selectonly) {
                echo html_writer::start_tag('form');
            }
            echo html_writer::select($options, 'userid', null, null, array('disabled' => 'disabled'));
            if (!$selectonly) {
                echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => $buttonlabel, 'disabled' => 'disabled'));
                echo html_writer::tag('div', get_string('nomoreusers', 'switchcast'));
                echo html_writer::end_tag('form');
            }
        }
    }


}

