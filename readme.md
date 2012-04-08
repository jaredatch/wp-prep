# wp-prep 

**Author**:  Jared Atchison ( [@jaredatch](http://twitter.com/jaredatch ) / [jaredatchison.com](http://jaredatchison.com/) )  
**Version**: 1.0      
**License**: GPLv2  

## Description

I typically setup 2-4 new development sites a week. While this process doesn't take long, I was doing thing same thing over and over again. Each setup required the same files and plugins to be uploaded - I wanted to automate the process.
This script was the result.

This script is ideal for people who have a dedicated area for development, e.g. `YourDevelopmentArea.com` - however it could be used in smaller use cases as well.

What's here is the first primary pass. There are likely bugs and optimizations that can be made - forks and pull requests are encouraged.

Lastly, depending on your host this may only partially work or may not even work at all.

##### Some nifty features include:

* Ability to download the latest stable version of WordPress - or if you are adventurous - use trunk. It can be toggled with the click of a link. 
* Create and install to a directory - e.g. `YourDevelopmentArea.com/acme-corp`
* Auto upload the Genesis Framework
* Auto upload base theme (with supports GitHub repos)
* Auto upload batch plugin installer (see `plugin-install.txt`)
* Delete Hello Dolly
* Delete TwentyTen/Eleven
* Delete wp-prep.php after completion

##### Links

* [Github project page](http://github.com/jaredatch/wp-prep)

This script is loosely based off of [WP Downloader](http://www.farinspace.com/wordpress-downloader/) - just _greatly_ expanded upon.

## Installation

###### configure wp-prep.php
* First and most importantly, **set a new password** (line 16)
* Assuming you want to use Genesis, a base theme, and batch plugin install - configure those URLs (line 17-19)

###### configure plugin-install.txt
* This plugin will provide the ability to batch install/activate other plugins
* Follow the template to modify the plugin-install.txt as you see fit

###### Profit. Once configured, visit wp-prep.php

##Screenshots

![WordPress admin dashboard](https://github.com/jaredatch/wp-prep/raw/master/screenshot.png "WordPress admin dashboard")


### Changelog

##### 1.0
* Initial release