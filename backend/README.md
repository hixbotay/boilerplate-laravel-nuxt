# Requirements

- PHP 7.4, Composer
- MySQL

# Steps run projects

- Install required packages: ``` composer install ```
- Duplicate file ```.env.example``` and rename it to ```.env```. Change DB config in this file
- Migrate database: ```php artisan migrate```
- [Optional] Seeding data: ```php artisan db:seed```
- Generate JWT secret key: ```php artisan jwt:secret```
- Run project: ```php artisan serve```
- All done!

# Configure queue process running in background
- [Install supervisor on CentOS](https://www.linode.com/docs/guides/how-to-install-and-configure-supervisor-on-centos-8/)
- [Configure for Laravel](https://laravel.com/docs/8.x/queues#supervisor-configuration)

# Configuring Laravel Queues with AWS SQS
- [Link](https://dev.to/ichtrojan/configuring-laravel-queues-with-aws-sqs-3f0n)