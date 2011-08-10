<?php

mysql_connect("localhost", "acaunca", "Acaunca2009") or die(mysql_error());

mysql_select_db("acaunca_joomla") or die(mysql_error());

//concatenate last_name and id
$result = mysql_query("UPDATE jos_aca_researchers SET pin=CONCAT(last_name,id)") 
or die(mysql_error()); 
$result = mysql_query("UPDATE jos_aca_reviewers SET pin=CONCAT(last_name,id)") 
or die(mysql_error());   
//make lowercase
$result = mysql_query("UPDATE jos_aca_researchers SET pin=LCASE(pin)") 
or die(mysql_error()); 
$result = mysql_query("UPDATE jos_aca_reviewers SET pin=LCASE(pin)") 
or die(mysql_error());   
//remove spaces
$result = mysql_query("UPDATE jos_aca_researchers SET pin=REPLACE(pin,' ','')") 
or die(mysql_error()); 
$result = mysql_query("UPDATE jos_aca_reviewers SET pin=REPLACE(pin,' ','')")
or die(mysql_error());  
//remove hyphens
$result = mysql_query("UPDATE jos_aca_researchers SET pin=REPLACE(pin,'-','')") 
or die(mysql_error()); 
$result = mysql_query("UPDATE jos_aca_reviewers SET pin=REPLACE(pin,'-','')")
or die(mysql_error());   

?>