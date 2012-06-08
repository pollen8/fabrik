<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// +----------------------------------------------------------------------+
// | Mobile user agent string parsing class for PHP5.                     |
// | Copyright (C) 2004 Craig Manley                                      |
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU Lesser General Public License as       |
// | published by the Free Software Foundation; either version 2.1 of the |
// | License, or (at your option) any later version.                      |
// |                                                                      |
// | This library is distributed in the hope that it will be useful, but  |
// | WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU     |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the Free Software  |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307  |
// | USA                                                                  |
// |                                                                      |
// | LGPL license URL: http://opensource.org/licenses/lgpl-license.php    |
// +----------------------------------------------------------------------+
// | Author: Craig Manley                                                 |
// +----------------------------------------------------------------------+
//
// $Id: MobileUserAgent.php,v 1.6 2004/12/21 15:07:29 cmanley Exp $
//



/**
 * @author    Craig Manley
 * @copyright Copyright � 2004, Craig Manley. All rights reserved.
 * @version   $Revision: 1.6 $
 * @package   com.craigmanley.classes.mobile.MobileUserAgent
 */


/**
 * Parses a mobile user agent string into it's basic constituent parts, the
 * most important being vendor and model.
 *
 * One reason for doing this would be to use this information to lookup vendor-model
 * specific device characteristics in a database. Of course you could use user agent
 * profiles for this, but not all mobile phones have (valid) user agent profiles, especially
 * the older types of mobile phones.
 *
 * Another reason would be to detect if the visiting client is a mobile handset. You could do
 * it like this:
 * <pre>
 *  require_once('MobileUserAgent.php');
 *  $mua = new MobileUserAgent();
 *  $is_client_mobile = $mua->success();
 * </pre>
 *
 * Some references:
 * <ul>
 *  <li>{@link http://www.handy-ortung.com }</li>
 *  <li>{@link http://www.mobileopera.com/reference/ua }</li>
 *  <li>{@link http://www.appelsiini.net/~tuupola/php/Imode_User_Agent/source/ }</li>
 *  <li>{@link http://www.zytrax.com/tech/web/mobile_ids.html }</li>
 *  <li>{@link http://webcab.de/wapua.htm }</li>
 *  <li>{@link http://www.nttdocomo.co.jp/english/p_s/i/tag/s2.html }</li>
 *  <li>{@link http://test.waptoo.com/v2/skins/waptoo/user.asp }</li>
 * </ul>
 *
 * @package  com.craigmanley.classes.mobile.MobileUserAgent
  */
class MobileUserAgent {

  // Private members
  private $useragent   = null;
  private $vendor      = null;
  private $model       = null;
  private $version     = null;
  private $imode_cache = null;
  private $screendims  = null;
  private $is_standard = false;
  private $is_imode    = false;
  private $is_mozilla  = false;
  private $is_rubbish  = false;
  private $is_series60 = null;

