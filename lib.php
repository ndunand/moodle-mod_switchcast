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


require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $switchcast Moodle {switchcast} table DB record
 * @return int newly created instance ID
 */
function switchcast_add_instance($switchcast) {
    global $DB, $USER;

    $switchcast->timemodified = time();

    $scast = new scast_obj();

    if (isset($switchcast->newchannelname)) {
        $scast->setChannelName($switchcast->newchannelname);
    }
    $scast->setChannelKind($switchcast->channeltype);
    //$scast->setCourseId();
    $scast->setDisciplineId($switchcast->disciplin);
    $scast->setLicense($switchcast->license);
    $scast->setEstimatedContentInHours($switchcast->contenthours);
    $scast->setLifetimeOfContentinMonth($switchcast->lifetime);
    $scast->setDepartment($switchcast->department);
    $scast->setAllowAnnotations($switchcast->annotations == SWITCHCAST_ANNOTATIONS);
    $scast->setTemplateId($switchcast->template_id);
    $scast->setIvt($switchcast->is_ivt);
    if (isset($switchcast->inviting)) {
        $scast->setInvitingPossible($switchcast->inviting);
    }
    $scast->setOrganizationDomain(scast_obj::getOrganizationByEmail($USER->email));
    $switchcast->organization_domain = $scast->getOrganization();

    if ($switchcast->channelnew == SWITCHCAST_CHANNEL_NEW) {
        // New channel
        $scast->setProducer(scast_user::getExtIdFromMoodleUserId($USER->id));
        $scast->doCreate();
        $switchcast->ext_id = $scast->getExtId();
    }
    else {
        // Existing channel
        $scast->setExtId($switchcast->ext_id);
        $scast->doUpdate();
    }

    if (empty($switchcast->timerestrict)) {
        $switchcast->timeopen = 0;
        $switchcast->timeclose = 0;
    }

    $switchcast->id = $DB->insert_record('switchcast', $switchcast);
    return $switchcast->id;
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $switchcast Moodle {switchcast} table DB record
 * @return bool true if everything went well
 */
function switchcast_update_instance($switchcast) {
    global $DB;

    $switchcast->id = $switchcast->instance;
    $switchcast->timemodified = time();

    $scast = new scast_obj();
    $scast->doRead($switchcast->id);

    $scast->setChannelKind($switchcast->channeltype);
    //$scast->setCourseId();
    $scast->setDisciplineId($switchcast->disciplin);
    $scast->setLicense($switchcast->license);
    $scast->setEstimatedContentInHours($switchcast->contenthours);
    $scast->setLifetimeOfContentinMonth($switchcast->lifetime);
    $scast->setDepartment($switchcast->department);
    $scast->setAllowAnnotations($switchcast->annotations == SWITCHCAST_ANNOTATIONS);
    $scast->setIvt($switchcast->is_ivt);
    if (!isset($switchcast->inviting) || $switchcast->is_ivt == false) {
        $switchcast->inviting = false;
    }
    $scast->setInvitingPossible($switchcast->inviting);

    // Existing channel
    $scast->setExtId($switchcast->ext_id);
    $scast_update = $scast->doUpdate();

    $switchcast->ext_id = $scast->getExtId();

    if (empty($switchcast->timerestrict)) {
        $switchcast->timeopen = 0;
        $switchcast->timeclose = 0;
    }

    $moodle_update = $DB->update_record('switchcast', $switchcast);

    return $scast_update && $moodle_update;
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id the ID of the {switchcast} DB record
 * @return bool true if succesful
 */
function switchcast_delete_instance($id) {
    global $DB;

    // make sure plugin instance exists
    if (! $switchcast = $DB->get_record('switchcast', array('id' => $id))) {
        return false;
    }

    // delete all clip members of this plugin instance
    if (! $DB->delete_records('switchcast_cmember', array('switchcastid' => $switchcast->id))) {
        return false;
    }

    // delete plugin instance itself
    if (! $DB->delete_records('switchcast', array('id' => $switchcast->id))) {
        return false;
    }

    return true;
}


/**
 * Gets a full switchcast record
 *
 * @param int $switchcastid the ID of the {switchcast} DB record
 * @return object|bool The {switchcast} DB record or false
 */
function switchcast_get_switchcast($switchcastid) {
    global $DB;

    if ($switchcast = $DB->get_record('switchcast', array('id' => $switchcastid))) {
        return $switchcast;
    }
    return false;
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the switchcast.
 *
 * @param object $mform form passed by reference
 */
function switchcast_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'switchcastheader', get_string('modulenameplural', 'switchcast'));
    $mform->addElement('advcheckbox', 'reset_switchcast', get_string('removeclipmembers','switchcast'));
}


/**
 * Course reset form defaults.
 *
 * @return array
 */
function switchcast_reset_course_form_defaults($course) {
    return array('reset_switchcast' => 1);
}


/**
 * Actual implementation of the reset course functionality, delete all the
 * switchcast clip members for course $data->courseid.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function switchcast_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'switchcast');
    $status = array();

    if (!empty($data->reset_switchcast)) {
        $DB->delete_records('switchcast_cmember', array('courseid' => $data->courseid));
        $status[] = array('component' => $componentstr, 'item' => get_string('removeclipmembers', 'switchcast'), 'error' => false);
    }

    // updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('switchcast', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);
    }

    return $status;
}


/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function switchcast_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_NO_VIEW_LINK:            return false;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $switchcastnode The node to add module settings to
 */
function switchcast_extend_settings_navigation(settings_navigation $settings, navigation_node $switchcastnode) {
    global $PAGE, $USER;

    // NOTE ND : forget it because no way to make this open in a new window
//    if (has_capability('mod/switchcast:isproducer', $PAGE->cm->context)) {
//        $sc_obj = new scast_obj();
//        $sc_obj->doRead($PAGE->cm->instance);
//        if ($sc_obj->isProducer(scast_user::getExtIdFromMoodleUserId($USER->id))) {
//            $switchcastnode->add(get_string('edit_at_switch', 'switchcast'), new moodle_url($sc_obj->getEditLink()), navigation_node::TYPE_SETTING);
//            $switchcastnode->add(get_string('upload_clip', 'switchcast'), new moodle_url($sc_obj->getUploadForm()), navigation_node::TYPE_SETTING);
//        }
//    }
}


/**
 * Obtains the automatic completion state for this switchcast based on any conditions
 * present in the settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
//function switchcast_get_completion_state($course, $cm, $userid, $type) {
//    global $CFG,$DB;
//
//    // Get switchcast details
//    $switchcast = $DB->get_record('switchcast', array('id'=>$cm->instance), '*', MUST_EXIST);
//
//    // If completion option is enabled, evaluate it and return true/false
//    if($switchcast->completionsubmit) {
//        $useranswer = switchcast_get_user_answer($switchcast, $userid);
//        return $useranswer !== false;
//    } else {
//        // Completion option is not enabled so just return $type
//        return $type;
//    }
//}


/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function switchcast_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-switchcast-*'=>get_string('page-mod-switchcast-x', 'switchcast'));
    return $module_pagetype;
}

