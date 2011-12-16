<?php 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class bitly {
	/**
	 * A PHP class to utilize the bitly API
	 * through the Bitly API. Handles:
	 * - Shorten
	 *
	 * Requires PHP5, CURL, JSON and a Bitly account http://bit.ly/
	 *	 
	 * Bitly API documentation: http://code.google.com/p/bitly-api/wiki/ApiDocumentation
	 */
 
	protected $appkey;
	protected $login;
	protected $version;
 
	/**
	* Contruct the Bitly Class
	*
    * @param string, your Bitly account key
    * @param string, your Bitly account login
	*/	
	public function __construct($login, $appkey, $version='2.0.1') {
		$this->login = $login;
		$this->appkey = $appkey;		
		$this->version = $version;
	}
 
	/**
	* Retrieve a shortened URL
	*
    * @param string, url to shorten
    * @param string, version of Bitly to use
	*/
	public function shorten($url) {
		//create the URL
		$api_url = 'http://api.bit.ly/shorten?version='.$this->version.'&longUrl='.urlencode($url).'&format=xml&login='.$this->login.'&apiKey='.$this->appkey;
		//call the API
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $api_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		curl_close($curl);
 
		//parse the XML response and return the url
		$xml_object = new SimpleXMLElement($response);
		return $xml_object->results->nodeKeyVal->shortUrl;
	}
 
}?>