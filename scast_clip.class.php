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

require_once($CFG->dirroot.'/mod/switchcast/scast_user.class.php');

define('SWITCHCAST_CLIP_UPLOADED', 1);
define('SWITCHCAST_CLIP_READY', 2);
define('SWITCHCAST_CLIP_TRYAGAIN', 3);
define('SWITCHCAST_STALE_PERIOD', 3600*6);


class scast_clip {

    /**
     * @var scast_obj SWITCHcast channel containing the clip
     *
     */
    private $scast_obj;


    /**
     * @var array list of the clip's invited members
     *
     */
    private $members;


    /**
     * Constructor
     *
     * @param scast_obj $a_obj_scast SWITCHcast channel the clip belongs to
     * @param string $clip_ext_id clip ID as SWITCHcast
     */
    public function __construct(scast_obj $a_obj_scast, $clip_ext_id, $isempty = false, $switchcast_id = 0) {
        if (!$isempty) {
            $this->scast_obj = $a_obj_scast;
            $this->switchcast_id = $switchcast_id;
    //		$this->obj_id = $a_obj_scast->obj_id;
            $this->channel_ext_id = $a_obj_scast->getExtId();
            $this->sys_account = $a_obj_scast->getSysAccount();
            $this->members = array();
            $this->setExtId($clip_ext_id);
            $this->setChannelEditLink($a_obj_scast->getEditLink());

            $this->doRead();
        }
	}


    /**
     * Reads clip data from the SWITCHcast server
     *
     */
	public function doRead() {
		global $DB;

        $url =  $this->scast_obj->getValueByKey('switch_api_host')."/vod";
		$url .= "/users/".$this->sys_account;
		$url .= "/channels";
		$url .= "/".$this->channel_ext_id;
		$url .= "/clips";
		$url .= "/".$this->getExtId().".xml";

		$simplexmlobj = scast_xml::sendRequest($url, "GET");

		$this->setExtId((string) $simplexmlobj->ext_id);
		$this->setOwner((string) $simplexmlobj->ivt__owner);
		$this->setTitle((string) $simplexmlobj->title);
		$this->setSubtitle((string) $simplexmlobj->subtitle);
		$this->setLocation((string) $simplexmlobj->location);
		$this->setPresenter((string) $simplexmlobj->presenter);
//		$this->setRecordingDate((string) $simplexmlobj->recording_date);
		$this->setRecordingDate((string) $simplexmlobj->issued_on);
		$this->setSortableRecordingDate((string) $simplexmlobj->recording_date);
        $this->setRecordingStation((string) $simplexmlobj->ivt__recordingstation);
        $this->setLinkMov($this->getUrlFor($simplexmlobj, 'QuickTime'));
        $this->setLinkM4v($this->getUrlFor($simplexmlobj, 'iPod'));
        $this->setCover($this->getUrlFor($simplexmlobj, 'Cover image'));
        $this->setAnnotationLink($this->getUrlFor($simplexmlobj, 'Annotate clip'));

        // BugFix IVT-Streaming
        // TODO : FIXME Sobald channel-templates neu gerechnet fixen
        $this->setLinkFlash(str_replace('mp4', 'html', $this->getUrlFor($simplexmlobj, 'Flash')));

		$members = $DB->get_records('switchcast_cmember', array('clip_ext_id' => $this->getExtId()));
		foreach ($members as $member) {
            $this->setMember($member->userid);
		}
	}


    /**
     * Gets various URLs from a clip's XML
     *
     * @param SimpleXMLElement $simplexmlobj XML for a clip
     * @param type $label label of the URL within the XML
     * @return string the URL
     */
    private function getUrlFor(SimpleXMLElement $simplexmlobj,  $label = '') {
        global $CFG;
        foreach ($simplexmlobj->urls->url as $url) {
            if (((string) $url['label']) == $label) {
                if (in_array($label, array('Flash', 'QuickTime', 'iPod', 'Annotate clip'))) {
                    $link  = $CFG->wwwroot . '/mod/switchcast/goTo.php';
                    $link .= '?url=' . base64_encode( (string)$url );
                    $link .= '&swid=' . $this->switchcast_id;
                    $link .= '&tk=' . sha1( scast_obj::getValueByKey('default_sysaccount') . $this->switchcast_id . (string)$url );
                    return $link;
                }
                return (string)$url;
            }
        }
    }


    /**
     * Updates the clip's owner at SWITCHCast server
     *
     * @return bool true if succesful
     */
    public function doUpdate() {

		$url =  $this->scast_obj->getValueByKey('switch_api_host');
		$url .= '/users/'.$this->scast_obj->getSysAccount();
		$url .= '/channels';
		$url .= '/'.$this->channel_ext_id;
		$url .= '/clips';
		$url .= '/'.$this->getExtId().'.xml';


		$simplexmlobj = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><clip />');
		$simplexmlobj->addChild('title', htmlspecialchars($this->getTitle(), ENT_XML1, 'UTF-8'));
		$simplexmlobj->addChild('subtitle', htmlspecialchars($this->getSubtitle(), ENT_XML1, 'UTF-8'));
		$simplexmlobj->addChild('presenter', htmlspecialchars($this->getPresenter(), ENT_XML1, 'UTF-8'));
		$simplexmlobj->addChild('location', htmlspecialchars($this->getLocation(), ENT_XML1, 'UTF-8'));
		$simplexmlobj->addChild('ivt__owner', htmlspecialchars($this->getOwner(), ENT_XML1, 'UTF-8'));

		scast_xml::sendRequest($url, 'PUT', $simplexmlobj->asXML()); // TODO mit neuer methode
		return true;
	}


    /**
     * Deletes the clip at SWITCHcast server
     *
     * @return boolean true if succesful
     */
    public function doDelete() {
        global $DB;

        $sysaccount = scast_obj::getSysAccountByOrganization($this->scast_obj->getOrganization(), true);

        if (!$sysaccount) {
            return false;
        }

        // we only delete clips if we actually have a $sysaccount for this channel

        $url =  $this->scast_obj->getValueByKey('switch_api_host');
        $url .= "/users/".$sysaccount;
        $url .= "/channels";
        $url .= "/".$this->channel_ext_id;
        $url .= "/clips";
        $url .= "/".$this->getExtId().".xml";

        //Clip auf Ebene SWITCHcast löschen
        $simplexmlobj = scast_xml::sendRequest($url, "DELETE");

        //Sämtliche Members entfernen
        $DB->delete_records('switchcast_cmember', array('clip_ext_id' => $this->getExtId()));

        return true;
    }


	/**
	 * Checks the current USER's permission on the clip
	 *
	 * @param string $a_perm permission : 'read' or 'write'
	 * @return bool true if permission granted
	 */
	public function checkPermissionBool($a_perm) {
		global $DB, $USER, $context;

        if (! has_capability('mod/switchcast:use', $context) ) {
            return false;
        }

        $scast_user = new scast_user();
        $user_uploaded_clips = $DB->get_records('switchcast_uploadedclip', array('userid' => $USER->id));
        $user_uploaded_clips_extids = array();
        if (is_array($user_uploaded_clips)) {
            foreach ($user_uploaded_clips as $user_uploaded_clip) {
                $user_uploaded_clips_extids[] = $user_uploaded_clip->ext_id;
            }
        }

		if ($a_perm == 'write') {
            if (    has_capability('mod/switchcast:isproducer', $context)
                    || ( ( $scast_user->getExternalAccount() == $this->getOwner() ) && $this->getOwner() !== '' )
                    || in_array($this->getExtId(), $user_uploaded_clips_extids)
                ) {
                /*
                 * the current $USER is channel producer
                 * OR the current $USER is the clip owner
                 * OR the current $USER is the user who uploaded the clip
                 */
				return true;
			}
		}
		else if ($a_perm == 'read') {
            if (
                    ( has_capability('mod/switchcast:isproducer', $context) )
                    || ( has_capability('mod/switchcast:seeallclips', $context) )
                    || ( $this->scast_obj->getIvt() && $this->getOwner() !== '' && ( $scast_user->getExternalAccount() == $this->getOwner() ) )
                    || ( $this->scast_obj->getIvt() == false )
                    || ( $this->scast_obj->getIvt() == true && $this->scast_obj->getInvitingPossible() == true && is_numeric(array_search($USER->id, $this->getMembers())) )
                    || ( scast_user::checkSameGroup(scast_user::getMoodleUserIdFromExtId($this->getOwner()), $USER->id) )
                    || in_array($this->getExtId(), $user_uploaded_clips_extids)
                ) {
                /*
                 * the current $USER is channel producer
                 * the current $USER has the mod/switchcast:seeallclips capability
                 * OR activity is set in individual mode AND the current $USER is the clip owner
                 * OR there are no individual clip permissions set for this activity
                 * OR activity is set in individual mode AND $USER is an invited member of a clip
                 * OR is in the same user group as the clip owner
                 * OR the current $USER is the user who uploaded the clip
                 */
                return true;
            }

		}

		return false;
	}


    /**
     * Adds a member (invitation) to the clip
     *
     * @param int $user_id Moodle user ID
     * @return boolean true if member added
     */
    public function addMember($user_id, $course_id, $switchcast_id) {
		global $DB, $context;

        $user_id = (int)$user_id;
        if (in_array($user_id, $this->getMembers())) {
            return false;
        }
        if (!has_capability('mod/switchcast:use', $context, $user_id)) {
            return false;
        }

        $insert = new stdClass();
        $insert->userid = $user_id;
        $insert->clip_ext_id = $this->getExtId();
        $insert->courseid = $course_id;
        $insert->switchcastid = $switchcast_id;
        $DB->insert_record('switchcast_cmember', $insert);

        $this->setMember($user_id);
		return true;
	}


