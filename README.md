# Symfony2 skeleton application
**Intention**: Make a modifyable application skeleton.
Powered by **bootstrap3**, **grunt**, **bower** and all the best tools available today.

## Development requirements
- linux, mac machine
- php 5.4 or higher
- nodejs - grunt and bower
- composer

## Installation
Install composer dependencies:

    composer install

Install grunt and bower globally (requires root permissions):

    npm install -g grunt-cli bower

Install bower and npm dependencies:

    bower install
    npm install

Build all assets:

    grunt

Reload application database.. Runs migrations and fixtures.

    bin/reload dev

Start serving application in **dev** environment:

    bin/webserver start dev

## Release package
To prepare a release archive run:

    grunt release {/path/to/install/app_archive.tar.gz}

## Notes
- It is advisable to adapt skeleton sources to application needs
- If there are two applications like **admin** and **client** - **AppBundle** can be moved into separate repository.
It will install the same binaries and have migrations and fixtures ready.