  /**
   * Constructor.
   *
   * @param string $useragent Optional useragent string. If null, environment variable HTTP_USER_AGENT is used.
   */
  function __construct($useragent = null) {
    if (!(isset($useragent) && strlen($useragent))) {
      if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        //throw new Exception('Environment variable HTTP_USER_AGENT is missing!');
        $useragent = '';
      }
      $useragent = $_SERVER['HTTP_USER_AGENT'];
    }
    $this->useragent = $useragent;
    $hash = $this->_parseUserAgent($useragent);
    if ($hash) {
      $this->vendor = $hash['vendor'];
      $this->model  = $hash['model'];
      if (isset($hash['version'])) {
        $this->version = $hash['version'];
      }
      if (isset($hash['imode_cache'])) {
        $this->imode_cache = $hash['imode_cache'];
      }
      if (isset($hash['screendims'])) {
        $this->screendims  = $hash['screendims'];
      }
    }
  }


  /**
   * Parses a standard mobile user agent string with the format vendor-model/version.
   * If no match can be made, FALSE is returned.
   * If a match is made, an associative array is returned containing the compulsory
   * keys "vendor" and "model", and the optional keys "version", and "screendims".
   *
   * Below are a few samples of these user agent strings:
   * <pre>
   *  Nokia8310/1.0 (05.57)
   *  NokiaN-Gage/1.0 SymbianOS/6.1 Series60/1.2 Profile/MIDP-1.0 Configuration/CLDC-1.0
   *  SAGEM-myX-6/1.0 UP.Browser/6.1.0.6.1.c.3 (GUI) MMP/1.0 UP.Link/1.1
   *  SAMSUNG-SGH-A300/1.0 UP/4.1.19k
   *  SEC-SGHE710/1.0
   * </pre>
   *
   * @param string $useragent User agent string.
   * @return mixed
   */
  protected function _parseUserAgentStandard($useragent) {
    // Standard vendor-model/version user agents
    if (!preg_match('/^((ACER|Alcatel|AUDIOVOX|BlackBerry|CDM|Ericsson|LG\b|LGE|Motorola|MOT|NEC|Nokia|Panasonic|QCI|SAGEM|SAMSUNG|SEC|Sanyo|Sendo|SHARP|SIE|SonyEricsson|Telit|Telit_Mobile_Terminals|TSM)[- ]?([^\/\s\_]+))(\/(\S+))?/', $useragent, $matches)) {
      return false;
    }
    $both   = $matches[1];
    $vendor = $matches[2];
    $model  = $matches[3];
    $version = null;
    if (count($matches) >= 6) {
      $version = $matches[5];
    }

    // Fixup vendors and models.
    if ($vendor == 'ACER') {
      $vendor = 'Acer';
    }
    elseif ($vendor == 'AUDIOVOX') {
      $vendor = 'Audiovox';
    }
    elseif ($vendor == 'CDM') {
      $vendor = 'Audiovox';
      $model = "CDM-$model";
    }
    elseif ($vendor == 'Ericsson') {
      if ($model == 'T68_NIL') {
        $model = 'T68';
      }
    }
    elseif (substr($vendor,0,2) == 'LG') {
      $vendor = 'LG';
      if (preg_match('/^([A-Za-z\d]+)-/', $model, $m)) { // LGE510W-V137-AU4.2
        $model = $m[1];
      }
    }
    elseif ($vendor == 'MOT') {
      $vendor = 'Motorola';
      $model = preg_replace('/[\._]$/', '', $model);
    }
    elseif ($vendor == 'PHILIPS') {
      $model = strtoupper($model);
    }
    elseif ($vendor == 'SAGEM') {
      if ($model == '-') {
        return false;
      }
    }
    elseif ($vendor == 'SEC') {
      $vendor = 'SAMSUNG';
      $model = preg_replace('/\*.*$/','',$model);
    }
    elseif ($vendor == 'SIE') {
      $vendor = 'Siemens';
    }
    elseif ($vendor == 'Telit_Mobile_Terminals') {
      $vendor = 'Telit';
    }
    elseif ($vendor == 'TSM') {
      $vendor = 'Vitelcom';
      $model = $both;
    }
    $result = array('vendor'  => $vendor,
                    'model'   => $model);
    if (isset($version)) {
      $result['version'] = $version;
    }
    return $result;
  }



  /**
   * Parses an i-mode user agent string.
   * If no match can be made, FALSE is returned.
   * If a match is made, an associative array is returned containing the compulsory
   * keys "vendor" and "model", and the optional keys "version", "imode_cache",
   * and "screendims".
   *
   * Below are a few samples of these user agent strings:
   * <pre>
   *  portalmmm/1.0 m21i-10(c10)
   *  portalmmm/1.0 n21i-10(c10)
   *  portalmmm/1.0 n21i-10(;ser123456789012345;icc1234567890123456789F)
   *  portalmmm/2.0 N400i(c20;TB)
   *  portalmmm/2.0 P341i(c10;TB)
   *  DoCoMo/1.0/modelname
   *  DoCoMo/1.0/modelname/cache
   *  DoCoMo/1.0/modelname/cache/unique_id_information
   *  DoCoMo/2.0 modelname(cache;individual_identification_information)
   * </pre>
   *
   * @param string $useragent User agent string.
   * @return mixed
   */
  protected function _parseUserAgentImode($useragent) {
    $vendors = array(
      'D'  => 'Mitsubishi',
      'ER' => 'Ericsson',
      'F'  => 'Fujitsu',
      'KO' => 'Kokusai', // Hitachi
      'M'  => 'Mitsubishi',
      'P'  => 'Panasonic', // Matsushita
      'N'  => 'NEC',
      'NM' => 'Nokia',
      'R'  => 'Japan Radio',
      'S'  => 'SAMSUNG',
      'SH' => 'Sharp',
      'SO' => 'Sony',
      'TS' => 'Toshiba',
   );
    // Standard i-mode user agents
    if (preg_match('/^(portalmmm|DoCoMo)\/(\d+\.\d+) ((' . implode('|',array_keys($vendors)) . ')[\w\-]+)\((c(\d+))?/i', $useragent, $matches)) {
      $result = array('vendor'      => $vendors[strtoupper($matches[4])],
                      'model'       => $matches[3],
                      'version'     => $matches[2]);
      if ((count($matches) == 7) && strlen($matches[6])) {
        $result['imode_cache'] = $matches[6] + 0;
      }
      else {
        $result['imode_cache'] = 5;
      }
      return($result);
    }

    // DoCoMo HTML i-mode user agents
    elseif (preg_match('/^DoCoMo\/(\d+\.\d+)\/((' . implode('|',array_keys($vendors)) . ')[\w\.\-\_]+)(\/c(\d+))?/i', $useragent, $matches)) {
      // HTML 1.0: DoCoMo/1.0/modelname
      // HTML 2.0: DoCoMo/1.0/modelname/cache
      // HTML 3.0: DoCoMo/1.0/modelname/cache/unique_id_information
      $result = array('vendor'      => $vendors[strtoupper($matches[3])],
                      'model'       => $matches[2],
                      'version'     => $matches[1]);
      if (count($matches) >= 6) {
        $result['imode_cache'] = $matches[5] + 0;
      }
      else {
        $result['imode_cache'] = 5;
      }
      return $result;
    }

    return false;


  }



  /**
   * Parses a Mozilla (so called) compatible user agent string.
   * If no match can be made, FALSE is returned.
   * If a match is made, an associative array is returned containing the compulsory
   * keys "vendor" and "model", and the optional keys "version", and "screendims".
   *
   * Below are a few samples of these user agent strings:
   * <pre>
   *  Mozilla/4.1 (compatible; MSIE 5.0; Symbian OS; Nokia 3650;424) Opera 6.10  [en]
   *  Mozilla/4.0 (compatible; MSIE 6.0; Nokia7650) ReqwirelessWeb/2.0.0.0
   *  Mozilla/1.22 (compatible; MMEF20; Cellphone; Sony CMD-Z5)
   *  Mozilla/1.22 (compatible; MMEF20; Cellphone; Sony CMD-Z5;Pz063e+wt16)
   *  Mozilla/2.0 (compatible; MSIE 3.02; Windows CE; PPC; 240x320)
   *  mozilla/4.0 (compatible;MSIE 4.01; Windows CE;PPC;240X320) UP.Link/5.1.1.5
   *  Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; PPC; 240x320)
   *  Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; SmartPhone; 176x220)
   *  Mozilla/2.0 (compatible; MSIE 3.02; Windows CE; 240x320; PPC)
   *  Mozilla/2.0 (compatible; MSIE 3.02; Windows CE; Smartphone; 176x220; Mio8380; Smartphone; 176x220)
   *  Mozilla/4.0 (MobilePhone SCP-8100/US/1.0) NetFront/3.0 MMP/2.0
   *  Mozilla/2.0(compatible; MSIE 3.02; Windows CE; Smartphone; 176x220)
   *  Mozilla/4.1 (compatible; MSIE 5.0; Symbian OS Series 60 42) Opera 6.0 [fr]
   *  Mozilla/SMB3(Z105)/Samsung UP.Link/5.1.1.5
   * </pre>
   *
   * @param string $useragent User agent string.
   * @return mixed
   */
  protected function _parseUserAgentMozilla($useragent) {
    // SAMSUNG browsers
    if (preg_match('/^Mozilla\/SMB3\((Z105)\)\/(Samsung)/', $useragent, $matches)) {
      return(array('vendor' => strtoupper($matches[2]), 'model'  => $matches[1]));
    }

    // Extract the string between the brackets.
    if (!preg_match('/^Mozilla\/\d+\.\d+\s*\(([^\)]+)\)/i', $useragent, $matches)) {
      return false;
    }
    $parts = preg_split('/\s*;\s*/', $matches[1]); // split string between brackets on ';' seperator.

    // Micro$oft PPC and Smartphone browsers. Unfortunately, one day, if history repeats itself, this will probably be the only user-agent check necessary.
    if ((count($parts) >= 4) && ($parts[0] == 'compatible') && ($parts[2] == 'Windows CE')) {
      $result = array('vendor' => 'Microsoft');
      if (($parts[3] == 'PPC') || (strtolower($parts[3]) == 'smartphone')) {
        $result['model'] = 'SmartPhone';
        if ((count($parts) >= 5) && preg_match('/^\d{1,4}x\d{1,4}$/i', $parts[4])) {
          $result['screendims'] = strtolower($parts[4]);
        }
      }
      elseif ((count($parts) >= 5) && (($parts[4] == 'PPC') || (strtolower($parts[4]) == 'smartphone'))) {
      	$result['model'] = 'SmartPhone';
      	if (preg_match('/^\d{1,4}x\d{1,4}$/i', $parts[3])) {
          $result['screendims'] = strtolower($parts[3]);
        }
      }
      if (array_key_exists('model',$result)) {
        return $result;
      }
    }

    // Nokia's with Opera browsers or SonyEricssons.
    if ((count($parts) >= 4) && ($parts[0] == 'compatible') && preg_match('/^(Nokia|Sony)\s*(\S+)$/', $parts[3], $matches)) {
      if ($matches[1] == 'Sony') {
        $matches[1] = 'SonyEricsson';
      }
      return(array('vendor' => $matches[1], 'model'  => $matches[2]));
    }

    // SANYO browsers
    if ((count($parts) >= 1) && preg_match('/^MobilePhone ([^\/]+)\/([A-Z]+\/)?(\d+\.\d+)$/', $parts[0], $matches)) { // MobilePhone PM-8200/US/1.0
      return(array('vendor' => 'Sanyo', 'model'  => $matches[1], 'version' => $matches[3]));
    }

    // Nokias with ReqwirelessWeb browser
    if ((count($parts) >= 3) && ($parts[0] == 'compatible') && preg_match('/^(Nokia)\s*(\S+)$/', $parts[1], $matches)) {
      return(array('vendor' => $matches[1], 'model' => $matches[2]));
    }

    return false;
  }



  /**
   * Parses a non-standard mobile user agent string.
   * If no match can be made, FALSE is returned.
   * If a match is made, an associative array is returned containing the compulsory
   * keys "vendor" and "model", and the optional keys "version", and "screendims".
   *
   * Below are a few samples of these user agent strings:
   * <pre>
   *  LGE/U8150/1.0 Profile/MIDP-2.0 Configuration/CLDC-1.0
   *  PHILIPS855 ObigoInternetBrowser/2.0
   *  PHILIPS 535 / Obigo Internet Browser 2.0
   *  PHILIPS-FISIO 620/3
   *  PHILIPS-Fisio311/2.1
   *  PHILIPS-FISIO311/2.1
   *  PHILIPS-Xenium9@9 UP/4.1.16r
   *  PHILIPS-XENIUM 9@9/2.1
   *  PHILIPS-Xenium 9@9++/3.14
   *  PHILIPS-Ozeo UP/4
   *  PHILIPS-V21WAP UP/4
   *  PHILIPS-Az@lis288 UP/4.1.19m
   *  PHILIPS-SYSOL2/3.11 UP.Browser/5.0.1.11
   *  Vitelcom-Feature Phone1.0 UP.Browser/5.0.2.2(GUI
   *  ReqwirelessWeb/2.0.0 MIDP-1.0 CLDC-1.0 Nokia3650
   *  SEC-SGHE710
   * </pre>
   * Notice how often one certain brand of these user-agents is handled by this function. I say no more.
   *
   * @param string $useragent User agent string.
   * @return mixed
   */
  protected function _parseUserAgentRubbish($useragent) {
    // Old ReqwirelessWeb browsers for Nokia. ReqwirelessWeb/2.0.0 MIDP-1.0 CLDC-1.0 Nokia3650
    if (preg_match('/(Nokia)\s*(N-Gage|\d+)$/', $useragent, $matches)) {
      return(array('vendor' => $matches[1], 'model' => $matches[2]));
    }

    // LG Electronics
    elseif (preg_match('/^(LG)E?\/(\w+)(\/(\d+\.\d+))?/', $useragent, $matches)) {  // LGE/U8150/1.0 Profile/MIDP-2.0 Configuration/CLDC-1.0
      $result = array('vendor' => $matches[1], 'model' => $matches[2]);
      if ((count($matches) == 5) && strlen($matches[4])) {
        $result['version'] = $matches[4];
      }
      return $result;
    }

    // And now for the worst of all user agents, those that start with the text 'PHILIPS'.
    elseif (preg_match('/^(PHILIPS)(.+)/', $useragent, $matches)) {
      $vendor  = $matches[1];
      $model   = null;
      $garbage = trim(strtoupper($matches[2])); // everything after the word PHILIPS in uppercase.
      if (preg_match('/^-?(\d+)/', $garbage, $matches)) { // match the model names that are just digits.
      	$model = $matches[1];
      	// PHILIPS855 ObigoInternetBrowser/2.0
        // PHILIPS 535 / Obigo Internet Browser 2.0
      }
      elseif (preg_match('/^-?(FISIO)\s*(\d+)/', $garbage, $matches)) { // match the FISIO model names.
      	$model = $matches[1] . $matches[2];
      	// PHILIPS-FISIO 620/3
        // PHILIPS-Fisio311/2.1
        // PHILIPS-FISIO311/2.1
      }
      elseif (preg_match('/^-?(XENIUM)/', $garbage, $matches)) { // match the XENIUM model names.
      	$model = $matches[1];
      	// PHILIPS-Xenium9@9 UP/4.1.16r
        // PHILIPS-XENIUM 9@9/2.1
        // PHILIPS-Xenium 9@9++/3.14
      }
      elseif (preg_match('/^-?([^\s\/]+)/', $garbage, $matches)) { // match all other model names that contain no spaces and no slashes.
        $model = $matches[1];
        // PHILIPS-Ozeo UP/4
        // PHILIPS-V21WAP UP/4
        // PHILIPS-Az@lis288 UP/4.1.19m
        // PHILIPS-SYSOL2/3.11 UP.Browser/5.0.1.11
      }
      if (isset($model)) {
        return(array('vendor' => $vendor, 'model' => $model));
      }
    }

    // Vitelcom user-agents (used in Spain)
    elseif (preg_match('/^(Vitelcom)-(Feature Phone)(\d+\.\d+)/', $useragent, $matches)) {
      // Vitelcom-Feature Phone1.0 UP.Browser/5.0.2.2(GUI)  -- this is a TSM 3 or a TSM 4.
      return(array('vendor'  => $matches[1],
                   'model'   => $matches[2],
                   'version' => $matches[3]));
    }

    return false;
  }



  /**
   * Parses a user agent string.
   * This method simply calls the other 4 _parseUserAgent*() methods to do the work.
   * If no match can be made, FALSE is returned.
   * If a match is made, an associative array is returned containing the compulsory
   * keys "vendor" and "model", and the optional keys "version", "imode_cache",
   * and "screendims".
   *
   * @param string $useragent User agent string.
   * @return mixed
   */
  protected function _parseUserAgent($useragent) {
    if ($result = $this->_parseUserAgentStandard($useragent)) {
      $this->is_standard = true;
      return $result;
    }
    if ($result = $this->_parseUserAgentMozilla($useragent)) {
      $this->is_mozilla = true;
      return $result;
    }
    if ($result = $this->_parseUserAgentImode($useragent)) {
      $this->is_imode = true;
      return $result;
    }
    if ($result = $this->_parseUserAgentRubbish($useragent)) {
      $this->is_rubbish = true;
      return $result;
    }
    return false;
  }



  /**
   * Returns true if the user-agent string passed into the constructor could be parsed, else false.
   * If this method returns false, then it's probably not a mobile user agent string that was
   * passed into the constructor.
   *
   * @return boolean
   */
  public function success() {
    return isset($this->vendor);
  }


  /**
   * Returns the user agent string as passed into the constructor or read
   * from the environment variable HTTP_USER_AGENT.
   *
   * @return string
   */
  public function userAgent() {
    return $this->useragent;
  }



  /**
   * Returns the vendor of the handset if success() returns true, else null.
   *
   * @return string|null
   */
  public function vendor() {
    return $this->vendor;
  }


  /**
   * Returns the model of the handset if success() returns true, else null.
   *
   * @return string|null
   */
  public function model() {
    return $this->model;
  }


  /**
   * Returns the version (if any) of the user agent.
   * The version information isn't always present, nor reliable.
   *
   * @return string|null
   */
  public function version() {
    return $this->version;
  }


  /**
   * Determines if the parsed user-agent string belongs to an i-mode handset.
   * Returns true, if so, else false.
   *
   * @return boolean
   */
  public function isImode() {
    return $this->is_imode;
  }


  /**
   * Determines if the parsed user-agent string has a Mozilla 'compatible' format.
   * Returns true, if so, else false.
   *
   * @return boolean
   */
  public function isMozilla() {
    return $this->is_mozilla;
  }


  /**
   * Determines if the parsed user-agent string has a standard vendor-model/version format.
   * Returns true, if so, else false.
   *
   * @return boolean
   */
  public function isStandard() {
    return $this->is_standard;
  }


  /**
   * Determines if the parsed user-agent string has a non-standard or messed up format.
   * Returns true, if so, else false.
   *
   * @return boolean
   */
  public function isRubbish() {
    return $this->is_rubbish;
  }


  /**
   * Returns the maximum i-mode cache data size in kb's of the user agent if it is
   * an i-mode user-agent, else null.
   *
   * @return integer|null
   */
  public function imodeCache() {
    return $this->imode_cache;
  }


  /**
   * Returns the screen dimensions in the format wxh if this information was parsed
   * from the user agent string itself, else null.
   *
   * @return string|null
   */
  public function screenDims() {
    return $this->screendims;
  }


  /**
   * Determines if this is a Symbian OS Series 60 user-agent string.
   *
   * @return boolean
   */
  public function isSeries60() {
    if (!isset($this->is_series60)) {
      //  NokiaN-Gage/1.0 SymbianOS/6.1 Series60/1.2 Profile/MIDP-1.0 Configuration/CLDC-1.0
      //  Mozilla/4.1 (compatible; MSIE 5.0; Symbian OS Series 60 42) Opera 6.0 [fr]
      $this->is_series60 = preg_match('/\b(Symbian OS Series 60|SymbianOS\/\S+ Series60)\b/', $this->useragent);
    }
    return $this->is_series60;
  }


}

?>