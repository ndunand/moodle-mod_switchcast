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
 * @author     Fabian Schmid <schmid@ilub.unibe.ch>
 * @author     Martin Studer <ms@studer-raimann.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/switchcast/scast_clip.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_user.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_xml.class.php');

define('SWITCHCAST_CHANNEL_PROD', 'periodic');
define('SWITCHCAST_CHANNEL_TEST', 'test');

define('SWITCHCAST_CHANNEL_NEW', 'new channel');
define('SWITCHCAST_CHANNEL_EXISTING', 'existing channel');

define('SWITCHCAST_ANNOTATIONS', 'yes');
define('SWITCHCAST_NO_ANNOTATIONS', 'no');


class scast_obj {

	/**
	 * @var bool
	 */
	protected $allow_annotations;

	/**
	 * @var bool
	 */
	protected $template_id;

	/**
	 * @var string
	 */
	protected $ext_id;

	/**
	 * @var string
	 */
	protected $organization_domain;

	/**
	 * @var string
	 */
	protected $introduction_text;

	/**
	 * @var string
	 */
	protected $channelname;

	/**
	 * @var string
	 */
	protected $sys_account;


	/**
	 * Constructor
	 *
	 * @access    public
	 */
	function __construct() {
		global $PAGE;

        $PAGE->requires->js('/mod/switchcast/js/javascript.js');

        // initially, set $sys_account and $organization_domain to current $USER's
        $sc_user = new scast_user();
        $this->setSysAccount($this->getSysAccountByOrganization(self::getOrganizationByEmail($sc_user->getExternalAccount())));
		$this->organization_domain = self::getOrganizationByEmail($sc_user->getExternalAccount());
	}


	/**
	 * @param $email
	 * @return mixed
	 */
	public static function getOrganizationByEmail($email) {
        if (!$email) {
            return false;
        }
		return preg_replace('/^[^@]+@([^.]+\.)?([^.]+\.ch)$/', '$2', $email);
	}


	/**
	* @return array
	*/
	public static function getEnabledOrgnanizations() {
	    $enabled_institutions_str = self::getValueByKey('enabled_institutions');
	    return explode(',', str_replace(' ', '', $enabled_institutions_str));
	}


	/**
	 * @param $organization
	 * @return mixed
	 */
	public static function getSysAccountByOrganization($organization) {
	    if (in_array($organization, self::getEnabledOrgnanizations())) {
            $sys_account_key = $organization . '_sysaccount';
            return self::getValueByKey($sys_account_key);
        }
        else {
            return self::getValueByKey('default_sysaccount');
        }
	}


    /**
     *
     * @return string
     */
	public static function getSysAccountOfUser() {
		$scuser = new scast_user();
		$organizationDomain = self::getOrganizationByEmail($scuser->getExternalAccount());
		$sys_account = self::getSysAccountByOrganization($organizationDomain);

		if ($sys_account == '') {
            // If the user's external account has no sysaccount,
            // the default sysaccount is used.
            $sys_account = self::getValueByKey('default_sysaccount');
		}

		return $sys_account;
	}


	public function getOrganization() {
		return $this->organization_domain;
	}


	/**
	 * @param $email
	 * @return bool returns true iff the organization of the email-address is the same as the channels organization.
	 */
	public function isAllowedAsPublisher($email) {
		return $this->organization_domain == self::getOrganizationByEmail($email);
	}


	/**
	 * register User at SWITCHcast
	 *
	 */
	public static function registerUser(scast_user $scuser) {
        global $USER;

        $url = self::getValueByKey('switch_api_host');
		$url .= '/users.xml';

		$data_for_xml = array(
			'root' => 'user',
			'data' => array(
				'login' => (string)$scuser->getExternalAccount(),
				'lastname' => (string)$USER->lastname,
				'firstname' => (string)$USER->firstname,
				'email' => (string)$USER->email,
				'organization_domain' => self::getOrganizationByEmail($scuser->getExternalAccount())
			)
		);

		return scast_xml::sendRequest($url, 'POST', $data_for_xml);

	}


	/**
	 * set Sys Account
	 *
	 */
	public function setSysAccount($a_sys_account) {
		$this->sys_account = $a_sys_account;
	}


