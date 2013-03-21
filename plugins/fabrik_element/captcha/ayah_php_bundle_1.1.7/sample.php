<!DOCTYPE html>
<?php
//******************************************************************************
/*
	Name:		sample.php

	Purpose:	Provide an example of how to integrate an AYAH PlayThru on PHP web form.

	Requirements:
			- your web server uses PHP5 (or higher).
			- all the AYAH PHP library files are in the same directory as this file.
			- the ayah_config.php contains a valid publisher key and scoring key.
			- you have read the installation instructions page at:
				http://portal.areyouahuman.com/installation/php

	Notes:		- if the Game Style for your PlayThru is set to "Lightbox", the
			  PlayThru will not display until after you click the submit button.
			  To change this setting, use the dashboard at:
				http://portal.areyouahuman.com/dashboard.php
*/
//******************************************************************************

// Instantiate the AYAH object.
require_once("ayah.php");
$ayah = new AYAH();

// If the PlayThru does not work correctly, enable debug mode.
//$ayah->debug_mode(TRUE);

// The form submits to itself, so see if the user has submitted the form.
if (array_key_exists('my_submit_button_name', $_POST))
{
	// Use the AYAH object to get the score.
	$score = $ayah->scoreResult();

	// Check the score to determine what to do.
	if ($score)
	{
		// Add code to process the form.
		echo "Hello ".$_POST['name'].", You are a human!";
	}
	else
	{
		echo "You are NOT a human!";
	}
}
?>

<!-- Build the form tag. -->
<!-- (note: the blank action causes the form to submit to itself) -->
<form method="post" action="">
	<!-- Build a form field. -->
	<p>Please enter your name: <input type="text" name="name"></p>

	<?php
		// Use the AYAH object to get the HTML code needed to
		// load and run the PlayThru.
		echo $ayah->getPublisherHTML();
	?>

	<!-- Include a submit button. -->
	<input type="Submit" name="my_submit_button_name" value=" GO ">
</form>
