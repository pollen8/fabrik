<?php
/**
 * This is a sample email template. It will just print out all of the request data:
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.email
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
?>
<table>
<?php
foreach ($this->data as $key => $val)
{
	if ($key === 'join') :
		continue;
	endif;
	echo '<tr><td>' . $key . '</td><td>';
	if (is_array($val)) :
		foreach ($val as $v):
			if (is_array($v)) :
				echo implode("<br>", $v);
			else:
				echo implode("<br>", $val);
			endif;
		endforeach;
	else:
		echo $val;
	endif;
	echo "</td></tr>";
}
?>
</table>

<h2>Join data</h2>
<p>Below out puts the form's join data one record at a time:</p>
<table>
<?php
$joindata = $this->data['join'];
foreach (array_keys($joindata) as $joinkey) :
	$keys = array_keys($joindata[$joinkey]);
	$length = count($joindata[$joinkey][$keys[0]]);
	for ($i = 0; $i < $length; $i++) :
		echo '<tr><td colspan="2"><h3>record ' . $i . '</h3></td></tr>';
		foreach ($keys as $k) :
			echo '<tr><td>' . $k . '</td><td>' . $this->data['join'][$joinkey][$k][$i] . '</td></tr>';
		endforeach;
	endfor;
endforeach;
?>
</table>
