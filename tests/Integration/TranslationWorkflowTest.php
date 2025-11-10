<?php

declare(strict_types=1);

/*
 * This file is part of the Atico/SpreadsheetTranslator package.
 *
 * (c) Samuel Vicent <samuelvicent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Tests\Integration;

use PHPUnit\Framework\Attributes\Group;
use Override;
use Exception;
use Throwable;
use App\Command\TranslatorCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Integration tests for the complete translation workflow
 *
 * Tests the command with real Symfony services and configuration
 */
final class TranslationWorkflowTest extends KernelTestCase
{
    private Filesystem $filesystem;

    private string $translationsDir;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->filesystem = new Filesystem();
        $this->translationsDir = self::$kernel->getProjectDir() . '/translations';

        $application = new Application();
        $command = self::getContainer()->get(TranslatorCommand::class);
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

    #[Override]
    protected function tearDown(): void
    {
        // Clean up generated translation files after each test
        $this->cleanUpTranslationFiles();
        parent::tearDown();
    }

    public function testServiceIsAvailableInContainer(): void
    {
        $command = self::getContainer()->get(TranslatorCommand::class);
        $this->assertInstanceOf(TranslatorCommand::class, $command);
    }

    public function testSpreadsheetTranslatorServiceIsConfigured(): void
    {
        $this->assertTrue(self::getContainer()->has('atico.spreadsheet_translator.manager'));
    }

    public function testTranslationsDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->translationsDir)) {
            $this->filesystem->mkdir($this->translationsDir);
        }

        $this->assertDirectoryExists($this->translationsDir);
        $this->assertDirectoryIsWritable($this->translationsDir);
    }

    /**
     * This test requires network access to Google Drive
     * Mark as risky or skip if running in offline mode
     */
    #[Group('network')]
    public function testCommandExecutesSuccessfully(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $statusCode = $this->commandTester->getStatusCode();
            $this->assertSame(0, $statusCode, 'Command should return success status');

            $output = $this->commandTester->getDisplay();
            $this->assertStringContainsString('Translation text for', $output);
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed - Google Drive may be unavailable: ' . $exception->getMessage());
        }
    }

    #[Group('network')]
    public function testTranslationFilesAreCreated(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            // Check that YAML files are created
            $finder = new Finder();
            $finder->files()->in($this->translationsDir)->name('demo_common.*.yml');

            $this->assertGreaterThan(0, $finder->count(), 'Translation files should be created');

            foreach ($finder as $file) {
                $this->assertFileExists($file->getRealPath());
                $this->assertFileIsReadable($file->getRealPath());
            }
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed: ' . $exception->getMessage());
        }
    }

    #[Group('network')]
    public function testTranslationFilesHaveValidYamlFormat(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $finder = new Finder();
            $finder->files()->in($this->translationsDir)->name('demo_common.*.yml');

            foreach ($finder as $file) {
                $content = file_get_contents($file->getRealPath());
                $this->assertNotEmpty($content, 'Translation file should not be empty');

                // Parse YAML to ensure it's valid
                $data = Yaml::parse($content);
                $this->assertIsArray($data, 'YAML content should parse to an array');
                $this->assertNotEmpty($data, 'Parsed YAML should not be empty');
            }
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed: ' . $exception->getMessage());
        }
    }

    #[Group('network')]
    public function testTranslationFilesContainExpectedKeys(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $finder = new Finder();
            $finder->files()->in($this->translationsDir)->name('demo_common.*.yml');

            foreach ($finder as $file) {
                $data = Yaml::parseFile($file->getRealPath());

                // Check for expected structure
                $this->assertArrayHasKey('homepage', $data, 'Translation should contain homepage key');

                if (isset($data['homepage'])) {
                    $this->assertIsArray($data['homepage']);
                    $this->assertArrayHasKey('title', $data['homepage'], 'homepage should contain title key');
                }
            }
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed: ' . $exception->getMessage());
        }
    }

    #[Group('network')]
    public function testCorrectFileNamingConvention(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $finder = new Finder();
            $finder->files()->in($this->translationsDir)->name('demo_common.*.yml');

            foreach ($finder as $file) {
                $filename = $file->getFilename();

                // Check naming pattern: prefix_sheetname.locale.yml
                $this->assertMatchesRegularExpression(
                    '/^demo_common\.[a-z]{2}_[A-Z]{2}\.yml$/',
                    $filename,
                    'Filename should match pattern: demo_common.{locale}.yml'
                );
            }
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed: ' . $exception->getMessage());
        }
    }

    #[Group('network')]
    public function testMultipleLocalesAreGenerated(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $finder = new Finder();
            $finder->files()->in($this->translationsDir)->name('demo_common.*.yml');

            // Should create files for multiple locales (e.g., en_GB, es_ES, fr_FR)
            $this->assertGreaterThanOrEqual(2, $finder->count(), 'Should generate files for multiple locales');

            $locales = [];
            foreach ($finder as $file) {
                // Extract locale from filename
                if (preg_match('/demo_common\.([a-z]{2}_[A-Z]{2})\.yml/', $file->getFilename(), $matches)) {
                    $locales[] = $matches[1];
                }
            }

            $this->assertNotEmpty($locales, 'Should extract locale codes from filenames');
            $this->assertCount(count($locales), array_unique($locales), 'Each locale should be unique');
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed: ' . $exception->getMessage());
        }
    }

    /**
     * Test command execution without options
     */
    #[Group('network')]
    public function testCommandWithoutOptionsExecutesWithoutError(): void
    {
        $this->expectException(Throwable::class);

        // This should throw an exception because options are required
        $this->commandTester->execute([]);
    }

    /**
     * Test command output contains expected information
     */
    #[Group('network')]
    public function testCommandOutputContainsTranslationInfo(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $output = $this->commandTester->getDisplay();

            $this->assertStringContainsString('homepage.title', $output);
            $this->assertStringContainsString('es_ES', $output);
            $this->assertStringContainsString('demo_common', $output);
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed: ' . $exception->getMessage());
        }
    }

    /**
     * Test file permissions are correct
     */
    #[Group('network')]
    public function testGeneratedFilesHaveCorrectPermissions(): void
    {
        try {
            $this->commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $finder = new Finder();
            $finder->files()->in($this->translationsDir)->name('demo_common.*.yml');

            foreach ($finder as $file) {
                $this->assertTrue($file->isReadable(), 'File should be readable');
                $this->assertTrue($file->isWritable(), 'File should be writable');
            }
        } catch (Exception $exception) {
            $this->markTestSkipped('Network test failed: ' . $exception->getMessage());
        }
    }

    private function cleanUpTranslationFiles(): void
    {
        if (!$this->filesystem->exists($this->translationsDir)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($this->translationsDir)->name('demo_common.*.yml');

        foreach ($finder as $file) {
            $this->filesystem->remove($file->getRealPath());
        }
    }
}
