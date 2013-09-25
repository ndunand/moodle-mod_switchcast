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


defined('MOODLE_INTERNAL') || die;


if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('switchcast/switch_api_host',
            get_string('switch_api_host', 'switchcast'),
            get_string('switch_api_host_desc', 'switchcast'),
            'https://api.cast.switch.ch/api/v2', PARAM_URL));

    $settings->add(new admin_setting_configtext('switchcast/external_authority_host',
            get_string('external_authority_host', 'switchcast'),
            get_string('external_authority_host_desc', 'switchcast'),
            $CFG->wwwroot, PARAM_URL));

    $settings->add(new admin_setting_configtext('switchcast/external_authority_id',
            get_string('external_authority_id', 'switchcast'),
            get_string('external_authority_id_desc', 'switchcast'),
            '', PARAM_INT));

    $settings->add(new admin_setting_configtextarea('switchcast/enabled_templates',
            get_string('enabled_templates', 'switchcast'),
            get_string('enabled_templates_desc', 'switchcast'),
            "1::Standard (3 formats)\n2::Standard (streaming only)",PARAM_TEXT, 60, 6));

    $settings->add(new admin_setting_configcheckbox('switchcast/allow_prod_channels',
            get_string('allow_prod_channels', 'switchcast'),
            get_string('allow_prod_channels_desc', 'switchcast'),
            '1'));

    $settings->add(new admin_setting_configcheckbox('switchcast/allow_test_channels',
            get_string('allow_test_channels', 'switchcast'),
            get_string('allow_test_channels_desc', 'switchcast'),
            '1'));

    $settings->add(new admin_setting_configtext('switchcast/xml_cache_time',
            get_string('xml_cache_time', 'switchcast'),
            get_string('xml_cache_time_desc', 'switchcast'),
            '60', PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('switchcast/display_select_columns',
            get_string('display_select_columns', 'switchcast'),
            get_string('display_select_columns_desc', 'switchcast', $CFG->dataroot),
            '0'));

    $settings->add(new admin_setting_configcheckbox('switchcast/logging_enabled',
            get_string('logging_enabled', 'switchcast'),
            get_string('logging_enabled_desc', 'switchcast', $CFG->dataroot),
            '0'));

    $settings->add(new admin_setting_configtext('switchcast/default_sysaccount',
            get_string('default_sysaccount', 'switchcast'),
            get_string('default_sysaccount_desc', 'switchcast'),
            '', PARAM_EMAIL));

    $settings->add(new admin_setting_configtext('switchcast/enabled_institutions',
            get_string('enabled_institutions', 'switchcast'),
            get_string('enabled_institutions_desc', 'switchcast'),
            '', PARAM_RAW));

    $enabled_institutions_rec = $DB->get_record('config_plugins', array('plugin' => 'switchcast', 'name' => 'enabled_institutions'));
    $switchcast_institutions = explode(',', $enabled_institutions_rec->value);
    foreach ($switchcast_institutions as $switchcast_institution) {
        $switchcast_institution = trim($switchcast_institution);
        if (!$switchcast_institution) {
            continue;
        }
        $switchcast_institution_id = str_replace('.', 'DOT', $switchcast_institution);
        $settings->add(new admin_setting_configtext('switchcast/'.$switchcast_institution_id.'_sysaccount',
                get_string('sysaccount', 'switchcast', $switchcast_institution),
                get_string('sysaccount_desc', 'switchcast', $switchcast_institution),
                '', PARAM_EMAIL));
    }

    $settings->add(new admin_setting_configtext('switchcast/uid_field',
            get_string('uid_field', 'switchcast'),
            get_string('uid_field_desc', 'switchcast'),
            'username', PARAM_RAW));

    $settings->add(new admin_setting_configfile('switchcast/cacrt_file',
            get_string('cacrt_file', 'switchcast'),
            get_string('cacrt_file_desc', 'switchcast'),
            $CFG->dirroot.'/mod/switchcast/certificates/QuoVadisRootCA2'));

    $settings->add(new admin_setting_configfile('switchcast/crt_file',
            get_string('crt_file', 'switchcast'),
            get_string('crt_file_desc', 'switchcast'),
            $CFG->dirroot.'/mod/switchcast/certificates/certificate.crt'));

    $settings->add(new admin_setting_configfile('switchcast/castkey_file',
            get_string('castkey_file', 'switchcast'),
            get_string('castkey_file_desc', 'switchcast'),
            $CFG->dirroot.'/mod/switchcast/certificates/keyfile.key'));

    $settings->add(new admin_setting_configpasswordunmask('switchcast/castkey_password',
            get_string('castkey_password', 'switchcast'),
            get_string('castkey_password_desc', 'switchcast'),
            '', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configfile('switchcast/serverkey_file',
            get_string('serverkey_file', 'switchcast'),
            get_string('serverkey_file_desc', 'switchcast'),
            $CFG->dirroot.'/mod/switchcast/certificates/keyfile.key'));

    $settings->add(new admin_setting_configpasswordunmask('switchcast/serverkey_password',
            get_string('serverkey_password', 'switchcast'),
            get_string('serverkey_password_desc', 'switchcast'),
            '', PARAM_RAW_TRIMMED));


}

