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
 * @copyright  2013-2015 Université de Lausanne
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_switchcast_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $PAGE;

        $mform =& $this->_form;

        // some checks, before going any further
        $scuser = new mod_switchcast_user();
        if (empty($this->_instance) && $scuser->getExternalAccount() == '') {
            // $USER has no SWITCHaai account and is attempting to create a new activity instance:
            // he cannot create a channel nor link to an existing channel (because he doesn't own
            // any, as he doesn't exist in SwitchCast). Therefore, we prevent him from going any further.
            print_error('user_notaai', 'switchcast',
                    new moodle_url('/course/view.php', ['id' => (int)$this->current->course]));
        }
        else if (empty($this->_instance) && !in_array(mod_switchcast_series::getOrganizationByEmail($scuser->getExternalAccount()),
                        mod_switchcast_series::getEnabledOrgnanizations())
        ) {
            // $USER has a SWITCHaai account, but we don't have a sys_account for his HomeOrganization.
            // Therefore, we prevent him from going any further.
            print_error('user_homeorgnotenabled', 'switchcast',
                    new moodle_url('/course/view.php', ['id' => (int)$this->current->course]),
                    mod_switchcast_series::getOrganizationByEmail($scuser->getExternalAccount()));
        }

        if (!empty($this->_instance) && !in_array($this->current->organization_domain,
                        mod_switchcast_series::getEnabledOrgnanizations())
        ) {
            print_error('badorganization', 'switchcast',
                    new moodle_url('/course/view.php', ['id' => (int)$this->current->course]));
        }

        if ($scuser->getExternalAccount() != '') {
            // $USER has a SWITCHaai account, so register him at SwitchCast to make sure it exists there
            //            mod_switchcast_obj::registerUser($scuser);
        }

        // have we got a sys_account for the channel?
        $sysaccount = false;
        if (!empty($this->_instance) && in_array($this->current->organization_domain,
                        mod_switchcast_series::getEnabledOrgnanizations())
        ) {
            $sysaccount_extid = mod_switchcast_series::getSysAccountByOrganization($this->current->organization_domain);
            $sysaccount = new mod_switchcast_user($sysaccount_extid);
        }

        $PAGE->requires->jquery();
        $PAGE->requires->js('/mod/switchcast/js/existing_series.js');

        // General settings :
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('switchcastname', 'switchcast'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        }
        else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(false, get_string('chatintro', 'chat'));

        // Miscellaneous settings :
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));

        if (mod_switchcast_series::getValueForKey('moreinfo_url')) {
            $mform->addElement('static', 'moreinfo_url', get_string('miscellaneoussettings_help', 'switchcast'),
                    html_writer::link(mod_switchcast_series::getValueForKey('moreinfo_url'),
                            mod_switchcast_series::getValueForKey('moreinfo_url')));
        }

        $mform->addElement('select', 'channelnew', get_string('channel', 'switchcast'), [
                        SWITCHCAST_CHANNEL_NEW      => get_string('channelnew', 'switchcast'),
                        SWITCHCAST_CHANNEL_EXISTING => get_string('channelexisting', 'switchcast')
                ]);
        if (empty($this->_instance)) {
            $mform->setDefault('channelnew', SWITCHCAST_CHANNEL_NEW);
        }
        else {
            $mform->setDefault('channelnew', SWITCHCAST_CHANNEL_EXISTING);
        }

        if (empty($this->_instance)) {
            if ($scuser->getExternalAccount() != '') {
                // USER has a SWITCHaai account -> get his channels
                $userchannels = $scuser->getChannels();
            }
            else {
                $userchannels = [];
            }
        }
        else {
            if ($sysaccount !== false) {
                // We've got a sys_account for this instance's organization -> get the user's channels list
                $userchannels = $scuser->getChannels();
            }
            else {
                // No sys_account for this instance's organization -> no channels list can be displayed
                $userchannels = [];
            }
        }

        $channels = [];
        if (!empty($this->_instance) && $scuser->getExternalAccount() == '') {
            // Instance exists but $USER is not SWITCHaai => get channels list
            // from $sysaccount, which MUST exist because we already checked.
            // We freeze the channel selector further anyway because $USER's
            // HomeOrg isn't the same as the channel's.
            $userchannels = $sysaccount->getChannels();
        }
        foreach ($userchannels as $userchannel) {
            $channels[(string)$userchannel->identifier] = (string)$userchannel->title;
        }
        $mform->addElement('select', 'ext_id', get_string('channelchoose', 'switchcast'), $channels);
        $mform->disabledIf('ext_id', 'channelnew', 'eq', SWITCHCAST_CHANNEL_NEW);

        $mform->addElement('text', 'newchannelname', get_string('newchannelname', 'switchcast'));
        $mform->disabledIf('newchannelname', 'channelnew', 'eq', SWITCHCAST_CHANNEL_EXISTING);
        $mform->setType('newchannelname', PARAM_TEXT);

        if (!empty($this->_instance)) {
            $mform->freeze('channelnew');
            $mform->removeElement('newchannelname');
        }

        $scast = new mod_switchcast_series();

        $scast_licenses = $scast->getAllLicenses();
        $mform->addElement('select', 'license', get_string('license', 'switchcast'), $scast_licenses);
        $mform->setDefault('license', '');

        $annotations = [
                SWITCHCAST_NO_ANNOTATIONS => get_string('annotationsno', 'switchcast'),
                SWITCHCAST_ANNOTATIONS    => get_string('annotationsyes', 'switchcast')
        ];
        $mform->addElement('select', 'annotations', get_string('annotations', 'switchcast'), $annotations);
        $mform->setDefault('annotations', SWITCHCAST_NO_ANNOTATIONS);

        $mod_switchcast_templates = mod_switchcast_series::getAllTemplates();
        $templates_admin = mod_switchcast_series::getEnabledTemplates(); // TODO NOW : remove all about templates
        $templates = [];
        foreach ($templates_admin as $template_id => $template_name) {
            if (array_key_exists($template_id, $mod_switchcast_templates)) {
                $templates[$template_id] = $template_name;
            }
        }
        $mform->addElement('select', 'template_id', get_string('template_id', 'switchcast'), $templates);
        $mform->disabledIf('template_id', 'channelnew', 'eq', SWITCHCAST_CHANNEL_EXISTING);
        $mform->addHelpButton('template_id', 'template_id', 'switchcast');

        $yesno = [0 => get_string('no'), 1 => get_string('yes')];
        $mform->addElement('select', 'is_ivt', get_string('is_ivt', 'switchcast'), $yesno);
        $mform->addElement('select', 'inviting', get_string('inviting', 'switchcast'), $yesno);
        $mform->disabledIf('inviting', 'is_ivt', 'eq', 0);

        if (mod_switchcast_series::getValueForKey('allow_userupload') && mod_switchcast_series::getValueForKey('userupload_maxfilesize')) {
            $mform->addElement('select', 'userupload', get_string('allow_userupload', 'switchcast'), $yesno);
            $mform->addElement('select', 'userupload_maxfilesize', get_string('userupload_maxfilesize', 'switchcast'),
                    mod_switchcast_series::getMaxfilesizes(true));
        }

        if (!empty($this->_instance) && mod_switchcast_series::getOrganizationByEmail($scuser->getExternalAccount()) !== $this->current->organization_domain) {
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
                $mform->removeElement('license');
                $mform->removeElement('ext_id');
                $mform->removeElement('channelnew');
                $mform->removeElement('userupload');
                $mform->removeElement('userupload_maxfilesize');
                $mform->addElement('html',
                        get_string('channeldoesnotbelong', 'switchcast', $this->current->organization_domain));
            }
        }

        // What if the channel does not exist any more? -> remove all channel manipulation options and display a notice
        if (!empty($this->_instance) && mod_switchcast_series::getOrganizationByEmail($scuser->getExternalAccount()) == $this->current->organization_domain && !isset($channels[$this->current->ext_id])) {
            $mform->removeElement('inviting');
            $mform->removeElement('is_ivt');
            $mform->removeElement('template_id');
            $mform->removeElement('annotations');
            $mform->removeElement('license');
            $mform->removeElement('ext_id');
            $mform->removeElement('channelnew');
            $mform->removeElement('userupload');
            $mform->removeElement('userupload_maxfilesize');
            $mform->addElement('html',
                    html_writer::tag('p', get_string('channel_not_found', 'switchcast'), ['class' => 'notify']));
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
        $scuser = new mod_switchcast_user();

        if ($data['channelnew'] == SWITCHCAST_CHANNEL_NEW) {
            if ($scuser->getExternalAccount() == '') {
                $errors['channelnew'] = get_string('user_notaai', 'switchcast');
            }
            if (!$data['newchannelname']) {
                $errors['newchannelname'] = get_string('required');
            }
        }
        if ($data['channelnew'] == SWITCHCAST_CHANNEL_EXISTING) {
            // make sure we can be external_authority for this channel
            $scobj = new mod_switchcast_series();
            $ext_id = isset($data['ext_id']) ? ($data['ext_id']) : ($this->current->ext_id);
            $scobj->setExtId($ext_id);
            $sysaccount_extid = mod_switchcast_series::getSysAccountOfUser();
            // we must explicitly set $USER as a producer in $scobj or we won't be allowed to add his system_user
            $scobj->setOrganizationDomain(mod_switchcast_series::getOrganizationByEmail($sysaccount_extid));
            $scobj->setProducer($scuser->getExternalAccount());
            // first, add SysAccount as producer (using $USER account), so we can use SysAccount later to make API calls
//            $scobj->addProducer($sysaccount_extid, false);
            $channelid = (empty($this->_instance)) ? ($ext_id) : ($this->current->id);
            // if there already is one instance we must refer to it by its Moodle ID otherwise there could
            // be several records!
            $thechannel = $scobj->fetch($channelid, !empty($this->_instance), true);
        }

        // make sure we don't use VISIBLEGROUPS
        //        if ($data['groupmode'] == VISIBLEGROUPS) {
        //            $errors['groupmode'] = get_string('novisiblegroups', 'switchcast');
        //        }
        else if ($data['groupmode'] != NOGROUPS && !$data['is_ivt']) {
            $errors['groupmode'] = get_string('nogroups_withoutivt', 'switchcast');
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

