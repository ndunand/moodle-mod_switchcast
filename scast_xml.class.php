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
     * @param string $a_url the API call URL
     * @param string $a_request request type
     * @param SimpleXMLElement $a_xml input XML
     * @param boolean $as_xml return raw XML?
     * @param boolean $nocache bypass cache?
     * @return boolean|\SimpleXMLElement result or false if error
     */
	static function sendRequest($a_url, $a_request, $a_xml = NULL, $as_xml = false, $usecache = true) {

        global $CFG;

        $cache_time = scast_obj::getValueByKey('xml_cache_time');
        $cache_dir = $CFG->dataroot.'/cache/mod_switchcast';
        if ($a_request !== 'GET') {
            // a modification has been made, clear the cache for consistency
            scast_log::write("CACHE : destroying cache");
            self::rmdirr($cache_dir);
        }
        if (!file_exists($cache_dir)) {
            scast_log::write("CACHE : initializing empty cache");
            mkdir($cache_dir);
        }

		if (is_array($a_xml)) {
			$a_xml = self::arrayToXML($a_xml['root'], $a_xml['data']);
		}

        scast_log::write("REQUEST ". $a_request." ".$a_url);
        scast_log::write("INPUT ". $a_xml);

        $cache_filename = $cache_dir.'/'.sha1("REQUEST ". $a_request." ".$a_url."INPUT ". $a_xml);

        if (    $usecache
                && (string)$a_request === 'GET'
                && $cache_time && $cache_dir
                && file_exists($cache_filename)
                && (time() - filemtime($cache_filename) < $cache_time)
            ) {
            // use the appropriate cached file
            scast_log::write("CACHE : using cached file");
            $output = file_get_contents($cache_filename);
        }
        else {
            // no cache for this request
            scast_log::write("CACHE : no cached file");
            //error_reporting(0);
            libxml_use_internal_errors(true);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CAINFO, scast_obj::getValueByKey('cacrt_file'));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSLCERT, scast_obj::getValueByKey('crt_file'));
            curl_setopt($ch, CURLOPT_SSLKEY, scast_obj::getValueByKey('castkey_file'));
            if(scast_obj::getValueByKey('castkey_password')) {
                curl_setopt($ch, CURLOPT_SSLKEYPASSWD, scast_obj::getValueByKey('castkey_password'));
            }
            curl_setopt($ch, CURLOPT_URL, $a_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $a_xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $a_request);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);

            $output = curl_exec($ch);
            curl_close($ch);

            if ((string)$a_request === 'GET' && $cache_time && $cache_dir && is_writable($cache_dir)) {
                // write cache to file
                scast_log::write("CACHE : writing output to cache file ".$cache_filename);
                $fh_w = fopen($cache_filename, 'w');
                fwrite($fh_w, $output);
                fclose($fh_w);
            }
        }


		if($as_xml) {
			return $output;
		}

		if ($output === false) {
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
                header("Content-type: text/plain");
                $sxe = "Failed loading XML\n";
                foreach(libxml_get_errors() as $error) {
                    $sxe .= "\t".$error->message;
                }
                $sxe .= "\n\n";
                $sxe .= $output;
                echo $sxe;
                error_log($sxe, 3, $CFG->dataroot.'/mod/switchcast/error.log');
                die();
            }
            print_error('xml_fail', 'switchcast', null, $e->getMessage() . $e->getCode());
			return false;
		}

        //Falls das Return-Objekt eine Mesage enthält so, ist etwas schief gelaufen.
		if ($return->message && strpos($return->message, 'success') === false) {
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
					$value = htmlentities($value);
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
    static function rmdirr($dirname) {
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
            self::rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }
        $dir->close();
        return rmdir($dirname);
    }
}

