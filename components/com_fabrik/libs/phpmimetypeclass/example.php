<?php

/**
* @package MIME Types Class
* @version 1.0
* @author  Robert Widdick
* @copyright Copyright (c) 2009 Robert Widdick - codehelpers.com
* @license GNU GPL
* @desc    An example of how to use the class
* @param   None
* @returns Nothing
* @example This is the example!
*
* If you use this, all I ask is that you let me know! I'd like to see who all uses this!
* Also, credits would be much appreciated or a link back to codehelpers.com! Thanks :)
*/

# Include the MIME Type Class
include "class.mime.php";

# Set the directory to show files in
$dir = ".";
if(is_dir($dir)) {
  if($dh = opendir($dir)) {
    $buffer = '
<table width="100%" cellspacing="5" cellpadding="5" border="0">
<tr>
  <td><strong><u>File Name</u></strong></td>
  <td><strong><u>File Size</u></strong></td>
  <td><strong><u>File Type</u></strong></td>
</tr>';

    # Define some variables
    $totalSizes = 0;
    $totalFiles = 0;

    # Define the MIME Type class object
    $mime = new MIMETypes();

    while(($file = readdir($dh)) !== false) {
      # Check to see if the file isn't a directory
      if($file != "." && $file != ".." && is_file($file) && !is_dir($file)) {
        # Increment total number of files
        $totalFiles ++;

        # Increment total file sizes (combined file sizes)
        $totalSizes += filesize($file);
        $buffer .= '
<tr>
  <td>'.$file.'</td>
  <td>'.filesize($file).' bytes</td>
  <td>'.$mime -> getMimeType($file).'</td>
</tr>
';
      }
    }
    closedir($dh);
    $buffer .= '</table>';

    # Check to see if there were any files at all
    if($totalFiles > 0) {
      # Yes there are files, output the buffer
      echo $buffer;
    } else {
      # No, there are no files
      echo "There are no files in directory &quot;$dir&quot;";
    }
  }
} else die("Invalid directory $dir");

?>