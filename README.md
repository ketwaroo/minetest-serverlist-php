# minetest-serverlist-php

Attempt at a PHP implementation of minetest serverlist.
Basically a quick and dirty port of https://github.com/minetest/serverlist

Bunch of things not implemented yet. Mostly useful if you want a private
serverlist on your LAN and have PHP webserver working already but don't want
to bother setting up python.

Works for now.

### Setup

Requires an initial `composer install`. Might make a release with all in one package.

Copy `app/config.example.php` to `app/config.php` and edit as needed.

Change your `serverlist_url` in your minetest config to point to the `index.php` file.

for example `http://local-homeserver/minetest-serverlist-php/public/index.php`

This should result in minetest hitting various API endpoints in `PATH_INFO` style: 

* http://local-homeserver/minetest-serverlist-php/public/index.php/list
* http://local-homeserver/minetest-serverlist-php/public/index.php/announce

