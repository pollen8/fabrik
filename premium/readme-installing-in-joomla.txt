
Set up
*******
This presumes you already have a Joomla site with Fabrik installed via git.

Add a remote git location - this is where we store the premium plugins:
> git remote add -f fabrik-premium git@bitbucket.org:pollen8/fabrik-premium.git

Add the subtree to your Joomla/Fabrik site:
> git subtree add --prefix=premium fabrik-premium/master

Then pull to grab the latest version:
> git subtree pull --prefix=premium fabrik-premium master

This will copy the premium project into the folder /premium
You then want to make a symlink from the plugins in this folder to the Joomla plugins folder. E.g. to link the form payments plugin:

> mklink /J plugins\fabrik_form\payments premium\plugins\fabrik_form\payments

Ensure that plugins/fabrik_form/payments is added to your Fabrik repo's .gitingore file

Push/pull
*********
> git subtree push --prefix=premium fabrik-premium master

> git subtree pull --prefix=premium fabrik-premium master

