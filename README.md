# Meraki Wedding Planner - Dockerized

This repository contains a dockerized version of the Meraki Wedding Planner WordPress site.

## Requirements

- Docker
- Docker Compose

## Setup

1. Clone this repository:
```
git clone <repository-url>
cd meraki
```

2. Start the containers:
```
docker-compose up -d
```

3. Access the website:
```
http://localhost
```

## Services

- **WordPress**: PHP-FPM application server
- **Nginx**: Web server
- **MySQL**: Database server

## Configuration

- WordPress configuration is in `wp-config.php`
- Nginx configuration is in `nginx/default.conf`
- PHP configuration is in `uploads.ini`
- Docker configuration is in `docker-compose.yml`

## Database

The database is initialized with the SQL dump file specified in the docker-compose.yml file.

## Volumes

- MySQL data is stored in a persistent volume
- WordPress files are mounted from the host for development

## Network

All services are connected through a Docker network named `meraki_network`. 