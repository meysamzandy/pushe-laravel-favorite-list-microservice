## Installation Tips
pushe is a favorite list microservice

### Requirements installation

Install web server (nginx) <br>
Install php >= 7.3  last stable version<br>
Install mariadb last stable version<br>
Install phpmyadmin -last stable version<br>
Install the composer v2<br>
Enable the event on mariadb<br>

### Requirements Command
run `git clone http://git.novincinema.com/ayeneh/new_pushe.git` <br>
run `cd /var/www/pooshe` <br>
run `composer install` <br>
Duplicate .env.example to .env <br>
configure mysql connection on .env <br>
add below lines to .env <br>
`DECRYPT_KEY ='FK2nCxJopir5iQ'`<br>
`DECRYPT_IV ='Xai8sh9k7EdfGv'`<br>
`ANONYMOUS ='f0483750-665f-43c9-b2ca-24e7d26f4049'`<br>
run `php artisan key:generate` <br>
run `php artisan migrate` <br>

