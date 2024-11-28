# API Getway service

## Installation
```bash
docker-compose up
```

## Services
All services stored at [services.json](services.json) file. To add new service for Api Getway insert new json object in to the services file:
```json
[
    // ...
    {
        "name": "users", // the key of service, must be unique, the field not nullable
        "host": "http://mk-web", // the internal host for requesting, the field not nullable
        "openapi": "http://mk-web/swagger.yaml" // link to openapi docs for service, the field not nullable 
    }
]
```
and update openapi docs for API Getway `docker-compose exec app php artisan openapi:generate`.

## Commands
To update openapi docs use:
```bash
docker-compose exec app php artisan openapi:generate
```
The command updates docs from all services from [services.json](services.json) file, store it at [openapi.json](openapi.json), and can be viewed by swagger ui at [http://localhost:8000/api](http://localhost:8000/api)