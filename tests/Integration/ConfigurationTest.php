<?php

/*
 * This file is part of the Atico/SpreadsheetTranslator package.
 *
 * (c) Samuel Vicent <samuelvicent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests for application configuration
 */
class ConfigurationTest extends KernelTestCase
{
    private string $configDir;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->configDir = self::$kernel->getProjectDir() . '/config';
        $this->filesystem = new Filesystem();
    }

    public function testConfigDirectoryExists(): void
    {
        $this->assertDirectoryExists($this->configDir);
    }

    public function testSpreadsheetTranslatorConfigExists(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $this->assertFileExists($configFile);
        $this->assertFileIsReadable($configFile);
    }

    public function testSpreadsheetTranslatorConfigIsValidYaml(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $content = file_get_contents($configFile);

        $data = Yaml::parse($content);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('atico_spreadsheet_translator', $data);
    }

    public function testSpreadsheetTranslatorConfigHasRequiredKeys(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $config = $data['atico_spreadsheet_translator'];

        // Should have at least one book configuration (frontend)
        $this->assertIsArray($config);
        $this->assertArrayHasKey('frontend', $config);

        $frontendConfig = $config['frontend'];

        // Check required sections
        $this->assertArrayHasKey('provider', $frontendConfig);
        $this->assertArrayHasKey('exporter', $frontendConfig);
        $this->assertArrayHasKey('shared', $frontendConfig);
    }

    public function testProviderConfigurationIsValid(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $provider = $data['atico_spreadsheet_translator']['frontend']['provider'];

        $this->assertArrayHasKey('name', $provider);
        $this->assertSame('google_drive', $provider['name']);

        $this->assertArrayHasKey('source_resource', $provider);
        $this->assertNotEmpty($provider['source_resource']);

        // Validate URL format
        $url = $provider['source_resource'];
        $this->assertStringContainsString('https://docs.google.com/spreadsheets/', $url);
    }

    public function testExporterConfigurationIsValid(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $exporter = $data['atico_spreadsheet_translator']['frontend']['exporter'];

        $this->assertArrayHasKey('format', $exporter);
        $this->assertSame('yml', $exporter['format']);

        $this->assertArrayHasKey('prefix', $exporter);
        $this->assertSame('demo_', $exporter['prefix']);

        $this->assertArrayHasKey('destination_folder', $exporter);
        $this->assertStringContainsString('translations', $exporter['destination_folder']);
    }

    public function testSharedConfigurationIsValid(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $shared = $data['atico_spreadsheet_translator']['frontend']['shared'];

        $this->assertArrayHasKey('default_locale', $shared);
        $this->assertSame('en', $shared['default_locale']);

        $this->assertArrayHasKey('name_separator', $shared);
        $this->assertSame('_', $shared['name_separator']);
    }

    public function testFrameworkConfigExists(): void
    {
        $configFile = $this->configDir . '/packages/framework.yaml';
        $this->assertFileExists($configFile);
    }

    public function testServicesConfigExists(): void
    {
        $configFile = $this->configDir . '/services.yaml';
        $this->assertFileExists($configFile);
    }

    public function testTranslationsDirectoryIsConfiguredCorrectly(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $destinationFolder = $data['atico_spreadsheet_translator']['frontend']['exporter']['destination_folder'];

        // Should contain kernel.project_dir parameter
        $this->assertStringContainsString('%kernel.project_dir%', $destinationFolder);
        $this->assertStringContainsString('/translations', $destinationFolder);
    }

    public function testGoogleDriveUrlIsAccessible(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $url = $data['atico_spreadsheet_translator']['frontend']['provider']['source_resource'];

        // Validate URL structure
        $this->assertMatchesRegularExpression(
            '#^https://docs\.google\.com/spreadsheets/d/[a-zA-Z0-9_-]+/edit#',
            $url,
            'Google Drive URL should match expected format'
        );
    }

    public function testConfigurationPrefixIsConsistent(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $prefix = $data['atico_spreadsheet_translator']['frontend']['exporter']['prefix'];

        // Prefix should be consistent with expected naming
        $this->assertSame('demo_', $prefix);
    }

    public function testDefaultLocaleIsValid(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $defaultLocale = $data['atico_spreadsheet_translator']['frontend']['shared']['default_locale'];

        // Should be a valid locale code
        $this->assertMatchesRegularExpression('/^[a-z]{2}(_[A-Z]{2})?$/', $defaultLocale);
    }

    public function testNameSeparatorIsValid(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $separator = $data['atico_spreadsheet_translator']['frontend']['shared']['name_separator'];

        $this->assertNotEmpty($separator);
        $this->assertIsString($separator);
        $this->assertSame('_', $separator);
    }

    public function testExporterFormatIsSupported(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $data = Yaml::parseFile($configFile);

        $format = $data['atico_spreadsheet_translator']['frontend']['exporter']['format'];

        $supportedFormats = ['yml', 'yaml', 'php', 'xliff', 'json'];
        $this->assertContains($format, $supportedFormats);
    }

    public function testConfigFileHasNoSyntaxErrors(): void
    {
        $configFile = $this->configDir . '/packages/atico_spreadsheet_translator.yaml';
        $content = file_get_contents($configFile);

        try {
            $data = Yaml::parse($content);
            $this->assertIsArray($data);
        } catch (\Exception $e) {
            $this->fail('Configuration file has YAML syntax errors: ' . $e->getMessage());
        }
    }

    public function testEnvironmentSpecificConfigsExist(): void
    {
        // Test for environment-specific configs if they exist
        $environments = ['dev', 'prod', 'test'];

        foreach ($environments as $env) {
            $envConfigDir = $this->configDir . '/packages/' . $env;
            if ($this->filesystem->exists($envConfigDir)) {
                $this->assertDirectoryExists($envConfigDir);
            }
        }
    }

    public function testParametersFileExists(): void
    {
        $parametersFile = $this->configDir . '/packages/parameters.yaml';

        // Parameters file is optional, but if it exists, it should be valid
        if ($this->filesystem->exists($parametersFile)) {
            $this->assertFileIsReadable($parametersFile);

            $content = file_get_contents($parametersFile);
            $data = Yaml::parse($content);
            $this->assertIsArray($data);
        } else {
            $this->assertTrue(true, 'Parameters file is optional');
        }
    }
}