	/**
	 * get Sys Account
	 *
	 */
	public function getSysAccount() {
		if (!$this->sys_account) {
			// Fallback
			$this->sys_account = self::getValueByKey('default_sysaccount');
		}
		return $this->sys_account;
	}


	/**
	 * add Producer
	 *
	 */
	public function addProducer($aaiUniqueId, $usesysaccount = true) {

        if ( ! $this->isAllowedAsPublisher($aaiUniqueId) ) {
            // only add producers from the same institution as the channel's
            return false;
        }

        $url = $this->getValueByKey('switch_api_host');
        if ($usesysaccount) {
            $url .= '/users/' . $this->getSysAccount();
        }
        else {
            $scuser = new scast_user();
            if (!$this->isProducer($scuser->getExternalAccount())) {
                return false;
            }
            $url .= '/users/' . $scuser->getExternalAccount();
        }
        $url .= '/channels';
        $url .= '/' . $this->getExtId();
        $url .= '/producers';
        $url .= '/' . $aaiUniqueId . '.xml';

		scast_xml::sendRequest($url, 'PUT');
        $this->setProducer($aaiUniqueId);
		return true;
	}


	/**
	 * is Producer
	 *
	 */
	public function isProducer($aaiUniqueId) {
		$arr_producer = $this->getProducers();
		return in_array($aaiUniqueId, $arr_producer);
	}


    /**
     *
     * @return array
     */
    public function getAllSysAccounts() {
        global $DB;
        $sysaccounts = array();
        $configs = $DB->get_records_sql("SELECT * FROM {switchcast_config} WHERE name LIKE '%sysaccount'");
        foreach ($configs as $config) {
            $sysaccounts[] = $config->value;
        }
        return $sysaccounts;
    }


	/**
	 * get Producers
     *
	 */
	public function getProducers() {
        if (isset($this->producers)) {
            return $this->producers;
        }
        else {
            return array();
        }
	}


    /**
     *
     * @param string $key
     * @return string
     */
    public static function getValueByKey($key) {
        global $DB;
        $key = str_replace('.', 'DOT', $key);
        $config = $DB->get_record('config_plugins', array('plugin' => 'switchcast', 'name' => $key));
        if (!$config) {
            scast_log::write('ERROR Config does not exist: '.$key);
            return '';
        }
        return $config->value;
    }


	/**
	 * set Producers
	 *
	 */
	public function setProducer($aaiUniqueId) {
        if ( $this->isAllowedAsPublisher($aaiUniqueId) ) {
            // only add producers from the same institution
            $this->producers[] = $aaiUniqueId;
        }
	}


    /**
     *
     * @param type $aaiUniqueId
     * @return boolean success state
     */
	public function removeProducer($aaiUniqueId) {

        if ( ! $this->isAllowedAsPublisher($aaiUniqueId) ) {
            // only remove producers from the same institution
            return false;
        }

		$url = $this->getValueByKey('switch_api_host');
		$url .= '/users/' . $this->getSysAccount();
		$url .= '/channels';
		$url .= '/' . $this->getExtId();
		$url .= '/producers';
		$url .= '/' . $aaiUniqueId . '.xml';

		scast_xml::sendRequest($url, "DELETE");

        return true;
	}


	/**
	 * get Clips
	 *
	 */
	public function getClips($arr_filter = array()) {

		$url = $this->getValueByKey('switch_api_host');
		$url .= '/vod';
		$url .= '/users/' . $this->getSysAccount();
		$url .= '/channels';
		$url .= '/' . $this->getExtId();
		$url .= '/clips.xml?conditions=';

		$i = 0;

		foreach ($arr_filter as $key => $filter) {

			if (trim($filter) != '') {
				if ($i > 0) {
					$url .= '%20AND%20';
				}
                $value = str_replace(' ', '%20', $filter);

				if ($key == 'withoutowner') {
                    if ($filter == 'true') {
                        $url .= 'ivt__owner%20IS%20NULL';
                    }
                    else {
                        $url = preg_replace('|%20AND%20$|', '', $url);
                    }
				}
                elseif ($key == 'ivt_owner' AND $filter != "") {
                    $url .= "ivt__owner%20LIKE%20'%25".$filter."%25'";
                }
                elseif ($key == 'recordingstation' AND $filter != "") {
                    $url .= "ivt__recordingstation%20LIKE%20'%25".$filter."%25'";
                }
				else {
					$url .= $key . "%20LIKE%20'%25" . $value . "%25'";
				}
                $i++;
			}
		}

		$obj_clips = scast_xml::sendRequest($url, 'GET');
        $data = array();

        foreach ($obj_clips->clip as $clip) {
            $data[] = $clip;
        }

		return $data;
	}


	/**
     * Check access to clips, and give back only the ones we have access to.
     *
	 * @param $clips array
	 * @return array
	 */
	public function checkAccess($clips = array()) {
		$newData = array();
        foreach ($clips as $clip) {
            // we have to instantiate a new scast_clip because $clip is a SimpleXMLElement
            $clipobj = new scast_clip($this, (string)$clip->ext_id);
            if ($clipobj->checkPermissionBool('read')) {
                $newData[] = $clip;
            }
        }
		return $newData;
	}


    /**
     * Returns a list of the templates allowed by the Moodle administrator.
     *
     * @return array a list of enabled templates (id => name)
     */
    public static function getEnabledTemplates() {
        $enabled_templates = explode("\n", self::getValueByKey('enabled_templates'));
        $templates = array();
        foreach ($enabled_templates as $enabled_template) {
            $parts = explode('::', $enabled_template);
            if (count($parts) !== 2) {
                continue;
            }
            $t_id = $parts[0];
            $t_title = $parts[1];
            if (!trim($t_title)) {
                // use SwitchCast official template name
                $t = self::getAllTemplates();
                $t_title = $t[$t_id];
            }
            $templates[$t_id] = $t_title;
        }
        return $templates;
    }


    /**
     * Returns a SwitchCast template ID when given a SwitchCast template name,
     * returns false if this template is not found or not enabled by the
     * Moodle administrator.
     *
     * @param string $template_name
     * @return boolean|integer the template ID or false if not found
     */
    public static function getTemplateIdFromName($template_name) {
        if (!in_array($template_name, self::getAllTemplates())) {
            return false;
        }
        $id = array_search($template_name, self::getAllTemplates());
        if (!array_key_exists($id, self::getEnabledTemplates())) {
            return false;
        }
        return $id;
    }


	/**
	 * To create a channel we need an aai account that is allowed to register a new channel.
     * Thus the first choice is the aai account of the current user, if he doesn't have an
     * account we use the system account.
	 */
	function doCreate() {
		global $USER;
        $scuser = new scast_user();

        // createuser @ switchcast server to be sure it exists there
        scast_obj::registerUser($scuser);

		// if the current USER has no switchaai account, prevent channel creation
		if ($scuser->getExternalAccount() == '') {
			print_error('user_notaai', 'switchcast');
		}

		// sets the sysaccount to the account according to the users extId
		$this->setSysAccount($this->getSysAccountOfUser());
		$this->organization_domain = self::getOrganizationByEmail($scuser->getExternalAccount());

		if ($this->getExtId() == '') {
            // No ext_id: that's a new channel to be created at SWITCHcast server
			$url = $this->getValueByKey('switch_api_host');
			$url .= '/users/' . $scuser->getExternalAccount();
			$url .= '/channels.xml';

			// Daten für neuen Channel
			$data_for_xml = array(
				'root' => 'channel',
				'data' => array(
					'name' => (string)$this->getChannelName(),
					'discipline_id' => (int)$this->getDisciplineId(),
					'license' => (string)$this->getLicense(),
					'author' => (string)$USER->firstname . ' ' . $USER->lastname,
					'department' => (string)$this->getDepartment(),
					'organization_domain' => (string)$this->organization_domain,
					'access' => (string)'external_authority',
                    'external_authority_id' => (int)$this->getValueByKey('external_authority_id'),
					'export_metadata' => (int)0,
					'template_id' => (int)$this->getTemplateId(),
					'estimated_content_in_hours' => (int)$this->getEstimatedContentInHours(),
					'lifetime_of_content_in_months' => (int)$this->getLifetimeOfContentinMonth(),
					'sort_criteria' => (string)'recording_date',
					'auto_chapter' => (int)1,
					'allow_annotations' => (int)$this->getAllowAnnotations() ? 'yes' : 'no',
                    'kind' => (string)$this->getChannelKind()
				)
			);

			$obj_channel = scast_xml::sendRequest($url, 'POST', $data_for_xml);

			// Check ext_id
			if ($obj_channel->ext_id != '') {
				$this->setExtId($obj_channel->ext_id);
				$this->addProducer($this->getSysAccount(), false);
			}
            else {
                print_error('errorchannelcreation', 'switchcast');
            }

		}

		else {
            // existing channel at SWITCHcast server, to be updated
            // basically, we only add our sysAccount as producer at the SWITCHcast server
			$this->doUpdate();
		}

	}


