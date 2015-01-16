# Symfony2 skeleton application
**Intention**: Make a modifyable application skeleton.
Powered by **bootstrap3**, **grunt**, **bower** and all the best tools available today.

## Development requirements
- linux, mac machine
- php 5.4 or higher
- nodejs - [grunt](http://gruntjs.com/) and [bower](http://bower.io/)
- [composer](https://getcomposer.org/)

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

### .editorconfig

See [editorconfig.org](http://editorconfig.org/) for more details and plugins available for an **Idea** of your choice.
In general **editorconfig** is a preconfiguration of indentation and general rules for various file types. Be free to
update it based on your team's preferences.

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
To prepare a release archive run:

    grunt release {/path/to/install/app_archive.tar.gz}

Why release package is better than cloned source? First of all, you do not need any tools to prepare your source
on production servers, etc.: nodejs, composer, git. Second, network may fail on deployment while downloading third party
dependencies.

**NOTE:** when a release archive is being built, it uses **.tarignore** file to exclude files and directories which
are not necessary for production use.

## Testing
For testing initially there is **phpspec** and **behat** as default options. You may change to **phpunit** or
anything else of your preference.

### PHPSpec
See [phpspec](http://www.phpspec.net/) for reference.

    bin/phpspec run -fpretty
    bin/phpspec run spec/AppBundle/Entity

### Behat
See [behat3](http://docs.behat.org/en/latest/) for reference.
To run behat tests:

    bin/reload test
    bin/webserver restart test
    bin/behat

Tests are using **sqlite** database. If you need to check database after a failed scenario:

    sqlite3 app/logs/test.db

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

#### Webserver
**webserver** - start|restart|stop webserver based on environment. **bin/webserver start prod** will start php internal
webserver listening on port **5550** in production environment.

    bin/webserver restart dev

Would restart webserver on the same port, but for the dev environment. It is convenient, because you do not need to
configure nginx or apache for development use.

#### Reload
**reload** - reloads your application datasources in the order: drop database(if available), create database, run migrations,
load fixtures, clear and warmup cache. These binaries are located in **src/AppBundle/Resources/bin** and may be adapted
for custom usage.

    bin/reload test

Would reload application for **test** environment. Default is **dev** as usual in symfony2 application.

#### Selenium
**selenium** binary is used to download(if not yet available) a standalone selenium server and start|stop|restart it on
default port. It is useful if you have behat features dependent on javascript, which is not advisable if possible avoid it.

#### Archive
**archive** binary is used to archive an application to **tar.gz** for production use in order to upload and deploy it
without any dependencies.

    bin/archive /directory/release.tar.gz

## Notes
- It is advisable to adapt skeleton sources to application needs
- If there are two applications like **admin** and **client** - **AppBundle** can be moved into separate repository.
It will install the same binaries and have migrations and fixtures ready.

## TODO

- Style the starwars theme better..
- Vagrant or Docker setup with Ansible playbook
- Two-factor authentication with [google authenticator](https://github.com/rchouinard/rych-otp)
- File upload service and entity + profile image.

