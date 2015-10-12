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
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('switchcast/operationsettings',
            get_string('operationsettings', 'switchcast'), ''));

    $settings->add(new admin_setting_configtext('switchcast/moreinfo_url', get_string('moreinfo_url', 'switchcast'),
            get_string('moreinfo_url_desc', 'switchcast'), '', PARAM_URL, 50));

    $settings->add(new admin_setting_configcheckbox('switchcast/display_select_columns',
            get_string('display_select_columns', 'switchcast'),
            get_string('display_select_columns_desc', 'switchcast', $CFG->dataroot), '0'));

    $settings->add(new admin_setting_configcheckbox('switchcast/allow_userupload',
            get_string('allow_userupload', 'switchcast'), get_string('allow_userupload_desc', 'switchcast'), '0'));

    $settings->add(new admin_setting_configselect('switchcast/userupload_maxfilesize',
            get_string('userupload_maxfilesize', 'switchcast'), get_string('userupload_maxfilesize_desc', 'switchcast'),
            10 * 1024 * 1024, mod_switchcast_series::getMaxfilesizes()));

    $settings->add(new admin_setting_configtext('switchcast/uploadfile_extensions',
            get_string('uploadfile_extensions', 'switchcast'), get_string('uploadfile_extensions_desc', 'switchcast'),
            'mov, mp4, m4v, avi, mpg, mpe, mpeg, mts, vob, flv, mkv, dv, mp3, aac, wav, wma, wmv, divx', PARAM_RAW,
            50));

    $settings->add(new admin_setting_heading('switchcast/adminsettings', get_string('adminsettings', 'switchcast'),
            ''));

    $settings->add(new admin_setting_configtext('switchcast/switch_api_host',
            get_string('switch_api_host', 'switchcast'), get_string('switch_api_host_desc', 'switchcast'),
            'https://api.cast.switch.ch/api/v2', PARAM_URL, 50));

    $settings->add(new admin_setting_configtext('switchcast/switch_admin_host',
            get_string('switch_admin_host', 'switchcast'), get_string('switch_admin_host_desc', 'switchcast'),
            'https://cast.switch.ch/', PARAM_URL, 50));

    $settings->add(new admin_setting_configtext('switchcast/import_workflow',
            get_string('import_workflow', 'switchcast'),
            '', 'switchcast-import-api-1.0', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('switchcast/pubchannel_videoplayer',
            get_string('pubchannel_videoplayer', 'switchcast'),
            '', 'switchcast-player', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('switchcast/pubchannel_download',
            get_string('pubchannel_download', 'switchcast'),
            '', 'switchcast-api', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('switchcast/pubchannel_annotate',
            get_string('pubchannel_annotate', 'switchcast'),
            '', 'switchcast-annotate', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('switchcast/thumbnail_flavors',
            get_string('thumbnail_flavors', 'switchcast'),
            '', 'presenter/search+preview, presentation/search+preview', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('switchcast/local_cache_time',
            get_string('local_cache_time', 'switchcast'), get_string('local_cache_time_desc', 'switchcast'), '1200',
            PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('switchcast/logging_enabled',
            get_string('logging_enabled', 'switchcast'),
            get_string('logging_enabled_desc', 'switchcast', $CFG->dataroot), '0'));

    $settings->add(new admin_setting_configtext('switchcast/uid_field', get_string('uid_field', 'switchcast'),
            get_string('uid_field_desc', 'switchcast'), 'username', PARAM_RAW));

    $settings->add(new admin_setting_configfile('switchcast/cacrt_file', get_string('cacrt_file', 'switchcast'),
            get_string('cacrt_file_desc', 'switchcast'),
            $CFG->dataroot . '/mod/switchcast/certificates/QuoVadisRootCA2'));

    $settings->add(new admin_setting_configfile('switchcast/crt_file', get_string('crt_file', 'switchcast'),
            get_string('crt_file_desc', 'switchcast'),
            $CFG->dataroot . '/mod/switchcast/certificates/certificate.crt'));

    $settings->add(new admin_setting_configfile('switchcast/castkey_file', get_string('castkey_file', 'switchcast'),
            get_string('castkey_file_desc', 'switchcast'),
            $CFG->dataroot . '/mod/switchcast/certificates/keyfile.key'));

    $settings->add(new admin_setting_configpasswordunmask('switchcast/castkey_password',
            get_string('castkey_password', 'switchcast'), get_string('castkey_password_desc', 'switchcast'), '',
            PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configfile('switchcast/serverkey_file', get_string('serverkey_file', 'switchcast'),
            get_string('serverkey_file_desc', 'switchcast'),
            $CFG->dataroot . '/mod/switchcast/certificates/keyfile.key'));

    $settings->add(new admin_setting_configpasswordunmask('switchcast/serverkey_password',
            get_string('serverkey_password', 'switchcast'), get_string('serverkey_password_desc', 'switchcast'), '',
            PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configtext('switchcast/curl_proxy', get_string('curl_proxy', 'switchcast'),
            get_string('curl_proxy_desc', 'switchcast'), '', PARAM_URL, 50));

    $settings->add(new admin_setting_configtext('switchcast/curl_timeout', get_string('curl_timeout', 'switchcast'),
            get_string('curl_timeout_desc', 'switchcast'), '50', PARAM_INT));
}

