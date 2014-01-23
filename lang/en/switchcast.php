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

defined('MOODLE_INTERNAL') || die();

$string['answered'] = 'Answered';
$string['channel'] = 'Channel';
$string['channelnew'] = 'New channel';
$string['channelchoose'] = 'Choose an existing channel';
$string['channelexisting'] = 'Existing channel';
$string['channeltype'] = 'Channel type';
$string['channelprod'] = 'Production';
$string['channeltest'] = 'Test channel';
$string['completionsubmit'] = 'Show as complete when user makes a choice';
$string['displayhorizontal'] = 'Display horizontally';
$string['displaymode'] = 'Display mode';
$string['displayvertical'] = 'Display vertically';
$string['expired'] = 'Sorry, this activity closed on {$a} and is no longer available';
$string['fillinatleastoneoption'] = 'You need to provide at least two possible answers.';
$string['full'] = '(Full)';
$string['switchcastclose'] = 'Until';
$string['switchcastname'] = 'SWITCHcast name';
$string['switchcastopen'] = 'Open';
$string['switchcastoptions'] = 'Switchcast options';
$string['chooseaction'] = 'Choose an action ...';
$string['limit'] = 'Limit';
$string['limitanswers'] = 'Limit the number of responses allowed';
$string['modulename'] = 'SWITCHcast channel';
$string['modulename_help'] = 'The SWITCHcast module allows a teacher to manage a SWITCHcast channel from his Moodle course.';
$string['modulenameplural'] = 'SWITCHcast channels';
$string['mustchooseone'] = 'You must choose an answer before saving.  Nothing was saved.';
$string['noresultsviewable'] = 'The results are not currently viewable.';
$string['notanswered'] = 'Not answered yet';
$string['notopenyet'] = 'Sorry, this activity is not available until {$a}';
$string['pluginadministration'] = 'SWITCHcast administration';
$string['pluginname'] = 'SWITCHcast';
$string['timerestrict'] = 'Restrict answering to this time period';
$string['viewallresponses'] = 'View {$a} responses';
$string['withselected'] = 'With selected';
$string['yourselection'] = 'Your selection';
$string['skipresultgraph'] = 'Skip result graph';
$string['moveselectedusersto'] = 'Move selected users to...';
$string['numberofuser'] = 'The number of user';
$string['uid_field'] = 'AAI unique ID field';
$string['uid_field_desc'] = 'User profile field containing the AAI unique ID, of the form &lt;fieldname&gt; OR &lt;table::fieldid&gt;.';
$string['switch_api_host'] = 'SWITCHcast API URL';
$string['switch_api_host_desc'] = 'Address where the SWITCHCAST Web service is to be contacted.';
$string['default_sysaccount'] = 'Default system account';
$string['default_sysaccount_desc'] = 'Default account to use for SWITCHcast API calls.';
$string['sysaccount'] = 'System account for {$a}';
$string['sysaccount_desc'] = 'Account to use for SWITCHcast API calls from {$a}.';
$string['cacrt_file'] = 'CA CRT file';
$string['cacrt_file_desc'] = 'Certification authority Certificate File.';
$string['crt_file'] = 'Certificate file';
$string['crt_file_desc'] = 'x509 Server Certificate File.';
$string['castkey_file'] = 'SwitchCast key file';
$string['castkey_file_desc'] = 'The key provided by SwitchCast to sign the API calls.';
$string['castkey_password'] = 'SwitchCast key file password';
$string['castkey_password_desc'] = 'The password (if any) needed to unlock the above key.';
$string['serverkey_file'] = 'Server key File';
$string['serverkey_file_desc'] = 'This server\'s SSL key File.';
$string['serverkey_password'] = 'Server key file password';
$string['serverkey_password_desc'] = 'The password (if any) needed to unlock the above key.';
$string['enabled_institutions'] = 'Enabled institutions';
$string['enabled_institutions_desc'] = 'Comma-separated list of enabled institutions on this Moodle server.';
$string['external_authority_host'] = 'External authority host';
$string['external_authority_host_desc'] = 'External authority host URL';
$string['external_authority_id'] = 'External authority ID';
$string['external_authority_id_desc'] = 'External authority ID at SWITCHcast';
$string['metadata_export'] = 'Metadata export';
$string['metadata_export_desc'] = '';
//$string['configuration_id'] = 'Configuration ID';
//$string['configuration_id_desc'] = '';
//$string['streaming_configuration_id'] = 'Streaming configuration ID';
//$string['streaming_configuration_id_desc'] = '';
$string['access'] = 'Access';
$string['access_desc'] = 'Access level for created channels.';
$string['allow_test_channels'] = 'Allow test channels';
$string['allow_test_channels_desc'] = 'Check this if you wish to allow creation of test channels.';
$string['allow_prod_channels'] = 'Allow production channels';
$string['allow_prod_channels_desc'] = 'Check this if you wish to allow creation of production channels.';
$string['misconfiguration'] = 'Plugin misconfigured; contact your Moodle administrator.';
$string['channeltypeforbidden'] = 'Creation of {$a} channels forbidden; contact your Moodle administrator.';
$string['logging_enabled'] = 'Logging enabled';
$string['logging_enabled_desc'] = 'Log all XML API calls and responses.<br />The log file is found in {$a}/mod/switchcast/switchcast_api.log';
$string['display_select_columns'] = 'Display only used columns';
$string['display_select_columns_desc'] = 'In the clip list, display only the used fields (columns), such as Recording Station, Owner, Actions. This has a performance impact, because the list of all clips must be retrieved on each display.';
$string['enabled_templates'] = 'Enabled templates';
$string['enabled_templates_desc'] = 'List here each SwitchCast template you wish to enable for your institution (enforced for channel creation only).<br />One definition per line, format : <em>&lt;TEMPLATE_ID&gt;::&lt;TEMPLATE_NAME&gt;</em>.<br />You may omit the template name (but not the two colons) to use Switch\'s official name for a template.';
$string['newchannelname'] = 'New channel name';
$string['license'] = 'License';
$string['disciplin'] = 'Disciplin';
$string['contenthours'] = 'Estimated video content in hours';
$string['lifetime'] = 'Intended lifetime of content';
$string['months'] = '{$a} months';
$string['years'] = '{$a} years';
$string['department'] = 'Department';
$string['annotations'] = 'Annotations';
$string['annotationsyes'] = 'Allow annotations';
$string['annotationsno'] = 'No annotations';
$string['template_id'] = 'SwitchCast template';
$string['is_ivt'] = 'Individual access per clip';
$string['inviting'] = 'Clip Owner can invite other users';
$string['clip_member'] = 'Invited clip member';
$string['channel_teacher'] = 'Teacher';
$string['untitled_clip'] = '(untitled clip)';
$string['no_owner'] = '(no owner)';
$string['owner_not_in_moodle'] = '(Owner not known in Moodle)';
$string['clip_no_access'] = 'You don\'t have access to this clip.';
$string['upload_clip'] = 'Upload a new clip';
$string['edit_at_switch'] = 'Edit this channel on the SWITCHcast server';
$string['edit_at_switch_short'] = 'Edit at SWITCHcast';
$string['switchcast:use'] = 'Display SWITCHcast channel contents';
$string['switchcast:isproducer'] = 'Be registered as a SWITCHcast channel producer (and hence access all clips)';
$string['switchcast:addinstance'] = 'Add a new SWITCHcast channel activity';
$string['switchcast:seeallclips'] = 'Can see all clips in the course';
$string['switchcast:uploadclip'] = 'Can add a clip via Moodle';
$string['nologfilewrite'] = 'Log file not writeable: {$a}. Check file system permissions.';
$string['noclipsinchannel'] = 'This channel contains no clips.';
$string['novisibleclipsinchannel'] = 'This channel contains no clips that you have access to.';
$string['user_notaai'] = 'You need a SWITCHaai account to create a new channel.';
$string['user_homeorgnotenabled'] = 'Creation of a SWITCHcast activity relies on the activation of your HomeOrganization ({$a}) at the site level; please contact the site administrator.';
$string['clip'] = 'Clip';
$string['cliptitle'] = 'Clip – Title';
$string['presenter'] = 'Presenter';
$string['location'] = 'Location';
$string['recording_station'] = 'Recording station';
$string['date'] = 'Date';
$string['owner'] = 'Owner';
$string['actions'] = 'Actions';
$string['editmembers'] = 'Invite members';
$string['addmember'] = 'Add member';
$string['editmembers_long'] = 'Manage the invited members of this clip';
$string['editdetails'] = 'Edit metadata';
$string['delete_clip'] = 'Delete clip';
$string['flash'] = 'Flash';
$string['mov'] = 'QuickTime';
$string['m4v'] = 'iPod';
$string['context'] = 'Context';
$string['confirm_removeuser'] = 'Really remove this user?';
$string['delete_clip_confirm'] = 'Do you really want to delete the clip?';
$string['back_to_channel'] = 'Return to the channel overview';
$string['channel_several_refs'] = 'This channel is referenced in other Moodle activities.';
$string['set_clip_details'] = 'Set clip metadata';
$string['owner_no_switch_account'] = 'Impossible to set &laquo;{$a}&raquo; as the owner of this clip, because this user has no SWITCHaai account.';
$string['nomoreusers'] = 'No more users available to add.';
$string['nocontenthours'] = 'Estimated content hours minimal value is 1.';
$string['nodepartment'] = 'You have to fill in the department.';
$string['setnewowner'] = 'Set as new owner';
$string['clip_owner'] = 'Clip owner';
$string['group_member'] = 'Group member';
$string['clip_uploader'] = 'Uploaded the clip';
$string['aaiid_vs_moodleid'] = 'AAI unique ID does not relate to correct Moodle user ID!';
$string['error_decoding_token'] = 'Error decoding token: {$a}';
$string['error_opening_privatekey'] = 'Error opening private key file: {$a}';
$string['error_decrypting_token'] = 'Error decrypting token: {$a}';
$string['channelhasotherextauth'] = 'This channel is already linked to another external authority: <em>{$a}</em>.';
$string['novisiblegroups'] = 'This group setting is unavailable for this activity.';
$string['nogroups_withoutivt'] = 'Separate groups access is only enforced if the setting &laquo;Individual access per clip&raquo; is enabled above.';
$string['itemsperpage'] = 'Clips per page';
$string['pageno'] = 'Page #';
$string['pagination'] = 'Displaying clips <span class="switchcast-cliprange-from"></span> to <span class="switchcast-cliprange-to"></span> of <span class="switchcast-cliprange-of"></span>.';
$string['filters'] = 'Filters';
$string['resetfilters'] = 'Reset filters';
$string['title'] = 'Title';
$string['subtitle'] = 'Subtitle';
$string['showsubtitles'] = 'show subtitles';
$string['recordingstation'] = 'Recording station';
$string['withoutowner'] = 'Without owner';
$string['notavailable'] = 'Sorry, this activity type is for testing only and is not available yet.';
$string['xml_cache_time'] = 'XML cache lifetime';
$string['xml_cache_time_desc'] = 'How long, in seconds, should the XML responses from the SwitchCast server be kept in cache? Zero value means no caching.';
$string['removeowner'] = 'Remove owner';
$string['channel_not_found'] = 'The linked channel does not exist (anymore?)';
$string['channeldoesnotbelong'] = 'The linked channel belongs to another organization ({$a}); therefore, you cannot modify it. Only a teacher from {$a} can modify it.';
$string['switch_api_down'] = 'The SwitchCast server is not responding.';
$string['xml_fail'] = 'Error communicating with the SwitchCast server.';
$string['badorganization'] = 'This channel\'s organization is not configured correctly.';
$string['curl_proxy'] = 'curl proxy';
$string['curl_proxy_desc'] = 'If curl has to use a proxy, define it here in the form <em>proxyhostname:port</em>';
$string['moodleaccessonly'] = 'This clip can only be accessed from within a Moodle activity.';
$string['loggedout'] = 'You have been logged out. Please refresh the page.';
$string['redirfailed'] = 'Redirection has failed.';
$string['allow_userupload'] = 'Allow user uploads';
$string['allow_userupload_desc'] = 'Allow users to upload video clips into a channel via the Moodle activity. The corresponding option has to be activated in the specific activity as well.';
$string['userupload_maxfilesize'] = 'User maximum file size';
$string['userupload_maxfilesize_desc'] = 'Maximum size of each file the users can upload.';
$string['userupload_error'] = 'An unexpected error occurred while uploading the file; please try again.';
$string['fileis_notavideo'] = 'File is not a video file type! MIME type is: {$a}';
$string['pendingclips'] = 'There are {$a} clips being processed in this channel';
$string['mypendingclips'] = 'You have {$a} clips being processed in this channel';
$string['uploadedclips'] = '{$a} clips have been uploaded to this channel';
$string['myuploadedclips'] = 'You have uploaded {$a} clips into this channel';
$string['clipready_subject'] = 'Your new clip is ready';
$string['clipready_body'] = 'Your uploaded clip "{$a->filename}" is ready, you may find it in the following Moodle activity:

{$a->link}
';
$string['clipstale_subject'] = 'Your uploaded clip had a problem';
$string['clipstale_subject_admin'] = 'An uploaded clip had a problem';
$string['clipstale_body'] = 'Your uploaded clip "{$a->filename}" failed encoding. You should try uploading it again in the following Moodle activity:

{$a->link}
';
$string['clipstale_body_admin'] = 'The uploaded clip "{$a->filename}" failed encoding.

    Moodle activity: {$a->link}
    Moodle user: {$a->userfullname} {$a->userlink}
';
$string['view_useruploads'] = 'Display user uploads';
$string['uploaded_clips'] = 'Clips uploaded by users';
$string['nouploadedclips'] = 'No clips uploaded yet.';
$string['feature_forbidden'] = 'You cannot use this feature';
$string['video_file'] = 'Add your video file here';
$string['video_title'] = 'Title of the video clip';
$string['video_subtitle'] = 'Subtitle of the video clip';
$string['video_presenter'] = 'Presenter of the video clip';
$string['video_location'] = 'Filming ocation of the video clip';
$string['scast_upload_form_hdr'] = 'Upload your video clip here';
$string['uploader'] = 'Uploaded by';

