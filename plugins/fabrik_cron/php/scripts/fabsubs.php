<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

error_reporting(E_ALL);

jimport('joomla.mail.helper');
//JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabsubs/tables');
//require_once JPATH_ROOT . '/fabrik_plugins/form/paypal/scripts/fabrikar_subs.php';

$db = FabrikWorker::getDbo();

$db->setQuery("SELECT *,
CASE
	WHEN timeunit = 'Y' THEN 365 * time_value
	WHEN timeunit = 'M' THEN 30 * time_value
	WHEN timeunit = 'W' THEN 7 * time_value
	WHEN timeunit = 'D' THEN time_value
  END
  as emailday
   FROM #__fabrik_subs_cron_emails WHERE event_type = 'auto_renewal'");
$auto_renewal_mails = $db->loadObjectList('emailday');
echo $db->getQuery();

$db->setQuery("SELECT *,
CASE
	WHEN timeunit = 'Y' THEN 365 * time_value
	WHEN timeunit = 'M' THEN 30 * time_value
	WHEN timeunit = 'W' THEN 7 * time_value
	WHEN timeunit = 'D' THEN time_value
  END
  as emailday
   FROM fabsubs_emails WHERE event_type = 'expiration'");
$expiration_mails = $db->loadObjectList('emailday');


$config = JFactory::getConfig();
$sitename = $config->get('sitename');
$mailfrom = $config->get('mailfrom');
$fromname = $config->get('fromname');
$url = str_replace('/administrator', '', JURI::base());

	$db->setQuery("SELECT s.id AS subid, p.id AS planid, p.duration, plan_name AS subscription, p.period_unit, username, email, u.name, userid,
signup_date, '$sitename' AS sitename, '$mailfrom' AS mailfrom, '$url' AS url, '$fromname' AS fromname, s.recurring,
CASE
	WHEN p.period_unit = 'Y' THEN date_add(lastpay_date , interval p.duration year)
	WHEN p.period_unit = 'M' THEN date_add(lastpay_date , interval p.duration month)
	WHEN p.period_unit = 'W' THEN date_add(lastpay_date , interval p.duration week)
	WHEN p.period_unit = 'D' THEN date_add(lastpay_date , interval p.duration day)
  END
  AS renew_date
,
CASE
	WHEN p.period_unit = 'Y' THEN datediff(date_add(lastpay_date , interval p.duration year), now())
	WHEN p.period_unit = 'M' THEN datediff(date_add(lastpay_date , interval p.duration month), now())
	WHEN p.period_unit = 'W' THEN datediff(date_add(lastpay_date , interval p.duration week), now())
	WHEN p.period_unit = 'D' THEN datediff(date_add(lastpay_date , interval p.duration day), now())
  END
  AS daysleft
FROM `fabsubs_subscriptions` AS s
LEFT JOIN #__users AS u ON u.id = s.userid
INNER JOIN fabsubs_plans AS p ON p.id = s.plan
INNER JOIN fabsubs_payment_gateways AS g ON g.id = s.type
 WHERE status = 'Active' AND lifetime = 0 and p.free = 0
ORDER BY daysleft ");

$res = $db->loadObjectList();

foreach ($res as $row) {
	if (array_key_exists($row->daysleft, $auto_renewal_mails) && $row->recurring == 1) {
		$mail = clone($auto_renewal_mails[$row->daysleft]);
		foreach ($row as $k=>$v) {
			$mail->subject = str_replace('{'.$k.'}', $v, $mail->subject);
			$mail->body = str_replace('{'.$k.'}', $v, $mail->body);
		}
		$res = JUtility::sendMail($mailfrom, $fromname, $row->email, $mail->subject, $mail->body, true);
	}

	if (array_key_exists($row->daysleft, $expiration_mails) && $row->recurring == 0)
	{
		$mail = clone($expiration_mails[$row->daysleft]);
		foreach ($row as $k=>$v)
		{
			$mail->subject = str_replace('{'.$k.'}', $v, $mail->subject);
			$mail->body = str_replace('{'.$k.'}', $v, $mail->body);
		}
		$res = JUtility::sendMail($mailfrom, $fromname, $row->email, $mail->subject, $mail->body, true);
	}
}

//send email reminders to active subs in old acctexpt table:
//last date this should be used from is 09/03/2011
$db->setQuery("SELECT s.id AS subid, email, name, `type` AS subscription, username,
datediff(expiration, now()) AS daysleft, expiration as renew_date
FROM `#__acctexp_subscr` as s
left join #__users as u on s.userid = u.id
where status = 'Active' and plan != 2 and plan !=3
");
$res = $db->loadObjectList();

foreach ($res as $row) {
	if (array_key_exists($row->daysleft, $expiration_mails)) {
		$mail = clone($expiration_mails[$row->daysleft]);
		foreach ($row as $k=>$v) {
			$mail->subject = str_replace('{'.$k.'}', $v, $mail->subject);
			$mail->body = str_replace('{'.$k.'}', $v, $mail->body);
		}
		$res = JUtility::sendMail( $mailfrom, $fromname, $row->email, $mail->subject, $mail->body, true);
	}
}

//expire non recurring subs that have expired. Create fall back plan if required

	$db->setQuery("SELECT s.id AS subid
FROM `fabsubs_subscriptions` AS s
INNER JOIN fabsubs_plans AS p ON p.id = s.plan
 WHERE status = 'Active' AND lifetime = 0 AND recurring = 0 AND CASE
	WHEN p.period_unit = 'Y' THEN datediff(date_add(lastpay_date , interval p.duration year), now())
	WHEN p.period_unit = 'M' THEN datediff(date_add(lastpay_date , interval p.duration month), now())
	WHEN p.period_unit = 'W' THEN datediff(date_add(lastpay_date , interval p.duration week), now())
	WHEN p.period_unit = 'D' THEN datediff(date_add(lastpay_date , interval p.duration day), now())
  END <= 0

  OR ( expiration != '0000-00-00 00:00:00' AND
  CASE
	WHEN p.period_unit = 'Y' THEN datediff(date_add(expiration , interval p.duration year), now())
	WHEN p.period_unit = 'M' THEN datediff(date_add(expiration , interval p.duration month), now())
	WHEN p.period_unit = 'W' THEN datediff(date_add(expiration , interval p.duration week), now())
	WHEN p.period_unit = 'D' THEN datediff(date_add(expiration , interval p.duration day), now())
  END <= 0
  )
 ");

	$ipn = new fabrikPayPalIPN();
	$rows = $db->loadObjectList();
	$now = JFactory::getDate()->toSql();
	$sub = FabTable::getInstance('Subscriptions', 'FabrikTable');
	foreach ($rows as $row) {
		$sub->load($row->subid);
		$sub->status = 'Expired';
		$sub->eot_date = $now;
		$sub->store();
		$ipn->fallbackPlan($sub);
	}