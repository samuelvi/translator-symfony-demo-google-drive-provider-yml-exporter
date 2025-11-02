# Use -f to specify the path to the docker-compose file
DOCKER_COMPOSE = docker-compose -f docker/docker-compose.yaml

.PHONY: help build up down restart shell composer-install console rector-check rector-fix test test-unit test-integration test-functional test-coverage test-local

help:
	@echo "Available commands:"
	@echo "  make build             Build or rebuild the Docker images"
	@echo "  make up                Start the services in the background"
	@echo "  make down              Stop and remove the services"
	@echo "  make restart           Restart the services"
	@echo "  make shell             Access the PHP container shell"
	@echo "  make composer-install  Run composer install inside the container"
	@echo "  make console           Run a Symfony console command (e.g., make console list)"
	@echo "  make rector-check      Run Rector in dry-run mode to check for improvements"
	@echo "  make rector-fix        Apply Rector changes to the codebase"
	@echo "  make test              Run all tests"
	@echo "  make test-unit         Run unit tests only"
	@echo "  make test-integration  Run integration tests only"
	@echo "  make test-functional   Run functional tests only"
	@echo "  make test-coverage     Run tests with code coverage report"
	@echo "  make test-local        Run tests locally (without Docker)"

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

rector-check:
	$(DOCKER_COMPOSE) exec php-atic-gy vendor/rector/rector/bin/rector process --dry-run

rector-fix:
	$(DOCKER_COMPOSE) exec php-atic-gy vendor/rector/rector/bin/rector process

# Test commands
test:
	$(DOCKER_COMPOSE) exec php-atic-gy bin/phpunit

test-unit:
	$(DOCKER_COMPOSE) exec php-atic-gy bin/phpunit --testsuite "Unit Tests"

test-integration:
	$(DOCKER_COMPOSE) exec php-atic-gy bin/phpunit --testsuite "Integration Tests"

test-functional:
	$(DOCKER_COMPOSE) exec php-atic-gy bin/phpunit --testsuite "Functional Tests"

test-coverage:
	$(DOCKER_COMPOSE) exec php-atic-gy bin/phpunit --coverage-html coverage

test-local:
	bin/phpunit

# This is needed to pass arguments to the console command
%:
	@: