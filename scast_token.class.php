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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/switchcast/scast_log.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');


class scast_token {


    /**
     * log everything in a log file?
     */
	const DEV = true;


	/**
	 * Decode public-key-encrypted and base64-encoded token into channel_id/clip_id/plain_token
     *
	 * @param string $encrypted_token_base64 encrypted token in base64 form
	 * @return array decrypted token bits if succesful
	 */
	static function ext_auth_decode_encrypted_token($encrypted_token_base64) {

        if (self::DEV) {
            scast_log::write('RECEIVED ENCRYPTED_TOKEN : ' . $encrypted_token_base64);
        }

        try {
            $encrypted_token = base64_decode($encrypted_token_base64);
        }
        catch (Exception $e) {
            print_error('error_decoding_token', 'switchcast', '', $e->getMessage() . $e->getCode());
            scast_log::write('ERROR ENCRYPTED_TOKEN');
        }

        $private_key = openssl_get_privatekey('file://'.scast_obj::getValueByKey('serverkey_file'), scast_obj::getValueByKey('serverkey_password'));
        if ($private_key === false) {
            print_error('error_opening_privatekey', 'switchcast', '', 'file:/'.scast_obj::getValueByKey('castkey_file'));
        }

        openssl_private_decrypt($encrypted_token, $decrypted_token, $private_key);

		if(self::DEV) {
            scast_log::write('DECRYPTED_TOKEN : ' . $decrypted_token);
		}

		// Token structure: <channel_id>::<clip_id>::<plain_token>
		$parts = explode("::", $decrypted_token);

		if (count($parts) == 3) {
			return array (
				'channel_id'    => $parts[0],
				'clip_id'       => $parts[1],
				'plain_token'   => $parts[2]
			);
		}
		else {
            scast_log::write('ERROR : TOKEN UNDECIPHERABLE');
            print_error('error_decrypting_token', 'switchcast', '', $decrypted_token);
            return null;
		}
	}


	/**
     * Redirects to the proper SWITCHcast VOD URL with plain token
     *
	 * @param string $redirect_url
	 * @param string $plain_token
	 */
	static function ext_auth_redirect_to_vod_url($redirect_url, $plain_token) {
		if (strpos($redirect_url, 'token=::plain::') !== false) {
			// URL format: https://cast.switch.ch/vod/clip.url?token=::plain::
			$redirect_url = str_replace('::plain::', urlencode($plain_token), $redirect_url);
		}
		elseif (strpos($redirect_url, '?') === false) {
			// URL format: https://cast.switch.ch/vod/clip.url
			$redirect_url .= '?token=' . urlencode($plain_token);
		}
		else {
			// URL format: https://cast.switch.ch/vod/clip.url?param=value
			$redirect_url .= '&token=' . urlencode($plain_token);
		}
		// Perform HTTP redirect
        if (self::DEV) {
            scast_log::write('REDIRECTING TO : ' . $redirect_url);
        }
		header('Location:' . $redirect_url);
	}

}


