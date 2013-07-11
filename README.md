course-site
===========

Welcome to a GitHub Repository for the University of Washington Biology Department. Continue reading to learn how to properly clone this repository to your computer and what Bundles are included.

1) Cloning this Repository
------------------------------

#### Requirements
* The latest version of mcrypt and libmcrypt must be installed.
* PHP version 5.4.1+ must be installed
* The permissions of app/cache and app/log [***must*** be set correctly](http://symfony.com/doc/current/book/installation.html#configuration-and-setup)
* if apache does not follow symlinks. Assets must be installed with `php app/console assets:install`

The best way to get this repository running on your computer is to use Composer. If you don't have Composer installed you can run the following commands from the project directory to install the various Bundles this project uses.

		curl -s http://getcomposer.org/installer | php

		php composer.phar update
		

You will be prompted to input various database options.



Before you try running this project, make sure your system is properly configured by running this command from project directory

		php app/check.php
		
Once all major problems are fixed you're ready to set up the project.

#### The Easy Way

		php app/console bio:setup username password

This command will do the complete basic setup for you. Install assets, create the database tables and columns, and initialize any entities that are necessary for the site to run Finally the command will store the username and hashed password in `app/config/security.yml` allowing you to access the admin pages.

#### The Hard Way

If for some reason `app/console bio:setup` does not work, you can do the setup yourself. From the main directory run

		php app/console assets:install --symlink

		php app/console doctrine:database:create

		php app/console doctrine:schema:create

to install the public resources for assetic, create the database, and create the tables respectively.

You must then persist at least these entities.

		$info = new Info();
		    $info->setCourseNumber(999)
		        ->setTitle('Biologiology')
		        ->setQtr('summer')
		        ->setYear(2013)
		        ->setDays(array('m', 'w', 'f'))
		        ->setStartTime(new \DateTime())
		        ->setEndTime(new \DateTime())
		        ->setBldg('KNE	Kane Hall')
		        ->setRoom('120')
		        ->setEmail('fakeemail@gmail.com');


		$folder = new Folder();
		    $folder->setName('root');

Make sure that the root folder has an Id of `1`, or it will not be recognized.

#### Notes

* If `bio:setup` fails after creating the database. Make sure to drop the database before running it again.

* If you need to add an account, you can run the command

		php app/console bio:create:account username password [ROLE_USER|ROLE_ADMIN|ROLE_SUPER_ADMIN|ROLE_SETUP]

	or add the necessary information in `app/config/security.yml` yourself.
	
* Four roles are possible, `ROLE_USER`, `ROLE_ADMIN`, `ROLE_SUPER_ADMIN`, and `ROLE_SETUP`. With each role inheriting the permissions of the previous.
    1. `ROLE_USER`: can log in and that's it. All newly registed accounts start at this role.
    2. `ROLE_ADMIN`: can edit all aspects of the site ***except*** for other users.
    3. `ROLE_SUPER_ADMIN`: can promote, demote, or delete users.
    4. `ROLE_SETUP`: Used for debugging. Can't be seen or deleted on the User admin screen. Can switch roles.


2) Bundles
------------------------------

* Student Bundle
    + Allows adding, deleting, and editing students. Along with uploading lists in the .csv format.
* Style Bundle
    + Responsible for the look and feel of the site.
    + All other bundles extend it's main.html.twig view.
* Clicker Bundle
    + Allows the registration of clickers. Along with downloading a list of registered clickers and clearing all registrations.
* Info Bundle
    + working on it....
