# About
A mini API for Aspire(https://aspire-cap.com) on creating and managing loans and repayments.

# First Run
* Change directory into miniAspire folder
* Copy and Rename: {.env.example} to {.env}
* Run Command: composer install
* Run Command: composer update
* 
* Create DB with name {miniaspire}
* Change DB credentials in {.env}
* Run Command: php artisan migrate

# Test Mail
* URL: Mailtrap (https://mailtrap.io)
* Email: admin@jefferyclonne.com
* Password: {check_your_email}

# Test API
* Run Command : php -S localhost:8000 miniAspire/
* Or setup LAMP / Homestead for better performance
* URL : http://localhost/miniAspire/api/{route} / http://miniAspire.test/api/{route}

# Note
* Please check {miniAspire/public/demo} for the steps and field requirements for testing