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

require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_xml.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_user.class.php');
require_once($CFG->dirroot.'/mod/switchcast/lib.php');

$channelExtId = required_param('ext_id', PARAM_ALPHANUM);

$sc_user = new scast_user();

$url = scast_obj::getValueByKey('switch_api_host');
if ($sc_user->getExternalAccount() != '') {
    $url .= '/users/' . $sc_user->getExternalAccount();
}
else {
    $url .= '/users/' . scast_obj::getSysAccountOfUser();
}
$url .= '/channels';
$url .= '/' . $channelExtId . '.xml';

$channel = scast_xml::sendRequest($url, 'GET');

$channel_details = array(
    'title'                 => $channel->name,
    'kind'                  => $channel->kind,
    'discipline'            => $channel->discipline_name,
    'license'               => $channel->license_name,
    'estimated_duration'    => $channel->estimated_content_in_hours,
    'lifetime'              => $channel->lifetime_of_content_in_months,
    'department'            => $channel->department,
    'allow_annotations'     => $channel->allow_annotations,
    'template_id'           => scast_obj::getTemplateIdFromName($channel->template_name),
);

echo json_encode($channel_details);

