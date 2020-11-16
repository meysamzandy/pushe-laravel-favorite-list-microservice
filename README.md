## Installation Tips
pooshe is a favorite list for filmgardi.com

### Requirements installation

Install web server (nginx) <br>
Install php >= 7.3  last stable version<br>
Install mariadb last stable version<br>
Install phpmyadmin -last stable version<br>
Install the composer v2<br>
Enable the event on mariadb<br>

### Requirements Command
run `git clone http://git.novincinema.com/ayeneh/pooshe.git` <br>
run `cd /var/www/pooshe` <br>
run `composer install` <br>
Duplicate .env.example to .env <br>
configure mysql connection on .env <br>
run `php artisan key:generate` <br>
run `php artisan migrate` <br>

