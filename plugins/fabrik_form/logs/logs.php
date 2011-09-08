<?php
/**
 * Form email plugin
 * @package Joomla
 * @subpackage Fabrik
 * @author peamak
 * @copyright (C) fabrikar.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');

class plgFabrik_FormLogs extends plgFabrik_Form {

	/**
	 * @var array of files to attach to email
	 */
	var $_counter = null;

	function onLoad( &$params, &$formModel )
	{

		if ((JRequest::getVar('view') == 'details') && ($params->get('log_details') != '0')) {
			$db = FabrikWorker::getDbo();
			$user = JFactory::getUser();
			$message = $user->get(''.$params->get('log_details').'');
			if (($params->get('log_details_ifvisitor') != 0) && ($message == NULL)) {
				if ($params->get('log_details_ifvisitor') == 1) {
					$message = $_SERVER['REMOTE_ADDR'];
				} else {
					$message = JText::_('PLG_FORM_LOG_LOG_DETAILS_VISITOR');
				}
			}
			$in_db = "INSERT INTO `#__{package}_log` (`referring_url`, `message_type`, `message`) VALUES ('".$_SERVER['HTTP_REFERER']."', 'details.view', '$message');";
			$db->setQuery($in_db);
			if ($message != NULL) {
				$db->query();
			}
		}
	return true;
	}

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form model
	 * @returns bol
	 */

	function onLastProcess(&$params, &$formModel )
	{
	  $app 				=& JFactory::getApplication();
	  //$data 			=& $formModel->_fullFormData;
	  //$data 			=& $formModel->_formData;

	 	// Generate random filename
		if ($params->get('logs_random_filename') == 1) {
	 		function generate_filename($length) {
  				$key = "";
  				$possible = "0123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRTVWXYZ";
  				$i = 0;
  					while ($i < $length) {
    					$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
      					$key .= $char;
      					$i++;
  					}
  				return $key;
			}
			$random_filename = '_'.generate_filename($params->get('logs_random_filename_length'));
		} else {
			$random_filename = '';
		}


		$w = new FabrikWorker();
		$logs_path = $w->parseMessageForPlaceHolder($params->get('logs_path'));
		if (!file_exists($logs_path)) {
			JFolder::create( $logs_path);
		}

		$ext = $params->get('logs_file_format');
		$sep = $params->get('logs_separator');
		// Making complete path + filename + extension
		$w = new FabrikWorker();
		$logs_file = $logs_path.$w->parseMessageForPlaceHolder($params->get('logs_file')).$random_filename.'.'.$ext;
		$logs_mode = $params->get('logs_append_or_overwrite');
		$date_element = $params->get('logs_date_field');
		$date_now = $params->get('logs_date_now');

		// COMPARE DATA
		$result_compare = '';
		if( $params->get('compare_data') == 1 ) {


			$this->formModel = $formModel;
			$data = $this->getEmailData();
			$post = JRequest::get('post');
			$listModel = $formModel->getTable();

			if ($ext == 'csv') {
				$sep_compare = '';
				$sep_2compare = '/ ';
			} else if ($ext == 'txt') {
				$sep_compare = '\n';
				$sep_2compare = '\n';
			} else if ($ext == 'htm') {
				$sep_compare = '<br/>';
				$sep_2compare = '<br/>';
			}
				foreach ($data as $key => $val) {
					if (($val != $formModel->_origData->$key) && (isset($formModel->_origData->$key)) && (isset($val)) && (substr($key, -4, 4) != '_raw')) {
						$id_elementModel = $formModel->getPluginManager()->getElementPlugin($key);
						$id_element = $id_elementModel->getElement(true);
						$formModel->_formData[$id_element->name] = $formModel->_fullFormData['rowid'];
						$formModel->_formData[$id_element->name . '_raw'] = $formModel->_fullFormData['rowid'];
						$test = $id_element->name->$key;
							$result_compare .= JText::_('PLG_FORM_LOG_COMPARE_DATA_CHANGE_ON').' '.$key.' '.$sep_compare.JText::_('PLG_FORM_LOG_COMPARE_DATA_FROM').' '.$formModel->_origData->$key.' '.$sep_compare.JText::_('PLG_FORM_LOG_COMPARE_DATA_TO').' '.$val.' '.$sep_2compare;

					}
				}

		}

		if ($date_now != '') {
		  	$date = date("$date_now");
		} else {
			$date = date("Y-m-d H:i:s");
		}

		// Custom Message
		if ($params->get('custom_msg') != '') {

					$rowidPos = strpos($_SERVER['HTTP_REFERER'], 'rowid=');
					$idPos = $rowidPos + 6;
					$rowid = substr($_SERVER['HTTP_REFERER'], $idPos, 1);
				if (($rowid == "=") || ($rowid == '&') || ($rowid == '')) {
					$rep_add_edit = JText::_('PLG_FORM_LOG_REP_ADD');
				} else {
					$rep_add_edit = JText::_('PLG_FORM_LOG_REP_EDIT');
				}
			$custom_msg = $params->get('custom_msg');
			$custom_msg = preg_replace('/{Add\/Edit}/', $rep_add_edit, $custom_msg);
			$custom_msg = preg_replace('/{DATE}/', $date, $custom_msg);
			/* Using Fabrik's own placeholders {$_SERVER->FOO} instead
			$custom_msg = preg_replace('/{IP}/', $_SERVER['REMOTE_ADDR'], $custom_msg);
			$custom_msg = preg_replace('/{REFERER}/', $_SERVER['HTTP_REFERER'], $custom_msg);
			$custom_msg = preg_replace('/{USERAGENT}/', $_SERVER['HTTP_USER_AGENT'], $custom_msg);*/
			$excl_clabels = preg_replace('/([-{2}| |"][0-9a-zA-Z.:$_>]*)/', '', $custom_msg);
			$split_clabels = preg_split('/[+]{1,}/', $excl_clabels);
			$clabels = preg_replace('/[={2}]+[a-zA-Z0-9_-]*/', '', $split_clabels);
			$ctypes = preg_replace('/[a-zA-Z0-9_-]*[={2}]/', '', $split_clabels);
			$labtyp = array_combine($clabels, $ctypes);

			/*$searchFor = array('date', 'fecha', 'data', 'datum');
			foreach ($searchFor as $sdate) {
				//if (in_array($sdate, $clabels)) {
					foreach ($clabels as $keydate => $valdate) {
						$valdate = strtolower($valdate);
    					if ($valdate == $sdate) {
        					unset($clabels[$keydate]);
        					$datefield = 1;
							$labeldate = $sdate;
    					}
    					next($clabels);
					}


				//}
			}*/

			$w = new FabrikWorker();
			$custom_msg = $w->parseMessageForPlaceHolder($custom_msg);
			$excl_cdata = preg_replace('/((?!("[^"]*))([ |\w|+|.])+(?=[^"]*"\b)|(?!\b"[^"]*)( +)+(?=([^"]*)$)|(?=\b"[^"]*)( +)+(?=[^"]*"\b))/', '', $custom_msg);
			$cdata = preg_split('/["]{1,}/', $excl_cdata);
			// Labels for CSV & for DB
			$clabels_csv_imp = implode("\",\"", $clabels);
			$clabels_csv_p1 = preg_replace('/^(",)/', '', $clabels_csv_imp);
			$clabels_csv = '';
			//if ($datefield) {
			//	$clabels_csv .= '"'.$labeldate.'",';
			//}
			$clabels_csv .= preg_replace('/(,")$/', '', $clabels_csv_p1);
			if( $params->get('compare_data') == 1 ) {
				$clabels_csv .= ', "'.JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_CSV').'"';
			}
			$clabels_createdb_imp = '';
			foreach ($labtyp as $klb => $vlb) {
				if ($vlb == 'varchar') {
					$clabels_createdb_imp .= '`'.$klb.'` '.$vlb.'(255) NOT NULL, ';
				} else if ($vlb == 'int') {
					$clabels_createdb_imp .= '`'.$klb.'` '.$vlb.'(11) NOT NULL, ';
				} else if ($vlb == 'datetime') {
					$clabels_createdb_imp .= '`'.$klb.'` '.$vlb.' NOT NULL, ';
				}
			}

			//$clabels_createdb_imp = implode("`, `", $clabels_createdb_imp);

			//$clabels_createdb_p1 = substr($clabels_createdb_imp, 9);
			$clabels_createdb = substr_replace($clabels_createdb_imp, '', -2);
			//$clabels_createdb = '';
			//if ($datefield) {
			//	$clabels_createdb .= '`'.$labeldate.'` datetime NULL, ';
			//}
			//$clabels_createdb .= preg_replace('/(,`)$/', '', $clabels_createdb_p1);
			if( $params->get('compare_data') == 1 ) {
				$clabels_createdb .= ', `'.JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_DB').'` text NOT NULL';
			}

			$clabels_db_imp = implode("`,`", $clabels);
			$clabels_db_p1 = preg_replace('/^(`,)/', '', $clabels_db_imp);
			$clabels_db = '';
			//if ($datefield) {
			//	$clabels_db .= '`'.$labeldate.'`, ';
			//}
			$clabels_db .= preg_replace('/(,`)$/', '', $clabels_db_p1);


			if( $params->get('compare_data') == 1 ) {
				$clabels_db .= ', `'.JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_DB').'`';
			}
			// data for CSV & for DB
			$cdata_csv_imp = implode("\",\"", $cdata);
			$cdata_csv_p1 = preg_replace('/^(",)/', '', $cdata_csv_imp);
			$cdata_csv = preg_replace('/(,")$/', '', $cdata_csv_p1);
			$cdata_csv = preg_replace('/={1,}",/', '', $cdata_csv);
			$cdata_csv = preg_replace('/""/', '"', $cdata_csv);

			if( $params->get('compare_data') == 1 ) {
				$cdata_csv .= ', "'.$result_compare.'"';
			}
			$cdata_db_imp = implode("','", $cdata);
			$cdata_db_p1 = preg_replace("/^(',)/", '', $cdata_db_imp);
			$cdata_db = preg_replace("/(,')$/", '', $cdata_db_p1);
			$cdata_db = preg_replace("/={1,}',/", '', $cdata_db);
			$cdata_db = preg_replace("/''/", "'", $cdata_db);

			if( $params->get('compare_data') == 1 ) {
				$result_compare = preg_replace('/<br\/>/', '- ', $result_compare);
				$result_compare = preg_replace('/\\n/', '- ', $result_compare);
				$cdata_db .= ", '".$result_compare."'";
			}
			$custom_msg = preg_replace('/([++][0-9a-zA-Z.:_]*)/', '', $custom_msg);
			$custom_msg = preg_replace('/^[ ]/', '', $custom_msg);
			$custom_msg = preg_replace('/  /', ' ', $custom_msg);
			$custom_msg = preg_replace('/"/', '', $custom_msg);
			if( $params->get('compare_data') == 1 ) {
				$custom_msg .= '<br />'.$result_compare;
			}

		}
		else {
			if ($params->get('logs_record_ip') == 1) {
				$clabels_createdb = "`date` datetime NOT NULL, `ip` varchar(255) NOT NULL";
				$clabels_db = '`date`, `ip`';
				$cdata_db = "'$date', '".$_SERVER['REMOTE_ADDR']."'";
			}
			if ($params->get('logs_record_referer') == 1) {
				if ($params->get('logs_record_ip') == 1) {
					$clabels_createdb .= ", `referer` varchar(255) NOT NULL";
					$clabels_db .= ', `referer`';
					$cdata_db .= ", '".$_SERVER['HTTP_REFERER']."'";
				} else {
					$clabels_createdb = "`date` datetime NOT NULL, `referer` varchar(255) NOT NULL";
					$clabels_db = '`date`, `referer`';
					$cdata_db = "'$date', '".$_SERVER['HTTP_REFERER']."'";
				}
			}
			if ($params->get('logs_record_useragent') == 1) {
				if (($params->get('logs_record_ip') == 1) || ($params->get('logs_record_referer') == 1)) {
					$clabels_createdb .= ", `user_agent` varchar(255) NOT NULL";
					$clabels_db .= ', `user_agent`';
					$cdata_db .= ", '".$_SERVER['HTTP_USER_AGENT']."'";
				} else {
					$clabels_createdb = "`date` datetime NOT NULL, `user_agent` varchar(255) NOT NULL";
					$clabels_db = '`date`, `user_agent`';
					$cdata_db = "'$date', '".$_SERVER['HTTP_USER_AGENT']."'";
				}
			}
			if ($params->get('compare_data') == 1) {
				if (($params->get('logs_record_ip') == 1) || ($params->get('logs_record_referer') == 1) || ($params->get('logs_record_useragent') == 1)) {
					$clabels_createdb .= ", `".JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_DB')."` text NOT NULL";
					$clabels_db .= ", `".JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_DB')."`";
					$cdata_db .= ", '".$result_compare."'";
				} else {
					$clabels_createdb = "`date` datetime NOT NULL, `".JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_DB')."` varchar(255) NOT NULL";
					$clabels_db = '`date`, `user_agent`';
					$cdata_db = "'$date', '".$result_compare."'";
				}
			}
		}

			/* For CSV files
			 * If 'Append' method is used, you don't want to repeat the labels (Date, IP, ...)
			 * each time you add a line in the file */
			if ((!file_exists($logs_file)) || ($logs_mode == 'w')) {
				$labels = 1;
			} else {
				$labels = 0;
			}

		if ($params->get('make_file') == 1) {
			// Opening or creating the file
			$open = fopen($logs_file, $logs_mode);

			if ($params->get('custom_msg') != '') {
				if ($ext != 'csv') {
					fwrite($open, $custom_msg."\n".$sep."\n");
					} else {
						// Making the CSV file
								// If the file already exists, do not add the 'label line'
								if ($labels == 1) {
									  fwrite($open, $clabels_csv);
								}
							// Inserting data in CSV with actual line break as row separator
							fwrite ($open, "
".$cdata_csv."");
					}
			} else {
				// Making HTM File
				if ($ext == 'htm') {
						fwrite($open, "<b>Date:</b> " . $date . "<br/>");
					if ($params->get('logs_record_ip') == 1) {
						fwrite($open, "<b>IP Address:</b> " . $_SERVER['REMOTE_ADDR'] . "<br/>");
					}
					if ($params->get('logs_record_referer') == 1) {
						fwrite($open, "<b>Referer:</b> ". $_SERVER['HTTP_REFERER'] . "<br/>");
					}
					if ($params->get('logs_record_useragent') == 1) {
						fwrite($open, "<b>UserAgent: </b>". $_SERVER['HTTP_USER_AGENT']. "<br/>");
					}
						fwrite($open, $result_compare.$sep."<br/>");
				}
				// Making the TXT file
				else if ($ext == 'txt') {
						fwrite($open, "Date: " . $date . "\n");
					if ($params->get('logs_record_ip') == 1) {
						fwrite($open, "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n");
					}
					if ($params->get('logs_record_referer') == 1) {
						fwrite($open, "Referer: ". $_SERVER['HTTP_REFERER'] . "\n");
					}
					if ($params->get('logs_record_useragent') == 1) {
						fwrite($open, "UserAgent: ". $_SERVER['HTTP_USER_AGENT']. "\n");
					}
						fwrite ($open, $result_compare.$sep."\n");
				} else // Making the CSV file
				if ($ext == 'csv') {
							// If the file already exists, do not add the 'label line'
							if ($labels == 1) {
								  fwrite($open, "Date,");
							  if ($params->get('logs_record_ip') == 1) {
								  // Putting some "" around the label to avoid two different fields
								  fwrite($open, "\"IP Address\"");
							  }
							  if ($params->get('logs_record_referer') == 1) {
								  if ($params->get('logs_record_ip') == 1) {
									  fwrite($open, ",Referer");
								  } else {
									  fwrite($open, "Referer");
								  }
							  }
							  if ($params->get('logs_record_useragent') == 1) {
								  if (($params->get('logs_record_ip') == 1) || ($params->get('logs_record_referer') == 1)) {
									  fwrite($open, ",UserAgent");
								  } else {
									  fwrite($open, "UserAgent");
								  }
							  }
							  if ($params->get('compare_data') == 1) {
								  if (($params->get('logs_record_ip') == 1) || ($params->get('logs_record_referer') == 1) || ($params->get('logs_record_useragent') == 1)) {
									  fwrite($open, ",\"".JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_CSV')."\"");
								  } else {
									  fwrite($open, "\"".JText::_('PLG_FORM_LOG_COMPARE_DATA_LABEL_CSV')."\"");
								  }
							  }
							}
						// Inserting data in CSV with actual line break as row separator
						fwrite ($open, "
\"".$date."\",");

					if ($params->get('logs_record_ip') == 1) {
						fwrite($open, "\"".$_SERVER['REMOTE_ADDR'] . "\"");
					}
					if ($params->get('logs_record_referer') == 1) {
						if ($params->get('logs_record_ip') == 1) {
							fwrite($open, ",\"".$_SERVER['HTTP_REFERER'] . "\"");
						} else {
							fwrite($open, "\"".$_SERVER['HTTP_REFERER'] . "\"");
						}
					}
					if ($params->get('logs_record_useragent') == 1) {
						if (($params->get('logs_record_ip', '') == 1) || ($params->get('logs_record_referer', '') == 1)) {
							fwrite($open, ",\"".$_SERVER['HTTP_USER_AGENT']. "\"");
						} else {
							fwrite($open, "\"".$_SERVER['HTTP_USER_AGENT']. "\"");
						}
					}
					if ($params->get('compare_data') == 1) {
						if (($params->get('logs_record_ip', '') == 1) || ($params->get('logs_record_referer', '') == 1) || ($params->get('logs_record_useragent', '') == 1)) {
							fwrite($open, ",\"".$result_compare. "\"");
						} else {
							fwrite($open, "\"".$result_compare. "\"");
						}
					}
				}
			}




			fclose($open);
		}

			// Record in DB
			if ($params->get('logs_record_in_db') == 1) {
				$db = FabrikWorker::getDbo();
				// In which table?
				if ($params->get('record_in') == '') {
					$rdb = '#__{package}_log';
				} else {
					$db_suff = $params->get('record_in');
					$this->formModel = $formModel;
					$form = $formModel->getForm();
					$fid = $form->id;
					$db->setQuery("SELECT `db_table_name` FROM `#__{package}_tables` WHERE `form_id` = '$fid'");
					$tname = $db->loadResult();
					$rdb = $tname.$db_suff;
				}

				// New record or edit?
				//if ((substr($_SERVER['HTTP_REFERER'], -1) == '=') || (substr($_SERVER['HTTP_REFERER'], -1) == '&')) {
					$rowidPos = strpos($_SERVER['HTTP_REFERER'], 'rowid=');
					$idPos = $rowidPos + 6;
					$rowid = substr($_SERVER['HTTP_REFERER'], $idPos, 1);
				if (($rowid == "=") || ($rowid == '&') || ($rowid == '')) {
					$message_type = 'form.new';
				} else {
					$message_type = 'form.edit';
				}
				// Making the message to record
				if ($params->get('custom_msg') != '') {
					$message = preg_replace('/<br\/>/', ' ', $custom_msg);
				} else {
					$message = '';
					if ($params->get('logs_record_ip') == 1) {
						//$message .= 'IP: '.$_SERVER['REMOTE_ADDR'].GROUPSPLITTER2;
						$message .= 'IP: '.$_SERVER['REMOTE_ADDR'].',';
					}
					if ($params->get('logs_record_useragent') == 1) {
						$message .= 'UserAgent: '.$_SERVER['HTTP_USER_AGENT'].GROUPSPLITTER2;
						$message .= 'UserAgent: '.$_SERVER['HTTP_USER_AGENT'].',';
					}
					if ($params->get('compare_data') == 1) {
						$result_compare = preg_replace('/<br\/>/', '- ', $result_compare);
						$message .= preg_replace('/\\n/', '- ', $result_compare);
					}
				}

				if ($params->get('record_in') == '') {
					$in_db = "INSERT INTO `$rdb` (`referring_url`, `message_type`, `message`) VALUES ('".$_SERVER['HTTP_REFERER']."', '$message_type', '$message');";
				} else {
					$create_custom_table = "CREATE TABLE IF NOT EXISTS `$rdb` (`id` int(11) NOT NULL auto_increment PRIMARY KEY, $clabels_createdb);";
					$db->setQuery($create_custom_table);
					$db->query();
					$in_db = "INSERT INTO `$rdb` ($clabels_db) VALUES ($cdata_db);";
				}
				// Insert in DB

				$db->setQuery($in_db);
				$db->query();
			}

		return true;

	}

}
?>