    /**
     * Removes a member (invitation) from the clip
     *
     * @param int $user_id Moodle user ID
     */
	public function deleteMember($user_id, $course_id, $switchcast_id) {
		global $DB;
        $DB->delete_records('switchcast_cmember', array('clip_ext_id' => $this->getExtId(), 'userid' => $user_id, 'courseid' => $course_id, 'switchcastid' => $switchcast_id));
        $this->delMember($user_id);
	}


    /**
     * Checks whether a user is member of the clip
     *
     * @param int $a_userid Moodle user ID
     * @return bool true if the user is a member
     */
    public function isMember($a_userid) {
        return in_array($a_userid, $this->getMembers());
    }


    /**
     *
     * @return array of Moodle user IDs
     */
    public function getMembers() {
		return $this->members;
	}


    /**
     *
     * @param int $a_id a Moodle user ID
     */
    public function setMember($a_id) {
		$this->members[] = (int)$a_id;
	}


	/**
	 * removes a Member from the scast_clip object
     *
	 * @param int $a_id a Moodle user ID
	 */
	public function delMember($a_id) {
        $newmembers = array();
        foreach ($this->members as $member) {
            if ((int)$member !== (int)$a_id) {
                $newmembers[] = $member;
            }
        }
		$this->members = $newmembers;
	}


    public function setChannelEditLink($a_channel_edit_link) {
        $this->channel_edit_link =  $a_channel_edit_link;
    }


    public function getChannelEditLink() {
        return $this->channel_edit_link;
    }


	public function setExtId($a_val) {
		$this->ext_id = $a_val;
	}


	public function getExtId() {
		return $this->ext_id;
	}


	public function setTitle($a_val) {
		$this->title = $a_val;
	}


	public function getTitle() {
		return $this->title;
	}


	public function setCover($a_val) {
		$this->cover = $a_val;
	}


	public function getCover() {
		return $this->cover;
	}


    public function setAnnotationLink($a_val) {
        $this->AnnotationLink = $a_val;
    }


    public function getAnnotationLink() {
        return $this->AnnotationLink;
    }


	public function setStreamingHtml($a_val) {
		$this->streaming_html = $a_val;
	}


	public function getStreamingHtml() {
		return $this->streaming_html;
	}


	public function setLinkBox($a_val) {
		$this->link_box = $a_val;
	}


	public function getLinkBox() {
 		return $this->link_box;
	}


    public function setLinkFlash($a_val) {
        $this->linkflash = $a_val;
    }


    public function getLinkFlash() {
        return $this->linkflash;
    }


    public function setLinkMp4($a_val) {
        $this->linkmp4 = $a_val;
    }


    public function getLinkMp4() {
        return $this->linkmp4;
    }


    public function setLinkMov($a_val) {
        $this->linkmov = $a_val;
    }


    public function getLinkMov() {
        return $this->linkmov;
    }


    public function setLinkM4v($a_val) {
        $this->linkm4v = $a_val;
    }


    public function getLinkM4v() {
        return $this->linkm4v;
    }


	public function setSubtitle($a_val) {
		$this->subtitle = $a_val;
	}


	public function getSubtitle() {
		return $this->subtitle;
	}


	public function setPresenter($a_val) {
		$this->presenter = $a_val;
	}


	public function getPresenter() {
		return $this->presenter;
	}


	public function setOwner($a_val) {
		$this->owner = $a_val;
	}


	public function getOwner() {
		return $this->owner;
	}


	public function setLectureDate($a_val) {
		$this->lecture_date = $a_val;
	}


	public function getLectureDate() {
		return $this->lecture_date;
	}


	public function setLocation($a_val) {
		$this->location = $a_val;
	}


	public function getLocation() {
		return $this->location;
	}


	public function setDownloadlinks($a_val) {
		$this->downloadlinks = $a_val;
	}


	public function getDownloadlinks() {
		return $this->downloadlinks;
	}


    public function setRecordingStation($a_val) {
        $this->recordingstation = $a_val;
    }


    public function getRecordingStation() {
        return $this->recordingstation;
    }


    public function setRecordingDate($a_val) {
        $this->recordingdate = $a_val;
    }


    public function getRecordingDate() {
        return $this->recordingdate;
    }


    public function setSortableRecordingDate($a_val) {
        $this->sortablerecordingdate = $a_val;
    }


    public function getSortableRecordingDate() {
        return $this->sortablerecordingdate;
    }


    /**
     * Gets the Moodle user IUD of the clip's owner
     *
     * @return int a Moodle user ID
     */
    public function getOwnerUserId() {
        global $DB;

        if (!$this->getOwner()) {
            // owner not defined
            return false;
        }

        $uid_field = $this->scast_obj->getValueByKey('uid_field');
        if (strpos($uid_field, '::') !== false) {
            $params = explode('::', $uid_field);
            $table = $params[0];
            $fieldid = $params[1];
            $u = $DB->get_record_select($table, 'fieldid = '.(int)$fieldid.' AND data = \''.(string)$this->getOwner().'\'');
            $userid = $u->userid;
        }
        else {
            $u = $DB->get_record('user', array($uid_field => $this->getOwner()));
            $userid = $u->$id;
        }
        return $userid;
    }

}


