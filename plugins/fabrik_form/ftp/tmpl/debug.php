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


<h3>Here's some placeholder magic you can use</h3>

<p>Grab specific elements data with {tablename___elementname} and {tablename___elementname_raw}
<p>use 'emailto' to get the email address of the current address which the email is being sent to:<br/>
hey {emailto} this rocks!</p>

<p>If that email address has an associated account, the plugin will load up that user and his properties can be accessed
with the place holder '$your->username':<br/>
Your user name = {$your->username}
</p>