	/**
     *
     * @param int|string $id
     * @param bool $inmoodle
     * @return type
     */
	function doRead($id, $inmoodle = true, $returninfo = false) {
		global $DB;

        if ($inmoodle) {
            // there must be a DB record
            if (is_number($id)) {
                $rec = $DB->get_record('switchcast', array('id' => $id));
            }
            else {
                $rec = $DB->get_record('switchcast', array('ext_id' => $id));
            }

            $this->setExtId($rec->ext_id);
            $this->setIvt($rec->is_ivt);
            $this->setInvitingPossible($rec->inviting);
            $this->setSysAccount($this->getSysAccountByOrganization($rec->organization_domain));
            $this->setOrganizationDomain($rec->organization_domain);
        }
        else if (!is_number($id)) {
            // channel not in Moodle
            $this->setExtId($id);
        }
        else {
            print_error();
        }

		// Channel
		$url = $this->getValueByKey('switch_api_host');
		$url .= '/users/' . $this->getSysAccount();
		$url .= '/channels';
		$url .= '/' . $this->getExtId() . '/edit.xml';

		$ch = scast_xml::sendRequest($url, 'GET');

        if (!$inmoodle || $returninfo) {
            // we just want the channel info
            return $ch;
        }

		// Daten aus SwitchCast anpassen
        $this->setChannelName((string)$ch->name);
		$this->setLicense((string)$ch->license);
		$this->setEstimatedContentInHours((int)$ch->estimated_content_in_hours);
		$this->setLifetimeOfContentinMonth((int)$ch->lifetime_of_content_in_months);
		$this->setDepartment((string)$ch->department);
		$this->setAllowAnnotations(trim((string)$ch->allow_annotations) == 'yes');
        $this->setDisciplineId((int)$ch->discipline_id);
//        $this->setOrganizationDomain((string)$ch->organization_name);

		if (count($ch->producers->user) > 0) {
			foreach ($ch->producers->user as $producer) {
				$this->setProducer((string)$producer->login);
			}
		}

		$this->setUploadForm($ch->urls->url[1]);
		$this->setEditLink($ch->urls->url[4]);

	}

	/**
	 * Update data
     *
     * @return boolean true if success
     */
	function doUpdate() {
		global $USER;

        $scuser = new scast_user();
        if ($scuser->getExternalAccount() != '') {
            // first, add sysAccount as producer, using our own authentication
            // only if we have a SwitchAAI account!
            $this->addProducer($this->getSysAccount(), false);
            // then we can deal with the channel using the sysAccount
        }

		$url = $this->getValueByKey('switch_api_host');
		$url .= '/users/' . $this->getSysAccount();
		$url .= '/channels';
		$url .= '/' . $this->getExtId() . '.xml';

		// We only allow updating of certain parameters
		$data_for_xml = array(
			'root' => 'channel',
			'data' => array(
//				'name' => (string)$this->getChannelName(),
                'discipline_id' => (int)$this->getDisciplineId(),
				'license' => (string)$this->getLicense(),
//				'author' => (string)$USER->firstname . ' ' . $USER->lastname,
				'department' => (string)$this->getDepartment(),
				'access' => (string)'external_authority',
				'external_authority_id' => (int)$this->getValueByKey('external_authority_id'),
//				'export_metadata' => (int)0,
//				'template_id' => (int)$configuration_id,
				'estimated_content_in_hours' => (int)$this->getEstimatedContentInHours(),
				'lifetime_of_content_in_months' => (int)$this->getLifetimeOfContentinMonth(),
//				'sort_criteria' => (string)'recording_date',
//				'auto_chapter' => (int)1,
				'allow_annotations' => (int)$this->getAllowAnnotations() ? 'yes' : 'no'
			)
		);

		if (!scast_xml::sendRequest($url, 'PUT', $data_for_xml)) {
			return false;
		};

		return true;
	}


