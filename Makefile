# Use -f to specify the path to the docker-compose file
DOCKER_COMPOSE = docker-compose -f docker/docker-compose.yaml
PHP_BIN = bin
RECTOR = vendor/rector/rector/bin/rector
PHPUNIT = $(PHP_BIN)/phpunit
CONSOLE = $(PHP_BIN)/console

.DEFAULT_GOAL := help

.PHONY: help install build up down restart shell logs clean
.PHONY: composer-install composer-update composer-validate
.PHONY: console demo
.PHONY: rector-check rector-fix rector-local-check rector-local-fix
.PHONY: test test-unit test-integration test-functional test-coverage test-local test-no-network
.PHONY: ci quality-check

## —— Help ————————————————————————————————————————————————————————————————————
help: ## Show this help message
	@echo "Usage: make [target]"
	@echo ""
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' \
		| sed -e 's/\[32m##/[33m/'

## —— Docker ——————————————————————————————————————————————————————————————————
build: ## Build or rebuild Docker images
	$(DOCKER_COMPOSE) build --no-cache

up: ## Start Docker services in background
	$(DOCKER_COMPOSE) up -d

down: ## Stop and remove Docker services
	$(DOCKER_COMPOSE) down

restart: down up ## Restart Docker services

shell: ## Access PHP container shell
	$(DOCKER_COMPOSE) exec php-atic-gy bash

logs: ## Show Docker container logs
	$(DOCKER_COMPOSE) logs -f php-atic-gy

## —— Composer ————————————————————————————————————————————————————————————————
install: composer-install ## Alias for composer-install

composer-install: ## Install Composer dependencies (in Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy composer install

composer-update: ## Update Composer dependencies (in Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy composer update

composer-validate: ## Validate composer.json and composer.lock
	$(DOCKER_COMPOSE) exec php-atic-gy composer validate --strict

## —— Symfony Console —————————————————————————————————————————————————————————
console: ## Run Symfony console command (e.g., make console list)
	$(DOCKER_COMPOSE) exec php-atic-gy $(CONSOLE) $(filter-out $@,$(MAKECMDGOALS))

demo: ## Run the demo translator command
	$(DOCKER_COMPOSE) exec php-atic-gy $(CONSOLE) atico:demo:translator --sheet-name=common --book-name=frontend

## —— Rector —————————————————————————————————————————————————————————————————
rector-check: ## Run Rector in dry-run mode (Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy $(RECTOR) process --dry-run

rector-fix: ## Apply Rector changes (Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy $(RECTOR) process

rector-local-check: ## Run Rector in dry-run mode (local)
	$(RECTOR) process --dry-run

rector-local-fix: ## Apply Rector changes (local)
	$(RECTOR) process

## —— Tests ——————————————————————————————————————————————————————————————————
test: ## Run all tests (Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy $(PHPUNIT) --colors=always

test-unit: ## Run unit tests only (Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy $(PHPUNIT) --testsuite "Unit Tests" --colors=always

test-integration: ## Run integration tests only (Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy $(PHPUNIT) --testsuite "Integration Tests" --colors=always

test-functional: ## Run functional tests only (Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy $(PHPUNIT) --testsuite "Functional Tests" --colors=always

test-coverage: ## Generate code coverage report (Docker)
	$(DOCKER_COMPOSE) exec php-atic-gy $(PHPUNIT) --coverage-html coverage --colors=always
	@echo "Coverage report generated in coverage/index.html"

test-local: ## Run all tests locally (without Docker)
	$(PHPUNIT) --colors=always

test-no-network: ## Run tests excluding network-dependent tests (local)
	$(PHPUNIT) --exclude-group network --colors=always

## —— Quality & CI ———————————————————————————————————————————————————————————
quality-check: rector-local-check test-no-network ## Run quality checks (Rector + Tests without network)

ci: composer-validate quality-check ## Run all CI checks (validate, rector, tests)

## —— Cleanup ————————————————————————————————————————————————————————————————
clean: ## Clean up generated files and caches
	rm -rf var/cache/* var/log/* coverage/ translations/demo_*.yml
	find . -type d -name ".phpunit.cache" -exec rm -rf {} +
	@echo "Cleanup completed"

## —— Installation & Setup ———————————————————————————————————————————————————
setup: build up composer-install ## Complete setup: build, start, and install dependencies
	@echo "Setup completed! Run 'make demo' to test the application"

# This is needed to pass arguments to the console command
%:
	@: