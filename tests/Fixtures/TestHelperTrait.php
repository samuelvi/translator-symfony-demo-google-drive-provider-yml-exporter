<?php

/*
 * This file is part of the Atico/SpreadsheetTranslator package.
 *
 * (c) Samuel Vicent <samuelvicent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Fixtures;

use Symfony\Component\Yaml\Yaml;
use Exception;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Helper trait for test classes
 *
 * Provides common utility methods for testing
 */
trait TestHelperTrait
{
    private ?Filesystem $testFilesystem = null;

    /**
     * Get filesystem instance for tests
     */
    protected function getTestFilesystem(): Filesystem
    {
        if (null === $this->testFilesystem) {
            $this->testFilesystem = new Filesystem();
        }

        return $this->testFilesystem;
    }

    /**
     * Clean up translation files created during tests
     */
    protected function cleanUpTranslationFiles(string $translationsDir, string $pattern = 'demo_common.*.yml'): void
    {
        $filesystem = $this->getTestFilesystem();

        if (!$filesystem->exists($translationsDir)) {
            return;
        }

        $files = glob($translationsDir . '/' . $pattern);
        if ($files) {
            foreach ($files as $file) {
                $filesystem->remove($file);
            }
        }
    }

    /**
     * Create a temporary test directory
     */
    protected function createTestDirectory(string $path): void
    {
        $filesystem = $this->getTestFilesystem();

        if (!$filesystem->exists($path)) {
            $filesystem->mkdir($path);
        }
    }

    /**
     * Remove a test directory and its contents
     */
    protected function removeTestDirectory(string $path): void
    {
        $filesystem = $this->getTestFilesystem();

        if ($filesystem->exists($path)) {
            $filesystem->remove($path);
        }
    }

    /**
     * Assert that a file exists and contains expected content
     */
    protected function assertFileContainsString(string $expected, string $filePath, string $message = ''): void
    {
        $this->assertFileExists($filePath, $message);
        $content = file_get_contents($filePath);
        $this->assertStringContainsString($expected, $content, $message);
    }

    /**
     * Assert that a file exists and is valid YAML
     */
    protected function assertFileIsValidYaml(string $filePath, string $message = ''): void
    {
        $this->assertFileExists($filePath, $message);

        $content = file_get_contents($filePath);
        $this->assertNotEmpty($content, 'File should not be empty');

        try {
            $data = Yaml::parse($content);
            $this->assertIsArray($data, 'YAML should parse to an array');
        } catch (Exception $e) {
            $this->fail('File is not valid YAML: ' . $e->getMessage());
        }
    }

    /**
     * Get fixture file path
     */
    protected function getFixturePath(string $filename): string
    {
        return __DIR__ . '/' . $filename;
    }

    /**
     * Create a mock translation file for testing
     */
    protected function createMockTranslationFile(string $path, array $content = []): void
    {
        $filesystem = $this->getTestFilesystem();

        $defaultContent = [
            'homepage' => [
                'title' => 'Test Title',
                'subtitle' => 'Test Subtitle',
            ],
        ];

        $data = $content === [] ? $defaultContent : $content;
        $yamlContent = Yaml::dump($data);

        $filesystem->dumpFile($path, $yamlContent);
    }

    /**
     * Assert that a directory is empty
     */
    protected function assertDirectoryIsEmpty(string $path, string $message = ''): void
    {
        $this->assertDirectoryExists($path);

        $files = array_diff(scandir($path), ['.', '..', '.gitignore']);
        $this->assertEmpty($files, $message ?: "Directory {$path} is not empty");
    }

    /**
     * Assert that a directory contains a specific number of files
     */
    protected function assertDirectoryContainsFiles(string $path, int $expectedCount, string $pattern = '*', string $message = ''): void
    {
        $this->assertDirectoryExists($path);

        $files = glob($path . '/' . $pattern);
        $actualCount = count($files);

        $this->assertSame(
            $expectedCount,
            $actualCount,
            $message ?: "Expected {$expectedCount} files, found {$actualCount}"
        );
    }

    /**
     * Skip test if network is not available
     */
    protected function skipIfNetworkUnavailable(): void
    {
        $connected = @fsockopen('www.google.com', 80, $errno, $errstr, 5);

        if (!$connected) {
            $this->markTestSkipped('Network is not available');
        }

        fclose($connected);
    }

    /**
     * Get test configuration
     */
    protected function getTestConfig(): array
    {
        return [
            'sheet_name' => 'common',
            'book_name' => 'frontend',
            'locale' => 'es_ES',
            'domain' => 'demo_common',
            'translation_key' => 'homepage.title',
        ];
    }

    /**
     * Assert command output matches expected format
     */
    protected function assertCommandOutputFormat(string $output, string $message = ''): void
    {
        $this->assertStringContainsString('Translation text for', $output, $message);
        $this->assertMatchesRegularExpression(
            '/Translation text for "[\w.]+" in "[\w_]+": ".*"/',
            $output,
            $message ?: 'Command output does not match expected format'
        );
    }

    /**
     * Assert that a locale string is valid
     */
    protected function assertValidLocale(string $locale, string $message = ''): void
    {
        $this->assertMatchesRegularExpression(
            '/^[a-z]{2}_[A-Z]{2}$/',
            $locale,
            $message ?: "Locale '{$locale}' is not valid"
        );
    }

    /**
     * Assert that a translation key exists in YAML file
     */
    protected function assertTranslationKeyExists(string $filePath, string $key, string $message = ''): void
    {
        $this->assertFileExists($filePath);

        $data = Yaml::parseFile($filePath);
        $keys = explode('.', $key);
        $current = $data;

        foreach ($keys as $part) {
            $this->assertArrayHasKey(
                $part,
                $current,
                $message ?: "Translation key '{$key}' does not exist in {$filePath}"
            );
            $current = $current[$part];
        }
    }
}
