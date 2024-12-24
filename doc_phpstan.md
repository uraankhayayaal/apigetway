# PHP Stan
## Install:
```bash
docker-compose exec app composer require --dev phpstan/phpstan
```
## Usage
```bash
docker-compose exec app vendor/bin/phpstan analyse app tests
docker-compose exec app vendor/bin/phpstan analyse app tests --debug -vvv
```