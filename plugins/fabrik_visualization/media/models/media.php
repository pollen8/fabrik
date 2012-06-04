<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'visualization.php');

class fabrikModelMedia extends FabrikFEModelVisualization {

	/** @var string google charts api url **/

	var $_url = '';

	function getMedia()
	{
		$app = JFactory::getApplication();
		$Itemid	= @$app->getMenu('site')->getActive()->id;
		$params = $this->getParams();
		$w = $params->get('media_width');
		$h = $params->get('media_height');
		$return = '';
		$which_player = "Extended";
		$player_url = COM_FABRIK_LIVESITE.$this->srcBase."media/libs/xspf/$which_player/xspf_player.swf";
		$playlist_url = 'index.php?option=com_fabrik&controller=visualization.media&view=visualization&task=getPlaylist&format=raw&Itemid='. $Itemid. '&id='.$this->get('id');
		$playlist_url = urlencode($playlist_url);
		$return = '<object type="application/x-shockwave-flash" width="400" height="170" data="' . $player_url . '?playlist_url=' . $playlist_url . '">';
		$return .= '<param name="movie" value="xspf_player.swf?playlist_url=' . $playlist_url . '" />';
		$return .= '</object>';
		return $return;
	}

	function getPlaylist()
	{
		$params = $this->getParams();

		$mediaElement	= $params->get('media_media_elementList');
		$mediaElement .= '_raw';
		$titleElement	= $params->get('media_title_elementList', '');
		$imageElement	= $params->get('media_image_elementList', '');
		if (!empty($imageElement)) {
			$imageElement .= '_raw';
		}
		$infoElement = $params->get('media_info_elementList', '');
		$noteElement = $params->get('media_note_elementList', '');

		$listid = $params->get('media_table');

		$listModel = JModel::getInstance('list', 'FabrikFEModel');
		$listModel->setId($listid);
		$list = $listModel->getTable();
		$form = $listModel->getForm();
		//remove filters?
		// $$$ hugh - remove pagination BEFORE calling render().  Otherwise render() applies
		// session state/defaults when it calls getPagination, which is then returned as a cached
		// object if we call getPagination after render().  So call it first, then render() will
		// get our cached pagination, rather than vice versa.
		$nav = $listModel->getPagination(0, 0, 0);
		$listModel->render();
		$alldata = $listModel->getData();
		$document = JFactory::getDocument();
		$retstr	= "<?xml version=\"1.0\" encoding=\"".$document->_charset."\"?>\n";
		$retstr .= "<playlist version=\"1\" xmlns = \"http://xspf.org/ns/0/\">\n";
		$retstr .= "	<title>" . $list->label . "</title>\n";
		$retstr .= "	<trackList>\n";
		foreach ($alldata as $data) {
			foreach ($data as $row) {
				if (!isset($row->$mediaElement)) {
					continue;
				}
				$location = $row->$mediaElement;
				if (empty($location)) {
					continue;
				}
				$location = str_replace('\\','/',$location);
				$location = ltrim($location, '/');
				$location = COM_FABRIK_LIVESITE . $location;
				//$location = urlencode($location);
				$retstr .= "		<track>\n";
				$retstr .= "			<location>" . $location . "</location>\n";
				if (!empty($titleElement)) {
					$title = $row->$titleElement;
					$retstr .= "			<title>" . $title . "</title>\n";
				}
				if (!empty($imageElement)) {
					$image = $row->$imageElement;
					if (!empty($image)) {
						$image = str_replace('\\','/',$image);
						$image = ltrim($image, '/');
						$image = COM_FABRIK_LIVESITE . $image;
						$retstr .= "			<image>" . $image . "</image>\n";
					}
				}
				if (!empty($noteElement)) {
					$note = $row->$noteElement;
					$retstr .= "			<annotation>" . $note . "</annotation>\n";
				}
				if (!empty($infoElement)) {
					$link = $row->$titleElement;
					$retstr .= "			<info>" . $link . "</info>\n";
				}
				else {
					$link = JRoute::_('index.php?option=com_fabrik&view=form&formid=' . $form->getId() . '&rowid=' . $row->__pk_val);
					$retstr .= "			<info>" . $link . "</info>\n";
				}
				$retstr .= "		</track>\n";
			}
		}
		$retstr .= "	</trackList>\n";
		$retstr .= "</playlist>\n";
		return $retstr;
	}

	function setListIds()
	{
		if (!isset($this->listids)) {
			$params = $this->getParams();
			$this->listids = (array) $params->get('media_table');
		}
	}
}

?>