these xml files go in http://fabrikar.com/update/fabrik/
As an end user they are not needed for you to run fabrik.
Below are some notes for fabrik contributors as to how the update system works.

//////////////////////////////////////////////////////////////
////////// The package_list.xml file /////////////////////////
//////////////////////////////////////////////////////////////

The update server is defined in administrator/components/com_fabrik/pkg_fabrik.xml

<updateservers>
	<server type="collection">http://fabrikar.com/update/fabrik/package_list.xml</server>
</updateservers>

This master xml file lists the current updates that can be applied to a fabrik component/module/plugin. 
So when you want to update a plugin you should bump up the version parameter's
value in the <extension> listing and ftp package_list.xml to /update/fabrik/

The version number in each of the <extension> entries is purely for information value.
It is displayed when the end administrator presses the 'Find Updates' button in the extension manager. The actual
update information is contained with the detailsurl xml file.

Within each <extension> entry the detailsurl parameter should point to an xml file which contains
descriptions of each and every update that can be applied to the plugin.


//////////////////////////////////////////////////////////////
////// Individual update xml files ///////////////////////////
//////////////////////////////////////////////////////////////

Each new release that we do should have a corresponding <update> entry in its details xml file.
New updates should go at the end of the xml file.
The version tag should match the current version of the plugin as defined in its own xml manifest file:
e.g. 
If we are releasing the Joomla content plugin version 4.0

plugins/content/fabrik/fabrik.xml should contain

<version>4.0</version>

and administrator/components/com_fabrik/update/fabrik/plg_content_fabrik.xml should contain an <update> section with 

<version>4.0</version>

and a download url entry pointing to the 4.0 version of the plugin.
administrator/components/com_fabrik/update/fabrik/plg_content_fabrik.xml  should then be uplaoded to /update/fabrik/plg_content_fabrik.xml
