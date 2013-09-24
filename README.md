course-site
===========
<img align="right" width="200px" src="https://raw.github.com/dmhurley/course-site/master/src/Bio/PublicBundle/Resources/public/images/mainpage.png">
Welcome to a GitHub Repository for the University of Washington Biology Department. Continue reading to learn how to properly clone this repository to your computer and what Bundles are included.

1) Cloning this Repository
------------------------------

#### Requirements
* PHP version 5.4.1+ must be installed
* MySQL must installed. Or at least another doctrine compatible database.
* The latest version of mcrypt and libmcrypt must be installed.
* The permissions of `app/cache`, `app/log`, `web/bundles`, and `web/files` [***must*** be set correctly](http://symfony.com/doc/current/book/installation.html#configuration-and-setup)
* If apache does not follow symlinks. Assets must be installed with `php app/console assets:install`

The best way to get this repository running on your computer is to use Composer. If you don't have Composer installed you can run the following commands from the project directory to install the various Bundles this project uses.

		curl -s http://getcomposer.org/installer | php

		php composer.phar update
		

You will be prompted to input various database options.



Before you try running this project, make sure your system is properly configured by running this command from project directory

		php app/check.php
		
Once all major problems are fixed you're ready to set up the project.

		php app/console bio:setup

This command will do the complete basic setup for you. Install bundles & assets, create the database tables and columns, and initialize any entities that are necessary for the site to run Finally the command will store the username and hashed password in `app/config/security.yml` allowing you to access the admin pages. Set the bundles you want to install in `app/config/parameters.yml`.

2) Custom Styles
------------------------

You can change the basic appearance of the site very easily with the main style sheet `src/Bio/PublicBundle/Resources/public/css/page.css`. Every rule regarding background, font, header images, and other basic style choices are at the top of the file, in logical groups with identifying comments. It may also be necessary to change the `border-color` at the top of `src/Bio/PublicBundle/Resources/public/css/form.css`.

Changing only a few lines at the top of `page.css`, it is possible to go from this to this:
<img src="http://i.imgur.com/03vb6m3.png" width=350 align="center"> <img src="http://i.imgur.com/giOhGa3.png" width=350 align="center">

Edit the `href` of the image-links in the header at `src/Bio/PublicBundle/views/main.html.twig`. Edit other portions of the css/html at your own risk.

3) Other stuff
-------------------------

#### Commands
In addition to `bio:setup` there are several other console commands to make your life easier.

* ###### Install Bundles

	There are several optional bundles included with the site that are by default not enabled. Use the command 

			php app/console bio:install [-d|--default] [-a|--all] [--no-clear] [bundles1] ... [bundlesN]
		
	to install them. `bundles` should be replaced with any combination of `info`, `folder`, `student`, `clicker`, 	`score`, `exam` `trip`, `switch`, and `user`. This command adds the necessary lines to `app/config/sidebar.yml` and 	`app/config/routing.yml`. The `-a` shortcut installs all available bundles, while `-d` installs only the default. If no bundles are specified, whatever bundles are set in `app/config/parameters.yml` will be used. After the installation has been completed the command attempts to clear the cache (unless `--no-clear` is enabled). Usually this step will fail and the cache will have to be cleared manually.

* ###### Update Site
	To update the site after making major changes or especially after pulling updates from this repository run the command:
		
			php app/console bio:update
	
	This command makes sure that all new assets are installed and dumped, all changes to the sidebar take effect, and automatically migrates the database as necessary. All sessions will be removed after calling this command.

* ###### Create User
	If you need to add an account, you can run the command
	
			php app/console bio:create:account [--username=username] [--password=password] [--role=ROLE]
			
	to create one. You will be prompted by the command line for any options you don't fill in.
	
	Four roles are possible, `ROLE_USER`, `ROLE_ADMIN`, `ROLE_SUPER_ADMIN`, and `ROLE_SETUP`. With each role inheriting the permissions of the previous.
 
 1. `ROLE_USER`: can log in and that's it. All newly registered accounts start at this role.
 2. `ROLE_ADMIN`: can edit all aspects of the site ***except*** for other users.
 3. `ROLE_SUPER_ADMIN`: can promote, demote, or delete users.
 4. `ROLE_SETUP`: Used for debugging. Can't be seen or deleted on the User admin screen. Can [switch roles](http://symfony.com/doc/current/book/security.html#impersonating-a-user).


* ###### Trip Email Reminder

	This command should be run by a cron job or scheduled task once every 1 hour. It finds students who are signed up for a trip that has passed and have not yet evaluated it. It sends two to three evenly spaced personalized reminder emails before a set number of days after. The command is:
	
			php app/console bio:email

#### Scripts

* ###### Update

	`update.sh` automatically pulls in the most recent changes from the master branch, updates the vendors, and updates the site and database as necessary.

#### Notes

* If `bio:setup` fails after creating the database. Make sure to drop the database before running it again.

* The order of the sidebar will be the same order you write the bundle names down. You can use `bio:install` to change the order or move the lines in `app/config/sidebar.yml` manually.

* `bio:install` will automatically install the `user` bundle at the end if it has not already been added. This is necessary to access any of the admin content.

* If `bio:email` is run more or less frequently then once per hour, emails may not send or may be sent many times in an hour.

* This project uses Doctrine Migrations to preserve information in the database when updating. Only up migrations are supported, down migrations will most likely cause problems.
