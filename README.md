Symfony Second Project 
---Clean Version---
$ cd c:/xampp/htdocs
$ composer create-project symfony/skeleton:"6.3.*" my_project_directory
$ cd my_project_directory
$ php -S localhost:3000 -t public

-CONTROLLER
$ composer require symfony/maker-bundle --dev
$ composer require annotations
$ symfony console make:controller MoviesController

-VIEW
$ comoser require twig

-DB
$ composer require symfony/orm-pack -W
y
$ symfony console list doctrine
$ composer require symfony/orm-pack
$ composer require --dev symfony/maker-bundle
//in .env
DATABASE_URL="mysql://root:@127.0.0.1:3306/second-sy?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
$ symfony console doctrine:database:create

-MODEL : ENTITY + REPO

-ENTITY
$ symfony console make:entity
$ symfony console make:migration
$ symfony console doctrine:migrations:migrate

-FIXTURES //Dummy Data
$ composer require --dev doctrine/doctrine-fixtures-bundle
add data in Fictures files
$ symfony console doctrine:fixtures:load
yes

-ASSETS
$ composer require symfony/webpack-encore-bundle
$ npm install
$ npm run dev
$ composer require symfony/asset

-IMAGES
$ npm i file-loader --save-dev
$ npm run dev

-CREATE FORM
$ composer require symfony/form
$ symfony console make:form MovieFormType Movie
$ composer require symfony/mime
-VALIDATE FORM FIELDS
$ composer require symfony/validator doctrine/annotations

-REGISTER & LOGIN
$ composer require symfony/security-bundle
$ symfony console make:user User
$ symfony console make:migration
$ symfony console doctrine:migrations:migrate
--registration form
$ symfony console make:registration-form
--login form
$ symfony console make:auth
