<?php
defined('_JEXEC') or die('Restricted access');
?>

<!-- This is a sample email template. It will just print out all of the request data:
 -->
<table border="1">

<?php
foreach ($this->data as $key => $val) {
  echo "<tr><td>$key</td><td>";
  if (is_array($val)) {
  	foreach ($val as $v) {
  		if (is_array($v)) {
  			echo implode("<br />", $v);
	  	}else{
	  		echo $v."<br />";
	  	}
  	}
  } else {
  	echo $val;
  }
  echo "</td></tr>";
}
?>
</table>
<?php if (array_key_exists('join', $this->data)) {?>
<h2>Join data</h2>
<p>Below out puts the form's join data one record at a time:</p>
<table>
<?php
$joindata = (array)$this->data['join'];
foreach (array_keys($joindata) as $joinkey) {
	$keys = array_keys($joindata[$joinkey]);
	$length = count($joindata[$joinkey][$keys[0]]);
	for ($i = 0; $i < $length; $i++) {
		echo "<tr><td colspan=\"2\"><h3>record $i</h3></td></tr>";
		foreach($keys as $k) {
			$data = $this->data['join'][$joinkey][$k][$i];
			echo  "<tr><td>$k</td><td>";print_r($data);echo "</td></tr>";
		}
	}
}
?>
</table>
<?php } else {?>
<p>No join data found in the form </p>
<?php }?>

<h3>Here's some placeholder magic you can use</h3>

<p>Grab specific elements data with {tablename___elementname} and {tablename___elementname_raw}
<p>use 'emailto' to get the email address of the current address which the email is being sent to:<br/>
hey {emailto} this rocks!</p>

<p>If that email address has an associated account, the plugin will load up that user and his properties can be accessed
with the place holder '$your->username':<br/>
Your user name = {$your->username}
</p>
