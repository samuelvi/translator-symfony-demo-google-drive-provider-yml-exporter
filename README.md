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

### Testing

This project includes a comprehensive test suite with unit, integration, and functional tests.

**Run all tests:**

```bash
make test
# or locally without Docker
make test-local
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
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite "Unit Tests"

# Exclude network tests (for offline development)
vendor/bin/phpunit --exclude-group network

# Run specific test file
vendor/bin/phpunit tests/Unit/Command/TranslatorCommandTest.php
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
make rector-check
# or
vendor/rector/rector/bin/rector process --dry-run
```

**Apply Rector changes:**

```bash
make rector-fix
# or
vendor/rector/rector/bin/rector process
```

The rector configuration (`rector.php`) includes:
- PHP 8.4 compliance rules (`LevelSetList::UP_TO_PHP_84`)
- Code quality improvements
- Dead code removal
- Type declaration enhancements
- Symfony 7.0 best practices
- Doctrine code quality rules
- Annotations to attributes conversion

**Example output:**
```
1 file has been changed by Rector
```

Contributing
------------

We welcome contributions to this project, including pull requests and issues (and discussions on existing issues).

If you'd like to contribute code but aren't sure what, the issues list is a good place to start. If you're a first-time code contributor, you may find Github's guide to <a href="https://guides.github.com/activities/forking/">forking projects</a> helpful.

All contributors (whether contributing code, involved in issue discussions, or involved in any other way) must abide by our code of conduct.


License
-------

Spreadsheet Translator Symfony Bundle is licensed under the MIT License. See the LICENSE file for full details.

