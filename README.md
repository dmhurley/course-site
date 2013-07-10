course-site
===========

Welcome to a GitHub Repository for the University of Washington Biology Department.
Continue reading to learn how to properly clone this repository to your computer
and what Bundles are included.

1) Cloning this Repository
------------------------------

The best way to get this repository running on your computer is to use Composer. 
If you don't have Composer installed you can run the following commands 
from the project directory:

		curl -s http://getcomposer.org/installer | php

to install Composer, and:

		php composer.phar update

to install the various vender Bundles this project uses.

You will be prompted to input various database options and it will fail after it is
done. To fix this edit app/parameters.yml and add

		database_socket: path/to/database.sock

If you do not need this to specify the database socket. Remove the line

		unix_socket: %database_socket%

from the app/config.yml file.

Before you try running this project, make sure your system is properly configured
by running this command from project directory

		php app/check.php
		
Then run:

		php app/console bio:setup username password

This command will do the complete basic setup for you. Installing assets, creating
the database tables and columns, and initializing any entities that are necessary.
Finally the command will store the username and hashed password in app/config/security.yml
allowing you to access the admin pages.

If for some reason bio:setup does not work, you can do the setup yourself. From the main
directory run

		php app/console assets:install --symlink

		php app/console doctrine:database:create

		php app/console doctrine:schema:create

to install the public resources for assetic, create the database, and create the tables
respectively.

You must then persist several entities.

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

Make sure that the root folder has an Id of 1, or it will not be recognized.

If you would need to add an account, you can run the command

		php app/console bio:create:account username password [ROLE_ADMIN|ROLE_SUPER_ADMIN]

or add the necessary information in app/config/security.yml yourself.


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