	/**
	 *
	 * @param int $a_val
	 * @return boolean
	 */
	public function getAllDisciplines() {
        $scuser = new scast_user();

		$url = self::getValueByKey('switch_api_host');
		$url .= '/users/' . $this->getSysAccount();
		$url .= '/channels';
		$url .= '/new.xml';

		$new = scast_xml::sendRequest($url, 'GET');
        $disciplines = array();

        if (count($new->discipline_id[0]) > 0) {
            foreach ($new->discipline_id[0] as $discipline) {
                $attr = $discipline->attributes();
                $value = (int)$attr['value'];
                $disciplines[(int)$value] = (string)$discipline;
            }
        }
        return $disciplines;
	}


    /**
     * Finds the external_authority name of this LMS at the SWITCHcast server
     *
     * @return string|bool name if found, false if not found
     */
	public function getExternalAuthName($id = 0) {

        if ($id === 0) {
            $id = $this->getValueByKey('external_authority_id');
        }

		$url = $this->getValueByKey('switch_api_host');
		$url .= '/users/' . $this->getSysAccount();
		$url .= '/channels';
		$url .= '/new.xml';

		$new = scast_xml::sendRequest($url, 'GET');

        if (count($new->external_authority_id[0]) > 0) {
            foreach ($new->external_authority_id[0] as $external_auth) {
                $attr = $external_auth->attributes();
                if ((int)$attr['value'] == (int)$id) {
                    return (string)$external_auth;
                }
            }
        }
        return false;
	}


	/**
	 * getAllLicences
     *
	 * @return array
	 */
	public function getAllLicenses() {

		$url = self::getValueByKey('switch_api_host');
		$url .= '/users/' . $this->getSysAccount();
		$url .= '/channels';
		$url .= '/new.xml';

		$new = scast_xml::sendRequest($url, 'GET');
        $licenses = array();

        if (count($new->license[0]) > 0) {
            foreach ($new->license[0] as $license) {
                $attr = $license->attributes();
                $value = (string)$attr['value'];
                $licenses[$value] = (string)$license;
            }
            return $licenses;
        }
        else {
            return array();
        }
	}


	/**
	 * gets all templates enabled at the SwitchCast server
     *
	 * @return array
	 */
	public static function getAllTemplates() {

		$url = self::getValueByKey('switch_api_host');
		$url .= '/users/' . self::getValueByKey('default_sysaccount');
		$url .= '/channels';
		$url .= '/new.xml';

		$new = scast_xml::sendRequest($url, 'GET');
        $templates = array();

        if (count($new->template_id[0]) > 0) {
            foreach ($new->template_id[0] as $template) {
                $attr = $template->attributes();
                $value = (string)$attr['value'];
                $templates[$value] = (string)$template;
            }
            return $templates;
        }
        else {
            return array();
        }
	}


	/**
	 * Read data from db
     *
	 */
	public function hasReferencedChannels() {
		$referenced_channels = $this->getAllReferences();
		return count($referenced_channels);
	}


	/**
	 * Read data from db
     *
	 */
	public function getAllReferences($ext_id = false) {
		global $DB;

		if (!$ext_id) {
			$ext_id = $this->getExtId();
		}

        $records = $DB->get_records('switchcast', array('ext_id' => $ext_id));

		foreach ($records as $record) {
			$count[] = $record->id;
		}

		return $count;
	}


    /**
	 * setExtId
	 * @param string $a_val
	 */
	public function setExtId($a_val) {
		$this->ext_id = (string)$a_val;
	}


