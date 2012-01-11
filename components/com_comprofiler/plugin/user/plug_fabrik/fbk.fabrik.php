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
