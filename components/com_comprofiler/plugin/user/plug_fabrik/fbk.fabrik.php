<?php
/**
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class getFabrikTab extends cbTabHandler {

	function getFabrikTab()
	{
		$this->cbTabHandler();
	}

	function getDisplayTab($tab,$user,$ui)
	{
		// $$$ hugh - added privacy option, so you can restrict who sees the tab, requested on forums:
		// http://fabrikar.com/forums/showthread.php?p=128127#post128127
		// privacy setting:
		// 0 = public
		// 1 = profile owner only
		// 2 = profile owner and admins
		$private = (int)$this->params->get('fabrik_private', '0');
		if ($private > 0) {
			$viewer = JFactory::getuser();
			if ($private === 1) {
				if ($user->get('user_id') != $viewer->get('id')) {
					return false;
				}
			}
			else if ($private === 2) {
				if ($user->get('id') !== $viewer->get('id') && ($viewer->get('gid') != 24 && $viewer->get('gid') != 25)) {
					return false;
				}
			}
		}
		$dispatcher = new JDispatcher();
		JPluginHelper::importPlugin('content', 'fabrik', true, $dispatcher);
		$dispatcher->register('content', 'plgContentFabrik');
		$args = array();
		$article = new stdClass();
		$txt = $this->params->get('plugintext');
		//do some dynamic replacesments with the owner's data
		foreach ($user as $k=>$v) {
			if (strstr( $txt, "{\$my->$k}" )) {
				$txt = str_replace("{\$my->$k}", $v, $txt);
			}
		}

		// $$$ hugh - set profile user in session so Fabrik user element can get at it
		// TODO - should really make this table/form specific!
		$session =& JFactory::getSession();
		$session->set('fabrik.plugin.profile_id', $user->get('id'));

		$params = new stdClass();
		$args[] = 0;
		$article->text = $txt;
		$args[] = &$article;
		$args[] = &$params;
		$res = $dispatcher->trigger('onContentPrepare', $args);
		// $$$ peamak http://fabrikar.com/forums/showthread.php?t=10446&page=2
    $dispatcher->register('content', 'plgContentFabrik');
		return  $article->text;
	}
}
?>