	/**
	 * getExtId
	 * @return string
	 */
	public function getExtId() {
		return $this->ext_id;
	}


	/**
	 * setExtId
	 * @param string $a_val
	 */
	public function setChannelName($a_val) {
		$this->channelname = $a_val;
	}


	/**
	 * getExtId
	 * @return string
	 */
	public function getChannelName() {
		return $this->channelname;
	}


	/**
	 * setEstimatedContentInHours
	 * @param int $a_val
	 */
	public function setEstimatedContentInHours($a_val) {
		$this->estimatet_content_in_hours = $a_val;
	}


	/**
	 * getEstimatedContentInHours
	 * @return int
	 */
	public function getEstimatedContentInHours() {
		return $this->estimatet_content_in_hours;
	}


	/**
	 * setLifetimeOfContentinMonth
	 * @param int $a_val
	 */
	public function setLifetimeOfContentinMonth($a_val) {
		$this->LifetimeOfContentinMonth = $a_val;
	}


	/**
	 * getLifetimeOfContentinMonth
	 * @return int
	 */
	public function getLifetimeOfContentinMonth() {
		return $this->LifetimeOfContentinMonth;
	}


	/**
	 * setDepartment
	 * @param string $a_val
	 */
	public function setDepartment($a_val) {
		$this->department = $a_val;
	}


	/**
	 * getDepartment
	 * @return string
	 */
	public function getDepartment() {
		return $this->department;
	}


	/**
	 * setDisciplineId
	 * @param int $a_val
	 */
	public function setDisciplineId($a_val) {
		$this->discipline_id = $a_val;
	}


	/**
	 * getDisciplineId
	 * @return int
	 */
	public function getDisciplineId() {
		return $this->discipline_id;
	}


    /**
     * setLicense
     * @param string $a_val
     */
    public function setLicense($a_val) {
        $this->license = $a_val;
    }


    /**
     * getLicense
     * @return string
     */
    public function getLicense() {
        return $this->license;
    }


    /**
     *
     * @param type $a_val
     */
	public function setTemplateId($a_val) {
		$this->template_id = (int)$a_val;
	}


    /**
     *
     * @return integer
     */
	public function getTemplateId() {
		return $this->template_id;
	}


	/**
	 * setInvitingPossible
	 * @param int $a_val
	 */
	public function setInvitingPossible($a_val) {
		$this->inviting_possible = $a_val;
	}


	/**
	 * getInvitingPossible
	 * @return int
	 */
	public function getInvitingPossible() {
		return $this->inviting_possible;
	}


	/**
	 * setIvt
	 * @param int $a_val
	 */
	public function setIvt($a_val) {
		$this->ivt = $a_val;
	}


	/**
	 * getIvt
	 * @return int
	 */
	public function getIvt() {
		return $this->ivt;
	}


	/**
	 * setUploadForm
	 * @param string $a_val
	 */
	public function setUploadForm($a_val) {
		$this->upload_form = $a_val;
	}


	/**
	 * getUploadForm
	 * @return string
	 */
	public function getUploadForm() {
		return $this->upload_form;
	}


	/**
	 * setEditLink
	 * @param string $a_val
	 */
	public function setEditLink($a_val) {
		$this->edit_link = $a_val;
	}


	/**
	 * getEditLink
	 * @return string
	 */
	public function getEditLink() {
		return $this->edit_link;
	}


	/**
	 * @return boolean
	 */
	public function getAllowAnnotations() {
		return $this->allow_annotations;
	}


	/**
	 * @param string $organization_domain
	 */
	public function setOrganizationDomain($organization_domain) {
		$this->organization_domain = $organization_domain;
	}


	/**
	 * @param boolean $allow_annotations
	 */
	public function setAllowAnnotations($allow_annotations) {
		$this->allow_annotations = $allow_annotations;
	}


    /**
     * @param string $kind periodic|test
     */
    public function setChannelKind($kind) {
        $this->channel_kind = $kind;
    }


    /**
     * return string $kind
     */
    public function getChannelKind() {
        return $this->channel_kind;
    }


}


