=== Openname ===
Contributors: larz3
Tags: openname, avatar
Requires at least: 4.1
Tested up to: 4.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows users to use Openname Avatars for the Wordpress Avatar and add Openname data
to Wordpress themes.

== Description ==


Allows users to use Openname Avatars for the Wordpress Avatar and add Openname data
to Wordpress themes.

Openname is a blockchain-based (the technology behind Bitcoin), decentralized
identity system.

Your Wordpress avatar will be kept in sync (with a short delay) with your Openname
Avatar.

For more information visit:

[Openname](https://openname.org)

== Installation ==

1. Upload `openname` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit your user profile page, add your Openname and enable your Openname avatar.


You can add Openname information to your theme as follows:

`<?php $person = Openname("larry"); // load the Openname
        echo $person->name_formatted(); ?>`
