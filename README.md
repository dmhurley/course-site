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
		
Then run

		php app/console assets:install --symlink

		php app/console doctrine:schema:create

to install the public resources for assetic and to initialize the database respectively.

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
