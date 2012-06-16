# Unity Coders Joomla Extentions
This is where any custom Joomla extentions will be managed. These extentions
are designed with the following parameters:

Joomla Version: 2.6
Joomla Locale: en-GB
Database Backend: MySQL

## Current Joomla Extentions
### Build automation system (build.sh)
All Joomla extentions are packaged in zip files with xml files within given
places within the zip file. The script build.sh takes the extentions and builds
them for inclusion into the website.

### Custom Profile Fields (ucprofile)
Since version 1.6 Joomla has allowed custom profile fields to be created in the
form of a plugin. This plugin aims to develop a set of profile fields which use
this functionality.

These profile fields should be relevent to the users of the website and should
be optional.
