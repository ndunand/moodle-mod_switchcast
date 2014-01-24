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
 * @author     Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author     Martin Studer <ms@studer-raimann.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/switchcast/scast_xml.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');


class scast_user {


    /**
     * Constructor
     *
     * @param string $aaiUniqueId AAI unique ID if known
     * @param int $moodleUserId Moodle user ID if not current user
     */
    public function __construct($aaiUniqueId = '', $moodleUserId = 0) {
        global $DB, $USER;

        if ($moodleUserId !== 0) {
            $userid = $moodleUserId;
        }
        else {
            $userid = $USER->id;
        }
        if ($aaiUniqueId == '') {
            $aaiUniqueId = self::getExtIdFromMoodleUserId($userid);
        }
        else if ($moodleUserId) {
            // make sure provided AAI unique ID is correct for actual user
            if (self::getMoodleUserIdFromExtId($aaiUniqueId) !== $userid) {
                print_error('aaiid_vs_moodleid', 'switchcast');
            }
        }

		$this->switch_api = scast_obj::getValueByKey('switch_api_host');
		$this->setExternalAccount($aaiUniqueId);

	}


    /**
     * Returns a SWITCHaai unique ID if one is found with the provided Moodle user ID
     *
     * @param int $userid Moodle user ID
     * @return string SWITCHaai unique ID
     */
    public static function getExtIdFromMoodleUserId($userid = 0) {
        global $DB;
        if (! $user = $DB->get_record('user', array('id' => $userid))) {
            return '';
        }
        $uid_field = scast_obj::getValueByKey('uid_field');
        if (strpos($uid_field, '::') !== false) {
            $params = explode('::', $uid_field);
            $table = $params[0];
            $fieldid = $params[1];
            $u = $DB->get_record($table, array('userid' => $user->id, 'fieldid' => (int)$fieldid));
            if ($u) {
                return $u->data;
            }
        }
        else {
            return $user->$uid_field;
        }
        return '';
    }


    /**
     * Returns a Moodle user ID if one is found with the provided SWITCHaai unique ID
     *
     * @param string $ext_id SWITCHaai unique ID
     * @return int Moodle user ID
     */
    public static function getMoodleUserIdFromExtId($ext_id = '') {
        global $DB;
        $moodleid = false;
        if ($ext_id === '') {
            return false;
        }
        $uid_field = scast_obj::getValueByKey('uid_field');
        if (strpos($uid_field, '::') !== false) {
            $params = explode('::', $uid_field);
            $table = $params[0];
            $fieldid = $params[1];
            $u = $DB->get_record_select($table, $DB->sql_compare_text('data') . ' = \'' . (string)$ext_id . '\' AND fieldid = ' . (int)$fieldid);
            if ($u) {
                $moodleid = $u->userid;
            }
        }
        else {
            $u = $DB->get_record('user', array($uid_field => (string)$ext_id));
            if ($u) {
                $moodleid = $u->id;
            }
        }
        return (int)$moodleid;
    }


    /**
     * Checks if two users are in the same Moodle group, returns true if:
     *  - mode is "no groups"
     *  - mode is "visible groups"
     *  - mode is "separate groups" and users are in the same group
     *
     * @param int $userid1 Moodle user ID of user 1
     * @param int $userid2 Moodle user ID of user 2
     * @return boolean see method description
     */
    public static function checkSameGroup($userid1, $userid2) {
        global $CFG, $course, $cm;
        if (!$userid1 || !$userid2) {
            // make sure we have two actual user ID's
            return false;
        }
        require_once($CFG->dirroot.'/group/lib.php');
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode == NOGROUPS) {
            // activity mode is "no groups", so people can't be in the same group
            return false;
        }
        else if ($groupmode == VISIBLEGROUPS) {
            // Impossible to restrict to nos use this particular setting in course AJAX editing, so if VISIBLEGROUPS is set, everyone sees everything.
            return true;
        }
        else if ($groupmode == SEPARATEGROUPS) {
            if (!groups_has_membership($cm, $userid1)) {
                return false;
            }
            $groups_user1 = groups_get_all_groups($course->id, $userid1);
            foreach ($groups_user1 as $group_user1) {
                if (groups_is_member($group_user1->id, $userid2)) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Creates a new user on the SWITCHcast server
     *
     * @return SimpleXMLElement
     */
	public function doCreate() {
		global $USER;

		$data = array(
			'root' => 'user',
			'data' => array('firstname' => $USER->firstname,
				'lastname' => $USER->lastname,
				'login' => $this->getExternalAccount(),
				'email' => $USER->email,
				'organization_domain' => scast_obj::getOrganizationByEmail($this->getExternalAccount())
			)
		);

		$url = $this->switch_api;
		$url .= '/users.xml';

		$scastNewUserObj = scast_xml::sendRequest($url, 'POST', $data);

		return $scastNewUserObj;
	}


    /**
     * Reads user data from the SWITCHcast server
     *
     */
	public function doRead() {

        if(!$this->getExternalAccount()) {
            return;
        }

		$url =  $this->switch_api;
		$url .= '/users/'.$this->getExternalAccount().'.xml?full=true';

		$simplexmlobj = scast_xml::sendRequest($url, 'GET');

		$this->setLastName((string)$simplexmlobj->lastname);
		$this->setFirstName((string)$simplexmlobj->firstname);
        $this->setEmail((string)$simplexmlobj->email);
		$this->setExternalAccount((string)$simplexmlobj->login);
	}


	/**
	 * getChannels
     *
	 */
	public function getChannels() {

        if (!$this->getExternalAccount()) {
            // prevent getting an error if we're trying to get a non-AAI user's
            // channels by mistake
            return array();
        }

		$url =  $this->switch_api;
		$url .= '/users/'.$this->getExternalAccount().'/channels.xml';
		$simplexmlobj = scast_xml::sendRequest($url, 'GET');

		return $simplexmlobj;
	}


	/**
	 * setFirstName
     *
	 */
	public function setFirstName($a_val) {
		$this->firstName = $a_val;
	}


	/**
	 * getFirstName
     *
	 */
	public function getFirstName() {
		return $this->firstName;
	}


	/**
	 * setLastName
     *
	 */
	public function setLastName($a_val) {
		$this->lastName = $a_val;
	}


	/**
	 * getLastName
     *
	 */
	public function getLastName() {
		return $this->lastName;
	}


	/**
	 * setExternalAccount
     *
     * @param $a_val
	 */
	public function setExternalAccount($a_val) {
		$this->external_account = $a_val;
	}


	/**
	 * getExternalAccount
     *
	 */
	public function getExternalAccount() {
		return $this->external_account;
	}


	/**
	 * setEmail
     *
	 */
	public function setEmail($a_val) {
		$this->email = $a_val;
	}


	/**
	 * getEmail
     *
	 */
	public function getEmail() {
		return $this->email;
    }


}

