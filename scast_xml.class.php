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

require_once($CFG->dirroot.'/mod/switchcast/scast_obj.class.php');
require_once($CFG->dirroot.'/mod/switchcast/scast_log.class.php');


class scast_xml {


    /**
     * Sends an XMP API request to the SwitchCast server
     *
     * @param string $request_url the API call URL
     * @param string $request_type request type
     * @param SimpleXMLElement $request_xml input XML
     * @param boolean $return_as_xml return raw XML?
     * @param boolean $usecache try to use cache?
     * @param string $a_file video file to upload
     * @return boolean|\SimpleXMLElement result or false if error
     */
	static function sendRequest($request_url, $request_type, $request_xml = NULL, $return_as_xml = false, $usecache = true, $a_file = NULL) {

        global $CFG;

        $cache_time = scast_obj::getValueByKey('xml_cache_time');
        $cache_dir = $CFG->dataroot.'/cache/mod_switchcast';
        if ($request_type !== 'GET') {
            // a modification has been made, clear the cache for consistency
            $reason = $request_type . ' ' . $request_url;
            $matches = false;
            if (preg_match('/\/channels\/([0-9a-zA-Z]+)/', $request_url, $matches)) {
                scast_log::write("CACHE : destroying cache for " . $matches[1] . " because " . $reason);
                self::clear_cache($cache_dir, $matches[1]);
            }
            else {
                // No need to destroy the cache, the requests not containning "/channel/xyzxyz" have no effect on clip/channel metadata
//                scast_log::write("CACHE : destroying entire cache because " . $reason);
//                self::clear_cache($cache_dir);
            }
        }
        if (!file_exists($cache_dir)) {
            scast_log::write("CACHE : initializing empty cache");
            mkdir($cache_dir);
        }

		if (is_array($request_xml)) {
			$request_xml = self::arrayToXML($request_xml['root'], $request_xml['data']);
		}

        scast_log::write("REQUEST ". $request_type." ".$request_url);
        scast_log::write("INPUT ". $request_xml);

        $cache_filename = $cache_dir.'/'. self::hashfilename($request_url);

        if (    $usecache
                && (string)$request_type === 'GET'
                && $cache_time && $cache_dir
                && file_exists($cache_filename)
                && (time() - filemtime($cache_filename) < $cache_time)
            ) {
            // use the appropriate cached file
            scast_log::write("CACHE : using cached file ".$cache_filename);
            $output = file_get_contents($cache_filename);
        }
        else {
            // no cache for this request
            scast_log::write("CACHE : no cached file");

            libxml_use_internal_errors(true);

            $ch = curl_init();

            if (isset($a_file)) {
                if (!filesize($a_file) || !is_readable($a_file)) {
                    scast_log::write("CURL UPLOAD ERROR : empty or unreadable file");
                    throw new moodle_exception('uploaderror', 'switchcast');
                }
                $fh = fopen($a_file, "rb");
                if (!$fh) {
                    scast_log::write("CURL UPLOAD ERROR : unable to open file");
                    throw new moodle_exception('uploaderror', 'switchcast');
                }
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300000);
                curl_setopt($ch, CURLOPT_PUT, true); // must be set, elsewise the multipart info will also be sent
                curl_setopt($ch, CURLOPT_INFILE, $fh);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, (bool)scast_obj::getValueByKey('logging_enabled'));
            }
            else {
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, (int)scast_obj::getValueByKey('curl_timeout')*1000);
            }

            curl_setopt($ch, CURLOPT_CAINFO, scast_obj::getValueByKey('cacrt_file'));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSLCERT, scast_obj::getValueByKey('crt_file'));
            curl_setopt($ch, CURLOPT_SSLKEY, scast_obj::getValueByKey('castkey_file'));
            if(scast_obj::getValueByKey('castkey_password')) {
                curl_setopt($ch, CURLOPT_SSLKEYPASSWD, scast_obj::getValueByKey('castkey_password'));
            }
            curl_setopt($ch, CURLOPT_URL, $request_url);
            if(scast_obj::getValueByKey('curl_proxy')) {
                curl_setopt($ch, CURLOPT_PROXY, scast_obj::getValueByKey('curl_proxy'));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!$a_file) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request_xml);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_type);

            $output = curl_exec($ch);
            $curl_errno = curl_errno($ch); // 0 if fine

            if (isset($a_file)) {
                scast_log::write("CURL UPLOAD ERROR : no. ".$curl_errno);
                fclose($fh);
                curl_close($ch);
                if ($curl_errno) {
                    scast_log::write("                  : ".$output);
                    throw new moodle_exception('uploaderror', 'switchcast');
                }
                return basename($a_file);
            }

            curl_close($ch);

            if ($output && (string)$request_type === 'GET' && $cache_time && $cache_dir && is_writable($cache_dir)) {
                // write cache to file
                scast_log::write("CACHE : writing output to cache file ".$cache_filename);
                $fh_w = fopen($cache_filename, 'w');
                fwrite($fh_w, $output);
                fclose($fh_w);
                if (strstr($request_url, 'clips.xml?full=true') !== false) {
                    // we're getting full clip matadata (woohoo!), so let's fill in the cache
                    // on these clips, before making any further API calls.
                    $channelfull = new SimpleXMLElement($output);
                    foreach ($channelfull as $clipxml) {
                        $clip_request_url = preg_replace('/^(.*)clips\.xml\?full=true.*/', '\1clips/'.$clipxml->ext_id.'.xml', $request_url);
                        $cache_clip_filename = $cache_dir.'/'.  self::hashfilename($clip_request_url);
                        $fh_w = fopen($cache_clip_filename, 'w');
                        fwrite($fh_w, $clipxml->asXML());
                        fclose($fh_w);
                    }
                }
            }
        }


		if($return_as_xml) {
			return $output;
		}

		if ($output === false) {
            if ($curl_errno) {
                scast_log::write("CURL REQUEST ERROR : no. ".$curl_errno);
            }
			print_error('switch_api_down', 'switchcast');
			return false;
		}

        scast_log::write("OUTPUT ". $output);

		try {
            $return = new SimpleXMLElement($output);
		}
        catch (Exception $e) {
            $sxe = simplexml_load_string($output);
            if ($sxe === false) {
//                header("Content-type: text/plain");
                $sxe = "Failed loading XML\n";
                foreach(libxml_get_errors() as $error) {
                    $sxe .= "\t".$error->message;
                }
                $sxe .= "\n\n";
                $sxe .= $output;
//                echo $sxe;
            }
            print_error('xml_fail', 'switchcast', null, $e->getMessage() . $e->getCode());
			return false;
		}

        // Falls das Return-Objekt eine Mesage enthält so, ist etwas schief gelaufen.
		if ($return->message && strpos($return->message, 'success') === false) {
            if (isset($return->code)) {
                print_error((string)$return->code, 'switchcast');
                return false;
            }
            print_error('xml_fail', 'switchcast', null, (string)$return->message);
			return false;
		}

		return $return;
	}


	/*
	 * arrayToXML
	 */
	static function arrayToXML($a_base, array $a_data) {
		if($a_base) {
			$xObj = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><" . $a_base . " />");
			foreach ($a_data as $key => $value) {
				if (is_string($value)) {
					$value = htmlspecialchars($value, ENT_XML1, 'UTF-8');
				}

				$xObj->addChild($key, $value);
			}

			return $xObj->asXML();
		}
	}


    /**
     * Delete a directory recursive with files inside
     *
     * @param string $dirname
     * @return bool
     */
    static function clear_cache($dirname, $filter = false) {
        if (!file_exists($dirname)) {
            return false;
        }
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            if ($filter !== false && strstr($entry, $filter) === false) {
                continue;
            }
            unlink($dirname . DIRECTORY_SEPARATOR . $entry);
        }
        $dir->close();
        return true;
    }


    /**
     *
     */
    static function hashfilename($url = '') {
        $f = str_replace(scast_obj::getValueByKey('switch_api_host'), '', $url);
        $f = str_replace(scast_obj::getValueByKey('default_sysaccount'), '', $f);
        $f = preg_replace('/[^a-zA-Z0-9]/', '_', $f);
        $f = preg_replace('/^(_)+/', '', $f);
//        return sha1($f);
        return $f;
    }


}

