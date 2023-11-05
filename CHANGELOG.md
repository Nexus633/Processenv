# version 1.0.0

## 11/05/2023

- initial release of processenv

## Test results

```shell
vendor/bin/phpunit
```

### PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

#### Runtime:       PHP 8.2.10

#### Configuration: /var/www/html/phpunit.xml

Processenv \
✔ Cant find env file [3.07 ms] \
✔ Get value from env file [0.81 ms] \
✔ Get object from env file as std class [0.20 ms] \
✔ Get object from env file as array [0.07 ms] \
✔ Get value from env file as array [0.06 ms] \
✔ Get value from env file as array but is string [0.08 ms] \
✔ Get value from env file in global env [0.11 ms] \
✔ Get value from env file in global server [0.08 ms] \
✔ Get value from env file in global env as key array [0.06 ms] \
✔ Get value from env file in global server as key array [0.10 ms] \
✔ Get all values from env file and global env [0.79 ms] \
✔ Get default value by key not exists [0.07 ms] \
✔ Get empty string by key not exists [0.06 ms] \
✔ Get inline comment value [0.06 ms] \
✔ Get inline comment with masked hash value [0.07 ms]

Time: 00:00.008, Memory: 4.00 MB

OK (15 tests, 18 assertions)
