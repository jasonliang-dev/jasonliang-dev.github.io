# jasonliang-dev.github.io

## Dev Server

```sh
# php's built in web server, but it's slow even for dev
php -S localhost:8080

# better web server using symfony cli
# https://symfony.com/doc/current/setup/symfony_server.html
symfony server:start
```

## Site Generation

```sh
# create a `dist` directory for deployment
php index.php
```
