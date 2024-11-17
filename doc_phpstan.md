# PHP Stan
## Install:
```bash
docker-compose exec app composer require --dev phpstan/phpstan
```
## Useage
```bash
docker-compose exec app vendor/bin/phpstan analyse app tests
```