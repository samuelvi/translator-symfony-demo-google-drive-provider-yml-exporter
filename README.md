Spreadsheet Translator Symfony Demo Application - Use Case
======================================================================================

Introduction
------------

Lightweight Symfony Demo Application for the Spreadsheet Translator functionality.
This demo brings a command that takes a **Google Drive spreadsheet** and creates translation files per locale in **YAML format**.

Installation
------------

```bash
composer create-project samuelvi/translator-symfony-demo-googledrive-to-yml
```

This will install the demo application into your computer.

Configuration
-------------

Before running the demo, configure your Google Drive spreadsheet URL in `config/packages/atico_spreadsheet_translator.yaml`:

```yaml
atico_spreadsheet_translator:
    frontend:
        provider:
            name: 'google_drive'
            source_resource: 'https://docs.google.com/spreadsheets/d/YOUR_SPREADSHEET_ID/edit#gid=0'
        exporter:
            format: 'yml'
            prefix: 'demo_'
            destination_folder: '%kernel.project_dir%/translations'
        shared:
            default_locale: 'en'
            name_separator: '_'
```

**Important:** Make sure your Google Spreadsheet is publicly accessible for the provider to download it.

Running the demo
---------

Type in your terminal:

```bash
bin/console atico:demo:translator --sheet-name=common --book-name=frontend
```

This command will generate the translation files that will be stored into the `translations/` folder.

The generated files will be:

```
translations/
├── demo_common.en_GB.yml
├── demo_common.es_ES.yml
└── demo_common.fr_FR.yml
```

demo_common.en_GB.yml will contain:

```yaml
homepage:
    title: >
        Secured Spreadsheet translator
    subtitle: >
        Translator of web pages from secured spreadsheet
```

Notes
-----

composer.json includes the following Spreadsheet Translator dependencies:
```json
  "samuelvi/spreadsheet-translator-core": "^8.4",
  "samuelvi/spreadsheet-translator-symfony-bundle": "^8.0",
  "samuelvi/spreadsheet-translator-provider-googledrive": "^8.0",
  "samuelvi/spreadsheet-translator-reader-xlsx": "^8.1",
  "samuelvi/spreadsheet-translator-exporter-yml": "^8.0"
```

Related
------------

Symfony Bundle:
- <a href="https://github.com/samuelvi/spreadsheet-translator-symfony-bundle">Symfony Bundle</a>

Symfony Demos:

- <a href="https://github.com/samuelvi/translator-symfony-demo-local-file-to-php">Symfony Demo. Takes a local file and creates translation files per locale in php format</a>
- <a href="https://github.com/samuelvi/translator-symfony-demo-google-drive-provider-yml-exporter">Symfony Demo. Takes a google drive spreadsheet and creates translation files per locale in yml format</a>
- <a href="https://github.com/samuelvi/translator-symfony-demo-onedrive-to-xliff">Symfony Demo. Takes a microsoft one drive spreadsheet and creates translation files per locale in xliff format</a>


Requirements
------------

  * PHP >=8.4
  * Symfony >=7.0


Development
-----------

### Quick Start

Get started quickly with the complete setup:

```bash
# Complete setup: build, start Docker, and install dependencies
make setup

# Run the demo
make demo
```

### Available Commands

Run `make` or `make help` to see all available commands with descriptions.

### Testing

This project includes a comprehensive test suite with unit, integration, and functional tests.

**Run all tests:**

```bash
# Run all tests in Docker
make test

# Run all tests locally (without Docker)
make test-local

# Run tests excluding network-dependent tests (for offline development)
make test-no-network
```

**Run specific test suites:**

```bash
# Unit tests only
make test-unit

# Integration tests only
make test-integration

# Functional tests only
make test-functional

# Generate coverage report
make test-coverage
```

**Run tests with PHPUnit directly:**

```bash
bin/phpunit

# Run specific test suite
bin/phpunit --testsuite "Unit Tests"

# Exclude network tests (for offline development)
bin/phpunit --exclude-group network

# Run specific test file
bin/phpunit tests/Unit/Command/TranslatorCommandTest.php

# Run specific test method
bin/phpunit --filter testExecuteWithBothOptions tests/Unit/Command/TranslatorCommandTest.php
```

**Test Structure:**
- `tests/Unit/` - Unit tests (isolated component testing with mocks)
- `tests/Integration/` - Integration tests (testing with real services)
- `tests/Functional/` - Functional tests (end-to-end user scenarios)
- `tests/Fixtures/` - Test fixtures and sample data

For detailed testing documentation, see [tests/README.md](tests/README.md).

### Code Quality with Rector

This project uses [Rector](https://github.com/rectorphp/rector) to maintain PHP 8.4 compliance and code quality standards.

**Run Rector to check for potential improvements:**

```bash
# In Docker
make rector-check

# Locally
make rector-local-check
# or
bin/rector process --dry-run
```

**Apply Rector changes:**

```bash
# In Docker
make rector-fix

# Locally
make rector-local-fix
# or
bin/rector process
```

The rector configuration (`rector.php`) includes:
- PHP 8.4 compliance rules with modern fluent syntax
- Code quality improvements
- Dead code removal and privatization
- Type declaration enhancements
- Early return patterns and strict booleans
- Symfony 7.0 best practices
- Doctrine code quality rules
- PHPUnit 11.0 rules
- Annotations to attributes conversion
- Parallel processing for faster execution

### Quality Checks

Run comprehensive quality checks before committing:

```bash
# Run all quality checks (Rector + Tests without network)
make quality-check

# Run all CI checks (includes composer validation)
make ci
```

### Docker Commands

```bash
# Build Docker images
make build

# Start services
make up

# Stop services
make down

# Restart services
make restart

# Access PHP container shell
make shell

# View container logs
make logs
```

### Composer Commands

```bash
# Install dependencies
make install

# Update dependencies
make composer-update

# Validate composer.json and composer.lock
make composer-validate
```

### Cleanup

```bash
# Clean generated files, caches, and test artifacts
make clean
```

Contributing
------------

We welcome contributions to this project, including pull requests and issues (and discussions on existing issues).

If you'd like to contribute code but aren't sure what, the issues list is a good place to start. If you're a first-time code contributor, you may find Github's guide to <a href="https://guides.github.com/activities/forking/">forking projects</a> helpful.

All contributors (whether contributing code, involved in issue discussions, or involved in any other way) must abide by our code of conduct.


License
-------

Spreadsheet Translator Symfony Bundle is licensed under the MIT License. See the LICENSE file for full details.

