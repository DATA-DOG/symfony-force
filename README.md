[![Build Status](https://travis-ci.org/DATA-DOG/symfony-force.png?branch=master)](https://travis-ci.org/DATA-DOG/symfony-force)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DATA-DOG/symfony-force/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DATA-DOG/symfony-force/?branch=master)

# Symfony Force Edition

**Intention**: Make a common symfony2 bootstrap application skeleton.
Powered by **bootstrap3**, **grunt**, **bower** and all the best tools available today.
The environment may be setup with **docker**, **vagrant** or local installation. A team should clone
the project and strip it down to whatever works for them.

<p align="center"><img src="https://raw.github.com/DATA-DOG/symfony-force/master/starwars.gif" alt="Symfony force" /></p>

## Screenshot

<p align="center"><img src="https://raw.github.com/DATA-DOG/symfony-force/master/screenshot.png" alt="Screenshot" /></p>

## Like a ride in Disneyland

<p align="center"><img src="http://33.media.tumblr.com/951808f2d7e4a809101199e9d07d1888/tumblr_inline_nwfybcXTvP1raprkq_500.gif" alt="Darth vader in disneyland" /></p>

## Development requirements

- **linux**, **unix** and probably **windows**
- **PHP 5.6** or higher
- **redis** for test and prod environments
- nodejs - [grunt](http://gruntjs.com/) and [bower](http://bower.io/)
- [composer](https://getcomposer.org/)

## What is specific?

- Does not allow two **flushes** within a request, unless a manual transaction was started. Prevent bad design and data inconsistencies.
- **behat** is configured to run scenarios within transaction, it saves about 70% of time for functional tests.
Additionally, that allows to run **behat** concurrently using [beflash](https://github.com/DATA-DOG/beflash.git)
which would in total run **85% faster** on 4 cores.
- uses twig to manage basic **CMS** related requirements.
- **test** environment acts like **prod** in order to see profiler set `$debug` to true in **web/app_test.php**.

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

    app/console server:run

### Bower

Bower allows you to prevent cluttering your **VCS** source tree with **js, css, font** and other asset library sources.
Edit **bower.json** to manage your asset dependencies.

### Less

Less allows you to organize and minify your css sources, having **grunt** or **gulp** inline helps to compile and build all
the necessary asset sources for production or development use.

See **assets/less/** for more details and [lesscss](http://lesscss.org/) for usage reference.

### Grunt

Grunt is a build tool. In current application, it:

- compiles assets for both production or development environment
- compiles twig layout template, to inject version number
- watches for source changes and rebuilds on change
- creates a minified release **tar.gz** package for production use.

Edit **package.json** to manage grunt dev dependencies and **gruntfile.js** to manage project build configuration.

## Release package

To prepare a release archive **ansible/frontend.tar.gz** run:

    grunt release

Why release package is better than cloned source? First of all, you do not need any tools to prepare your source
on production servers, etc.: nodejs, composer, git. Second, network may fail on deployment while downloading third party
dependencies.

**NOTE:** when a release archive is being built, it uses **.tarignore** file to exclude files and directories which
are not necessary for production use.

## Testing

You can run all tests from **grunt**:

    grunt test

For testing initially there is **phpunit** and **behat** as default options. You may change to **phpspec** or
anything else of your preference.

    bin/phpunit -c app

### Behat

See [behat3](http://docs.behat.org/en/latest/) for reference.
To run behat tests:

    bin/reload test
    bin/behat

## Application

What comes with this skeleton application.

- migrations and fixtures. Fixtures are executed once as a data migration subject, they are ordered and environment
specific. [Migrations](http://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html) are located in **AppBundle**
which may be moved to separate repository if database is a shared resource. Fixtures and migrations are **idempotent**,
meaning it cannot be included twice.
- basic security settings: login, logout, signup and confirmation by email, profile, reset password. Should be modified
on custom basis.
- [doctrine extensions](https://github.com/Atlantic18/DoctrineExtensions) - only timestampable is activated by default.
See **app/config/doctrine_extensions.yml**.
- a command to check **anonymously accessible routes** all commands are under **app** namespace.

### Binaries

Application installs some convenient binary executables on composer install|update hooks.

#### Reload

**reload** - reloads your application datasources in the order: drop database(if available), create database, run migrations,
load fixtures, clear and warmup cache. These binaries are located in **app/Resources/bin** and may be adapted
for custom usage.

    bin/reload test

Would reload application for **test** environment. Default is **dev** as usual in symfony2 application.

## Vagrant

Currently **Vagrant** provisions with **ansible** and deploys into a VirtualBox centos linux machine.
In order to run successfully, you will need **virtualbox, ansible, vagrant** installed on your system.

    vagrant up

## Notes

- It is advisable to adapt skeleton sources to application needs
- If there are two applications like **admin** and **client** - **AppBundle** can be moved into separate repository.
It will install the same binaries and have migrations and fixtures ready.

## TODO

- File upload service and entity + profile image.

## API JWT

To authenticate through API and get JWT:

    curl -X POST http://localhost:8000/api/authenticate --header 'Content-Type:application/json' --data '{"username": "joda@datadog.lt", "password": "S3cretpassword"}' -i


