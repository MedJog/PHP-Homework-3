<?php

require_once('vendor/autoload.php');

// вызов корневой функции
$result = main("/code/config.ini");
// вывод результата
echo $result; 

// команды в проекте
// composer install
// docker build -t myapp/php-cli ./php-cli
// docker run -it -v ${pwd}/php-cli/code:/code myapp/php-cli php /code/app.php help
// docker run -it -v ${pwd}/php-cli/code:/code myapp/php-cli php /code/app.php add
// docker run -it -v ${pwd}/php-cli/code:/code myapp/php-cli php /code/app.php del
// docker run -it -v ${pwd}/php-cli/code:/code myapp/php-cli php /code/app.php clear
// docker run -it -v ${pwd}/php-cli/code:/code myapp/php-cli php /code/app.php read-profiles
// docker run -it -v ${pwd}/php-cli/code:/code myapp/php-cli php /code/app.php read-profile 
