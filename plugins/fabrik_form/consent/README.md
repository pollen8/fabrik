# Consent Fabrik form plugin

This plugin asks and records user's consent in order to be compliant with the GDPR directive.

## What to use it for?

You can use it for **contacts forms**, asking confirmation to the user filling the form that you are authorized to process his personal data according to your terms of services.
This is done through an **unchecked checkbox**, preceded by a custom text asking for consent. This custom text can include a link to your terms of services or any URL.
Once the file is submitted, the consent is recorded in a table in the Joomla! main database (#__fabrik_privacy). The record includes:
* the date of the submission
* a reference to the Fabrik list ID, so you can pre-filter the records in case you have several tables recording personal datas you wish to track consent
* a reference to the form ID
* a reference to the row ID, so you can join your Fabik list with the consent database to have a full view of the data recorded and their consents
* the text of the label that the user agreed to
* the IP address of the user (optional)

## Other use
You can also use this plugin for **user management**: you can create, edit and delete Joomla! users using the JUser Fabrik plugin to create complete registration forms, including any additional field you may require.
Remember however that the GDPR require that you collect **only** the data you need to accomplish your mission, and nothing more.
With this plugin, you can comply with GDPR:
* By asking and recording consent upon the creation of an account
* By providing the user the ability to withdraw his consent and delete his account by himself
* By informing via email the user when you have edited his personal data

## Installation

* Download a .zip file of the plugin and install it as any Joomla! extension.
* Once installed, go to the plugin manager, filter out on fabrik form plugins and enable the plugin
* In your Fabrik form settings, add the plugin and configure it as appropriate

You can see this plugin in action on [this page](https://www.betterweb.fr/contact?utm_medium=referral&utm_source=github&utm_campaign=signature) of our website.

Need help to comply to GDPR, please contact us : [https://www.betterweb.fr/services/applications-pour-joomla](https://www.betterweb.fr/services/applications-pour-joomla?utm_medium=referral&utm_source=github&utm_campaign=signature).