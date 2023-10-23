# Dotenv
### use .env files in your projects

> With **Dotenv** you have the opportunity to use environment variables directly in your project. 
> You can parse **.env** files and add them to the super global variables ***$_ENV*** and ***$_SERVER***. 
> You can also specify default values with **Dotenv**. These values are used if there is no environment variable. 
> This makes it easier to check for errors and use standard configurations.
> 
> You also have the option to define nested variables in the environment file

---
## Installation
```shell
$ composer require nexus633/dotenv
```
---
## usage
```php
<?php

require 'vendor/autoload.php';
use Nexus633\Dotenv\Dotenv;

$env = new Dotenv();
```
```php
<?php

require 'vendor/autoload.php';
use Nexus633\Dotenv\Dotenv;

/**
 * with options
 * the options in this example are the default values 
 */

$env = new Dotenv([
    'set_locale_first' => true,
    'set_exceptions' => true,
    'set_global_env' => false,
    'set_global_server' => false,
    'set_global_key' => false
]);
```
## Options
> If this option is set to true, the environment variables from .env file will be set as first element before add super global $_ENV.
> ```php
>    'set_locale_first' = true
>```

> If this option is set to true, a fileNotFoundException will be thrown if no .env file is present.
> ```php
>    'set_exceptions' = true
>```
> If the option is set to false, only the super global values from $_ENV are reflected.
> ```php
>    'set_exceptions' = false
>```

> If this option is set to true, the environment variables from .env file will be added to the super global variable $_ENV.
> ```php
>    'set_global_env' = true
>```

> If this option is set to true, the environment variables from .env file will be added to the super global variable $_SERVER.
> ```php
>    'set_global_server' = true
>```

> When this option is set to true, a new key named DOTENV is added to the super global variable $_ENV. This key then contains the variables from the .env file
> ```php
>    'set_global_key' = true
>```

---
### get environment variables
```dotenv
# this .env file is a example
MODE=live
HOME_PATH=/var/www
LOG_PATH=${HOME_PATH}/log
ERROR_LOG=${LOG_PATH}/error.log
```

```php
<?php

echo $env->processenv('LOG_PATH');
// if HOME_PATH is set then the result is `(string) /var/www/log` else empty string

echo $env->processenv('LOG_PATH', '/var/www/log');
// if HOME_PATH is set then the result is `(string) /var/www/log` else `(string) /var/www/log` as fallback value

print_r($env->processenv());
// get all environment variables
```

### use magic methods
```php
<?php

echo $env->HOME_PATH;
// if HOME_PATH is set, then the result is `(string) /var/www/log` else empty string
```
### END
> Did you find any suggestions or bugs? Make a pull request or ask your question :-)
