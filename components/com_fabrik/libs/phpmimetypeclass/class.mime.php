<?php

/**
* @package MIME Types Class
* @version 1.0
* @author  Robert Widdick
* @copyright Copyright (c) 2009 Robert Widdick - codehelpers.com
* @license GNU GPL
* @desc    Defines the class for mime types
* @param   None
* @returns Nothing
* @example See example.php
*
* If you use this, all I ask is that you let me know! I'd like to see who all uses this!
* Also, credits would be much appreciated or a link back to codehelpers.com! Thanks :)
*/

class MIMETypes {
  # Define some private variables
  private $mimeTypes;

  /**
  * @desc Initiate variables and include mime types
  * @param String $MIMEFile The MIME File location, if not changed, mime.types.php
  * @return Nothing
  */
  public function __construct($MIMEFile = "mime.types.php") {
    require_once $MIMEFile;
    $this -> mimeTypes = $types;
    unset($types);
  }

  /**
  * @desc Free up some memory
  * @param None
  * @returns Nothing
  */
  public function __destruct() {
    unset($this);
  }

  /**
  * @desc Retrieve the corresponding MIME type, if one exists
  * @param String $file File Name (relative location such as "image_test.jpg" or full "http://site.com/path/to/image_test.jpg")
  * @return String $MIMEType - The type of the file passed in the argument
  */
  public function getMimeType($file = NULL) {
    if(is_file($file)) {
      /**
      * Attempts to retrieve file info from FINFO
      * If FINFO functions are not available then try to retrieve MIME type from pre-defined MIMEs
      * If MIME type doesn't exist, then try (as a last resort) to use the (deprecated) mime_content_type function
      * If all else fails, just return application/octet-stream
      */
      if(!function_exists("finfo_open")) {
        $extension = $this -> getExtension($file);
        if(array_key_exists($extension, $this -> mimeTypes)) {
          return $this -> mimeTypes[$extension];
        } else {
          if(function_exists("mime_content_type")) {
            $type = mime_content_type($file);
            return !empty($type) ? $type : "application/octet-stream";
          } else {
            return "application/octet-stream";
          }
        }
      } else {
        $finfo = finfo_open(FILEINFO_MIME);
        $MIMEType = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $MIMEType;
      }
    } else return "##INVALID_FILE##FILE=".$file."##";
  }

  /**
  * @desc Gets the file extention from a string
  * @param String $file The full file name
  * @return String $ext The file extension
  */
  private function getExtension($file = NULL) {
    if(!is_null($file)) {
      $ext = strtolower(array_pop(explode('.', $file)));
      return $ext;
    } else return "##INVALID_FILE##FILE=".$file."##";
  }
}

?>