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

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_user.class.php');

class mod_switchcast_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $PAGE;
        
        $mform    =& $this->_form;

        // some checks, before going any further
        $scuser = new scast_user();
        if (empty($this->_instance) && $scuser->getExternalAccount() == '') {
            // $USER has no SWITCHaai account and is attempting to create a new activity instance:
            // he cannot create a channel nor link to an existing channel (because he doesn't own
            // any, as he doesn't exist in SwitchCast). Therefore, we prevent him from going any further.
            print_error('user_notaai', 'switchcast', new moodle_url('/course/view.php', array('id' => (int)$this->current->course)));
        }
        else if (empty($this->_instance) && !in_array(scast_obj::getOrganizationByEmail($scuser->getExternalAccount()), scast_obj::getEnabledOrgnanizations())) {
            // $USER has a SWITCHaai account, but we don't have a sys_account for his HomeOrganization.
            // Therefore, we prevent him from going any further.
            print_error('user_homeorgnotenabled', 'switchcast', new moodle_url('/course/view.php', array('id' => (int)$this->current->course)), scast_obj::getOrganizationByEmail($scuser->getExternalAccount()));
        }

        if (!empty($this->_instance) && !in_array($this->current->organization_domain, scast_obj::getEnabledOrgnanizations())) {
            print_error('badorganization', 'switchcast', new moodle_url('/course/view.php', array('id' => (int)$this->current->course)));
        }

        if ($scuser->getExternalAccount() != '') {
            // $USER has a SWITCHaai account, so register him at SwitchCast to make sure it exists there
            scast_obj::registerUser($scuser);
        }
        
        // have we got a sys_account for the channel?
        $sysaccount = false;
        if ( !empty($this->_instance) && in_array($this->current->organization_domain, scast_obj::getEnabledOrgnanizations()) ) {
            $sysaccount_extid = scast_obj::getSysAccountByOrganization($this->current->organization_domain);
            $sysaccount = new scast_user($sysaccount_extid);
        }

        if ($CFG->version >= 2013051400)
            // Moodle 2.5 or later
            $PAGE->requires->jquery();
        else {
            // earlier Moodle versions
            $PAGE->requires->js('/mod/switchcast/js/jquery-1.9.1.min.js');
        }
        $PAGE->requires->js('/mod/switchcast/js/existing_channel.js');

        // General settings :
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('switchcastname', 'switchcast'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(true, get_string('chatintro', 'chat'));

        // Miscellaneous settings :
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));

        $mform->addElement('select', 'channelnew', get_string('channel', 'switchcast'), array(
            SWITCHCAST_CHANNEL_NEW      => get_string('channelnew', 'switchcast'),
            SWITCHCAST_CHANNEL_EXISTING => get_string('channelexisting', 'switchcast')
        ));
        if (empty($this->_instance)) {
            $mform->setDefault('channelnew', SWITCHCAST_CHANNEL_NEW);
        }
        else {
            $mform->setDefault('channelnew', SWITCHCAST_CHANNEL_EXISTING);
        }

        $channeltypes = array();
        if (scast_obj::getValueByKey('allow_test_channels')) {
            $channeltypes[SWITCHCAST_CHANNEL_TEST] = get_string('channeltest', 'switchcast');
        }
        if (scast_obj::getValueByKey('allow_prod_channels')) {
            $channeltypes[SWITCHCAST_CHANNEL_PROD] = get_string('channelprod', 'switchcast');
        }
        if (!count($channeltypes)) {
            print_error('misconfiguration', 'switchcast');
        }
        $mform->addElement('select', 'channeltype', get_string('channeltype', 'switchcast'), $channeltypes);
        $mform->setDefault('channeltype', SWITCHCAST_CHANNEL_TEST);
        $mform->disabledIf('channeltype', 'channelnew', 'eq', SWITCHCAST_CHANNEL_EXISTING);

        if (empty($this->_instance)) {
            if ($scuser->getExternalAccount() != '') {
                // USER has a SWITCHaai account -> get his channels
                $userchannels = $scuser->getChannels();
            }
            else {
                $userchannels = array();
            }
        }
        else {
            if ($sysaccount !== false) {
                // We've got a sys_account for this instance's organization -> use it to get the channels list
                $userchannels = $sysaccount->getChannels();
            }
            else {
                // No sys_account for this instance's organization -> no channels list can be displayed
                $userchannels = array();
            }
        }

        $channels = array();
        foreach ($userchannels->channel as $userchannel) {
            $channels[(string)$userchannel->ext_id] = (string)$userchannel->name;
        }
        $mform->addElement('select', 'ext_id', get_string('channelchoose', 'switchcast'), $channels);
        $mform->disabledIf('ext_id', 'channelnew', 'eq', SWITCHCAST_CHANNEL_NEW);

        $mform->addElement('text', 'newchannelname', get_string('newchannelname', 'switchcast'));
        $mform->disabledIf('newchannelname', 'channelnew', 'eq', SWITCHCAST_CHANNEL_EXISTING);
        $mform->setType('newchannelname', PARAM_TEXT);

        if (!empty($this->_instance)) {
            $mform->freeze('channelnew,channeltype');
            $mform->removeElement('newchannelname');
        }

        $scast = new scast_obj();

        $scast_disciplins = $scast->getAllDisciplines();
        $mform->addElement('select', 'disciplin', get_string('disciplin', 'switchcast'), $scast_disciplins);

        $scast_licenses = $scast->getAllLicenses();
        $mform->addElement('select', 'license', get_string('license', 'switchcast'), $scast_licenses);
        $mform->setDefault('license', '');

        $mform->addElement('text', 'contenthours', get_string('contenthours', 'switchcast'));
        $mform->setType('contenthours', PARAM_INT);

        $lifetime = array(
            6  => get_string('months', 'switchcast', 6),
            12 => get_string('months', 'switchcast', 12),
            24 => get_string('years', 'switchcast', 2),
            36 => get_string('years', 'switchcast', 3),
            60 => get_string('years', 'switchcast', 5),
            72 => get_string('years', 'switchcast', 6)
        );
        $mform->addElement('select', 'lifetime', get_string('lifetime', 'switchcast'), $lifetime);
        $mform->setDefault('lifetime', 36);

        $mform->addElement('text', 'department', get_string('department', 'switchcast'));
        $mform->setType('department', PARAM_TEXT);

        $annotations = array(
            SWITCHCAST_NO_ANNOTATIONS => get_string('annotationsno', 'switchcast'),
            SWITCHCAST_ANNOTATIONS => get_string('annotationsyes', 'switchcast')
        );
        $mform->addElement('select', 'annotations', get_string('annotations', 'switchcast'), $annotations);
        $mform->setDefault('annotations', SWITCHCAST_NO_ANNOTATIONS);

        $scast_templates = scast_obj::getAllTemplates();
        $templates_admin = scast_obj::getEnabledTemplates();
        $templates = array();
        foreach($templates_admin as $template_id => $template_name) {
            if (array_key_exists($template_id, $scast_templates)) {
                $templates[$template_id] = $template_name;
            }
        }
        $mform->addElement('select', 'template_id', get_string('template_id', 'switchcast'), $templates);
        $mform->disabledIf('template_id', 'channelnew', 'eq', SWITCHCAST_CHANNEL_EXISTING);

        $yesno = array(0 => get_string('no'), 1 => get_string('yes'));
        $mform->addElement('select', 'is_ivt', get_string('is_ivt', 'switchcast'), $yesno);
        $mform->addElement('select', 'inviting', get_string('inviting', 'switchcast'), $yesno);
        $mform->disabledIf('inviting', 'is_ivt', 'eq', 0);

        if ( !empty($this->_instance) && scast_obj::getOrganizationByEmail($scuser->getExternalAccount()) !== $this->current->organization_domain ) {
            // teacher has no SwitchAAI account OR is from a different HomeOrg than the Channel Producer(s),
            // so check whether we have sys_account for him to see if we can manipulate the channel
            if ($sysaccount) {
                // sys_account available -> only freeze channel selection
                $mform->disabledIf('ext_id', 'channelnew', 'eq', SWITCHCAST_CHANNEL_EXISTING);
            }
            else {
                // sys_account unavailable -> remove all channel manipulation options and display a notice
                $mform->removeElement('inviting');
                $mform->removeElement('is_ivt');
                $mform->removeElement('template_id');
                $mform->removeElement('annotations');
                $mform->removeElement('department');
                $mform->removeElement('lifetime');
                $mform->removeElement('contenthours');
                $mform->removeElement('license');
                $mform->removeElement('disciplin');
                $mform->removeElement('ext_id');
                $mform->removeElement('channeltype');
                $mform->removeElement('channelnew');
                $mform->addElement('html', get_string('channeldoesnotbelong', 'switchcast', $this->current->organization_domain));
            }
        }
      
        // What if the channel does not exist any more?
        if ( scast_obj::getOrganizationByEmail($scuser->getExternalAccount()) == $this->current->organization_domain && !empty($this->_instance) && !isset($channels[$this->current->ext_id]) ) {
            $mform->removeElement('inviting');
            $mform->removeElement('is_ivt');
            $mform->removeElement('template_id');
            $mform->removeElement('annotations');
            $mform->removeElement('department');
            $mform->removeElement('lifetime');
            $mform->removeElement('contenthours');
            $mform->removeElement('license');
            $mform->removeElement('disciplin');
            $mform->removeElement('ext_id');
            $mform->removeElement('channeltype');
            $mform->removeElement('channelnew');
            $mform->addElement('html', get_string('channeldoesntexist', 'switchcast'));
        }

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();

    }

    function data_preprocessing(&$default_values) {
        // do nothing
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        $scuser = new scast_user();

        if ($data['channelnew'] == SWITCHCAST_CHANNEL_NEW) {
            if ($scuser->getExternalAccount() == '') {
                $errors['channelnew'] = get_string('user_notaai', 'switchcast');
            }
            if (strlen($data['department']) < 1) {
                $errors['department'] = get_string('nodepartment', 'switchcast');
            }
            if ((int)$data['contenthours'] < 1) {
                $errors['contenthours'] = get_string('nocontenthours', 'switchcast');
            }
        }
        if ($data['channelnew'] == SWITCHCAST_CHANNEL_EXISTING) {
            // make sure we can be external_authority for this channel
            $scobj = new scast_obj();
            $ext_id = isset($data['ext_id']) ? ($data['ext_id']) : ($this->current->ext_id);
            $scobj->setExtId($ext_id);
            // first, add SysAccount as producer (using $USER account), so we can use SysAccount later to make API calls
            $scobj->addProducer($scobj->getSysAccountOfUser(), false);
            $channelid = (empty($this->_instance)) ? ($ext_id) : ($this->current->id);
            // if there already is one instance we must refer to it by its Moodle ID otherwise there could
            // be several records!
            $thechannel = $scobj->doRead($channelid, !empty($this->_instance));
            if (trim((string)$thechannel->access) == 'external_authority' && (int)$thechannel->external_authority_id != $scobj->getValueByKey('external_authority_id')) {
                // we can't steal external_authority from another institution
                $errors['ext_id'] = get_string('channelhasotherextauth', 'switchcast', $scobj->getExternalAuthName($thechannel->external_authority_id));
            }
        }

        // make sure we don't use VISIBLEGROUPS
//        if ($data['groupmode'] == VISIBLEGROUPS) {
//            $errors['groupmode'] = get_string('novisiblegroups', 'switchcast');
//        }
        else if ($data['groupmode'] != NOGROUPS && !$data['is_ivt']) {
            $errors['groupmode'] = get_string('nogroups_withoutivt', 'switchcast');
        }

        // make sure we use only allowed channel types
        if ($data['channeltype'] == SWITCHCAST_CHANNEL_PROD && !scast_obj::getValueByKey('allow_prod_channels')) {
            $errors['channeltype'] = get_string('channeltypeforbidden', 'switchcast', $data['channeltype']);
        }
        else if ($data['channeltype'] == SWITCHCAST_CHANNEL_TEST && !scast_obj::getValueByKey('allow_test_channels')) {
            $errors['channeltype'] = get_string('channeltypeforbidden', 'switchcast', $data['channeltype']);
        }

        return $errors;
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Set up completion section even if checkbox is not ticked
        if (empty($data->completionsection)) {
            $data->completionsection = 0;
        }
        return $data;
    }

}

