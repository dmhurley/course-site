course-site
===========

Welcome to a GitHub Repository for the University of Washington Biology Department. Continue reading to learn how to properly clone this repository to your computer and what Bundles are included.

1) Cloning this Repository
------------------------------

#### Requirements
* PHP version 5.4.1+ must be installed
* MySQL must installed. Or at least another doctrine compatible database.
* The latest version of mcrypt and libmcrypt must be installed.
* The permissions of `app/cache`, `app/log`, and `web/files` [***must*** be set correctly](http://symfony.com/doc/current/book/installation.html#configuration-and-setup)
* if apache does not follow symlinks. Assets must be installed with `php app/console assets:install`

The best way to get this repository running on your computer is to use Composer. If you don't have Composer installed you can run the following commands from the project directory to install the various Bundles this project uses.

		curl -s http://getcomposer.org/installer | php

		php composer.phar update
		

You will be prompted to input various database options.



Before you try running this project, make sure your system is properly configured by running this command from project directory

		php app/check.php
		
Once all major problems are fixed you're ready to set up the project.

#### The Easy Way

		php app/console bio:setup

This command will do the complete basic setup for you. Install bundles & assets, create the database tables and columns, and initialize any entities that are necessary for the site to run Finally the command will store the username and hashed password in `app/config/security.yml` allowing you to access the admin pages. Set the bundles you want to install in `app/config/parameters.yml`.

#### The Hard Way

If for some reason `app/console bio:setup` does not work, you can do the setup yourself. From the main directory run

		php app/console doctrine:database:create
		
		php app/console doctrine:schema:create
		
		php app/console bio:install --no-clear
		
		php app/console assets:install --symlink
		
		php app/console assetic:dump --env=prod

to create the database and tables; enable the desired bundles; and install and dump public resources.

You must then persist at least these entities.
``` php
		$info = new Info();
		$info->setCourseNumber(999)
			->setTitle('Biologiology')
			->setQtr('summer')
			->setYear(2013)
			->setDays(array('m', 'w', 'f'))
			->setStartTime(new \DateTime())
			->setEndTime(new \DateTime())
			->setBldg("HCK\tHitchcock Hall")
			->setRoom('120')
			->setEmail('fakeemail@gmail.com');

		$root = new Folder();
		$root->setName('root')
			->setPrivate(false);

		$instructor = new Person();
		$instructor->setfName('John')
			->setlName('Doe')
			->setEmail('johndoe@gmail.com')
			->setBldg("HCK\tHitchcock Hall")
			->setRoom('101')
			->setTitle('instructor');

		$examGlobal = new ExamGlobal();
		$examGlobal->setGrade(2)
			->setRules("Exam rules go here.");

		$tripGlobal = new TripGlobal();
		$tripGlobal->setOpening(new \DateTime())
			->setClosing(new \Datetime())
			->setMaxTrips(1)
			->setEvalDue(5)
			->setPromo('Trip promo goes here.')
			->setInstructions('Trip instructions go here.');')
```
Make sure that the root folder has an Id of `1`, or it will not be recognized.

#### Optional Bundles

There are several optional bundles included with the site that are by default not enabled. Use the command 

		php app/console bio:install [-d|--default] [-a|--all] [--no-clear] [bundles1] ... [bundlesN]
		
to install them. `bundles` should be replaced with any combination of `info`, `folder`, `student`, `clicker`, `score`, `exam` `trip`, `switch`, and `user`. This command adds the necessary lines to `app/config/sidebar.yml` and `app/config/routing.yml`. The `-a` shortcut install alls available bundles, while `-d` installs only the default. If no bundles are specified, whatever bundles are set in `app/config/parameters.yml` will be used. After the installation has been completed the command attempts to clear the cache (unless `--no-clear` is enabled). Usually this step will fail and the cache will have to be cleared manually.

#### Other Commands

* ###### Update Site
	To update the site after making major changes or especially after pulling updates from this repository run the command:
		
			php app/console bio:update
	
	This command makes sure that all new assets are installed and dumped, all changes to the sidebar take effect, and automatically migrates the database as necessary (TODO). All sessions will be removed after calling this command.

* ###### Create User
	If you need to add an account, you can run the command
	
			php app/console bio:create:account [--username=username] [--password=password] [--role=ROLE]
			
	to create one. You will be prompted by the command line for any options you don't fill in.
	
	Four roles are possible, `ROLE_USER`, `ROLE_ADMIN`, `ROLE_SUPER_ADMIN`, and `ROLE_SETUP`. With each role inheriting the permissions of the previous.
 
 1. `ROLE_USER`: can log in and that's it. All newly registed accounts start at this role.
 2. `ROLE_ADMIN`: can edit all aspects of the site ***except*** for other users.
 3. `ROLE_SUPER_ADMIN`: can promote, demote, or delete users.
 4. `ROLE_SETUP`: Used for debugging. Can't be seen or deleted on the User admin screen. Can [switch roles](http://symfony.com/doc/current/book/security.html#impersonating-a-user).


* ###### Trip Email Reminder

	This command is made to be run as a cron job once every 24 hours. It finds students who are signed up for a trip that has passed and have not yet evaluated it. It sends an email 5 days after their trip, and twice again the two days before evaluations close. The command is:
	
			php app/console bio:email

#### Notes

* If `bio:setup` fails after creating the database. Make sure to drop the database before running it again.

* The order of the sidebar will be the same order you write the bundle names down. You can use `bio:install` to change the order or move the lines in `app/config/sidebar.yml` manually.

* bio:install will automatically install the `user` bundle at the end if it has not already been added. This is necessary to access any of the admin content.
