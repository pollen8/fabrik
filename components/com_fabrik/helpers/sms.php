<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class fabrikSMS
{
	public static function doRequest($method, $url, $vars, $auth = '', $callback = false)
	{
		if (!function_exists('curl_init'))
		{
			JError::raiseNotice(500, JText::_('COM_FABRIK_ERR_CURL_NOT_INSTALLED'));
			return;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		if ($method == 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}
		if (!empty($auth))
		{
			curl_setopt($ch, CURLOPT_USERPWD, $auth);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		echo "<pre>";print_r($ch);
		print_r($data);
		if ($data)
		{
			if ($callback)
			{
				return call_user_func($callback, $data);
			}
			else
			{
				return $data;
			}
		}
		else
		{
			return curl_error($ch);
		}
	}
}