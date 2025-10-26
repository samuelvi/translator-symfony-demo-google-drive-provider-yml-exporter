# Use -f to specify the path to the docker-compose file
DOCKER_COMPOSE = docker-compose -f docker/docker-compose.yaml

.PHONY: help build up down restart shell composer-install console

help:
	@echo "Available commands:"
	@echo "  make build             Build or rebuild the Docker images"
	@echo "  make up                Start the services in the background"
	@echo "  make down              Stop and remove the services"
	@echo "  make restart           Restart the services"
	@echo "  make shell             Access the PHP container shell"
	@echo "  make composer-install  Run composer install inside the container"
	@echo "  make console           Run a Symfony console command (e.g., make console list)"

build:
	$(DOCKER_COMPOSE) build --no-cache

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

restart: down up

shell:
	$(DOCKER_COMPOSE) exec php-atic-gy bash

composer-install:
	$(DOCKER_COMPOSE) exec php-atic-gy composer install

# Allows running any Symfony command, e.g., `make console list` or `make console samuelvi:demo:translator`
console:
	$(DOCKER_COMPOSE) exec php-atic-gy bin/console $(filter-out $@,$(MAKECMDGOALS))

# This is needed to pass arguments to the console command
%:
	@: