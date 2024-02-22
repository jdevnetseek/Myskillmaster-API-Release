# API MySkillMaster

Latest commit from BP: [3071852fa1](https://gitlab.com/appetiser/baseplate-api/-/commit/3071852fa1ce646ccc0ed7e6616170da88a9b398))

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

## Laravel Version
Laravel 9.x

## Minimum PHP Version
* PHP 8.1
## Prerequisites

php extensions

* BCMath PHP Extension
* Ctype PHP Extension
* JSON PHP Extension
* Mbstring PHP Extension
* OpenSSL PHP Extension
* PDO PHP Extension
* SQLite 3 PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* Exif PHP Extension

### Installing

A step by step series of examples that tell you how to get a development env running

* Make sure you have composer installed on your local machine `composer install`
* For windows user like for those who use laragon, you can run `composer install --ignore-platform-reqs` to ignore `laravel/horizon`'s requirement for `ext-pcntl`
* Create .env file from .env.example `cp .env.example .env`
* Update .env based on your local configuration
* Generate application key `php artisan key:generate`
* Run migration `php artisan migrate`
* You can serve the application on LAMP/XAMPP/LEMP stack or use its builtin server `php artisan serve`
* To seed sample data run `php artisan db:seed --class=DevelopmentSeeder`

### Installing using Docker
Please read the [docker.md](/docker.md) file.

## Commands

* `php artisan app:initialize:dev` - This will run all the commands needed to kick start development. Feel free to modify this commands in `routes/console.php`
* `php artisan app:acl:sync` - This will sync user roles and permissions that defined in `app/Enums/Role.php` and `app/Enums/Permission.php` to the database. code can be found in `app/Console/Commands/AclSync.php`

## Testing

For testing, Configuration can be found in `.env.testing`

* We need to add database and test user that will going to use in running testing
```mysql
CREATE DATABASE mysql_test_database;
CREATE USER 'mysql_test_user'@'localhost'
  IDENTIFIED
  	WITH mysql_native_password
  	BY 'mysql_test_password';
GRANT ALL
  ON mysql_test_database.*
  TO 'mysql_test_user'@'localhost'
  WITH GRANT OPTION;
```
* To run the test, run this on your terminal `vendor/bin/phpunit`
* To create test, you need to mimic the file directory of the code you want to test. Please see `test/` folder for example

## Coding Standards

We're using PSR2 for coding standards, configuration can be found in `phpcs.xml`
* To check if your code follow the standard, run this on your terminal `vendor/bin/phpcs`

## Gitlab CI

We're using gitlab-ci for continuous integration and deployments, configuration can be found in `.gitlab-ci.yml`

Gitlab-ci will automatically run when you push to master or develop branch, this will run `phpunit` to check if your commit passes every test and also run `phpcs` to check for coding standards. This can be viewed on your project [pipelines](https://gitlab.com/appetiser/baseplate-api/pipelines). Also push to deploy can be configured here.

## Libraries

* [https://github.com/BenSampo/laravel-enum](https://github.com/BenSampo/laravel-enum)
* [https://github.com/spatie/laravel-cors](https://github.com/spatie/laravel-cors)
* [https://github.com/spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary)
* [https://github.com/spatie/laravel-permission](https://github.com/spatie/laravel-permission)
* [https://github.com/spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder)
* [https://github.com/spatie/laravel-queueable-action](https://github.com/spatie/laravel-queueable-action)
* [https://github.com/spatie/laravel-settings](https://github.com/spatie/laravel-settings)
* [https://github.com/tymondesigns/jwt-auth](https://github.com/tymondesigns/jwt-auth)
* [https://github.com/staudenmeir/eloquent-json-relations](https://github.com/staudenmeir/eloquent-json-relations)
* [https://github.com/laravel/cashier-stripe](https://github.com/laravel/cashier-stripe)
* [https://github.com/laravel/horizon](https://github.com/laravel/horizon)
* [https://github.com/laravel/sanctum](https://github.com/laravel/sanctum)
* [https://github.com/laravel/telescope](https://github.com/laravel/telescope)

## PR
If you found any bugs, issues, typos or you want to add some changes, [merge request](https://docs.gitlab.com/ee/gitlab-basics/add-merge-request.html) is very much appreciated. Thanks!
