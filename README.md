# Processenv

### use .env files in your projects

With **Processenv** you have the opportunity to use environment variables directly in your project. \
You can parse **.env** files and add them to the super global variables ***$_ENV*** and ***$_SERVER***. \
You can also specify default values with **Processenv**. These values are used if there is no environment variable. \
This makes it easier to check for errors and use standard configurations.

You also have the option to define nested variables, arrays and objects in the environment file \
You can use inline comments and masked hashtags (\\#)

## Version 1.0.1

checkout the [Changelog](CHANGELOG.md)

---

## Installation

```shell
$ composer require nexus633/processenv
```

---

## usage

```php
<?php

require 'vendor/autoload.php';
use Nexus633\Processenv\Processenv;

$env = new Processenv();
$env->load();
```

```php
<?php

require 'vendor/autoload.php';
use Nexus633\Processenv\Processenv;

/**
 * with options
 * the options in this example are the default values 
 */

$env = new Processenv(
    new ProcessenvOptions(
        localFirst: true,
        exceptions: true,
        globalEnv: false,
        globalServer: false,
        globalKey: false,
        objectParser: self::PARSE_AS_STDCLASS,
        replacePattern: '[:PROCESSENV_REPLACE:]'
    )
);

$env->load();
```

## Options

> If this option is set to true, the environment variables from .env file will be set as first element before add super
> global $_ENV.
> ```php
>    localFirst = true
>```
>
> If this option is set to true, a fileNotFoundException will be thrown if no .env file is present else only the super
> global values from $_ENV are reflected.
> ```php
>    exceptions = true
>```
>
> If this option is set to true, the environment variables from .env file will be added to the super global variable $_
> ENV.
> ```php
>    globalEnv = true
>```
>
> If this option is set to true, the environment variables from .env file will be added to the super global variable $_
> SERVER.
> ```php
>    globalServer = true
>```
>
> When this option is set to true, a new key named DOTENV is added to the super global variable $_ENV. This key then
> contains the variables from the .env file
> ```php
>    globalKey = true
>```
>
>If this option is set to “ProcessenvOptions::PARSE_AS_STDCLASS”, objects in the .env file will automatically be
> converted to StdClass. \
> If you set the option to ProcessenvOptions::PARSE_AS_ARRAY, objects will be converted into an associative array
> ```php
>     objectParser = ProcessenvOptions::PARSE_AS_STDCLASS | ProcessenvOptions::PARSE_AS_ARRAY
> ```
>
>With this option you can specify how the masked comments are parsed.
> ```php
>     replacePattern = '[:PROCESSENV_REPLACE:]'
> ```
---

### get environment variables

```dotenv
# this .env file is a example
MODE=live
IGNORED=${HOME_PATH}
HOME_PATH=/var/www
LOG_PATH=${HOME_PATH}/log
ACCESS_LOG='${LOG_PATH}/access.log'
ERROR_LOG=${LOG_PATH}/error.log
ERROR_MODE='{ "info": "${LOG_PATH}/info.log", "fatal": "${LOG_PATH}/fatal.log", "exception": "${LOG_PATH}/exception.log" }'
ERROR_MODE_ARRAY='[ "info\\#with masked hash", "fatal", "exception" ]'
INLINE_COMMENT='this is a inline comment #not parsed'
INLINE_COMMENT_WITH_ESCAPE='this is an inline comment \# with masked hash'
```

```php
<?php

echo $env->processenv('LOG_PATH');
/* if HOME_PATH is set then the result is `(string) /var/www/log` else empty string */

echo $env->processenv('LOG_PATH', '/var/www/log');
/* if HOME_PATH is set then the result is `(string) /var/www/log` else `(string) /var/www/log` as fallback value */

echo $env->processenv('INLINE_COMMENT');
/* output: this is an inline comment */

echo $env->processenv('INLINE_COMMENT_WITH_ESCAPE');
/* output: this is an inline comment # with masked hash */

print_r($env->processenv());
/* get all environment variables */
```

### use magic methods

```php
<?php

echo $env->HOME_PATH;
// if HOME_PATH is set, then the result is `(string) /var/www/log` else empty string
```

### END

> Did you find any suggestions or bugs? Make a pull request or ask your question :-)
