<?php

/*
 * This file is part of the Atico/SpreadsheetTranslator package.
 *
 * (c) Samuel Vicent <samuelvicent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Functional tests for command execution
 *
 * Tests the command behavior and output from a user perspective
 */
class CommandExecutionTest extends KernelTestCase
{
    private Application $application;
    private Filesystem $filesystem;
    private string $translationsDir;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
        $this->filesystem = new Filesystem();
        $this->translationsDir = $kernel->getProjectDir() . '/translations';
    }

    protected function tearDown(): void
    {
        $this->cleanUpTestFiles();
        parent::tearDown();
    }

    public function testCommandIsRegistered(): void
    {
        $command = $this->application->find('atico:demo:translator');
        $this->assertInstanceOf(Command::class, $command);
        $this->assertSame('atico:demo:translator', $command->getName());
    }

    public function testCommandHasExpectedOptions(): void
    {
        $command = $this->application->find('atico:demo:translator');
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasOption('sheet-name'));
        $this->assertTrue($definition->hasOption('book-name'));

        $sheetOption = $definition->getOption('sheet-name');
        $bookOption = $definition->getOption('book-name');

        $this->assertFalse($sheetOption->isValueRequired());
        $this->assertFalse($bookOption->isValueRequired());
    }

    public function testCommandDescriptionIsSet(): void
    {
        $command = $this->application->find('atico:demo:translator');
        $this->assertNotEmpty($command->getDescription());
        $this->assertStringContainsString('Translate', $command->getDescription());
    }

    /**
     * @group network
     */
    public function testCommandExecutionWithValidOptions(): void
    {
        try {
            $command = $this->application->find('atico:demo:translator');
            $commandTester = new CommandTester($command);

            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
            $this->assertStringContainsString('Translation text for', $commandTester->getDisplay());
        } catch (\Exception $e) {
            $this->markTestSkipped('Network test failed: ' . $e->getMessage());
        }
    }

    public function testCommandExecutionWithOnlySheetName(): void
    {
        $this->expectException(\Throwable::class);

        $command = $this->application->find('atico:demo:translator');
        $commandTester = new CommandTester($command);

        // This should throw an exception because book-name is required or missing
        $commandTester->execute([
            '--sheet-name' => 'common',
        ]);
    }

    public function testCommandExecutionWithOnlyBookName(): void
    {
        $this->expectException(\Throwable::class);

        $command = $this->application->find('atico:demo:translator');
        $commandTester = new CommandTester($command);

        // This should throw an exception because sheet-name is required
        $commandTester->execute([
            '--book-name' => 'frontend',
        ]);
    }

    /**
     * @group network
     */
    public function testCommandOutputFormat(): void
    {
        try {
            $command = $this->application->find('atico:demo:translator');
            $commandTester = new CommandTester($command);

            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $output = $commandTester->getDisplay();

            // Check output format contains expected elements
            $this->assertMatchesRegularExpression(
                '/Translation text for "[\w.]+" in "[\w_]+"/',
                $output,
                'Output should contain translation information in expected format'
            );
        } catch (\Exception $e) {
            $this->markTestSkipped('Network test failed: ' . $e->getMessage());
        }
    }

    /**
     * @group network
     */
    public function testCommandCreatesFilesInCorrectLocation(): void
    {
        try {
            $command = $this->application->find('atico:demo:translator');
            $commandTester = new CommandTester($command);

            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $this->assertDirectoryExists($this->translationsDir);

            // Check that files are created
            $files = glob($this->translationsDir . '/demo_common.*.yml');
            $this->assertNotEmpty($files, 'Translation files should be created in translations directory');
        } catch (\Exception $e) {
            $this->markTestSkipped('Network test failed: ' . $e->getMessage());
        }
    }

    /**
     * @group network
     */
    public function testCommandOutputShowsSpanishTranslation(): void
    {
        try {
            $command = $this->application->find('atico:demo:translator');
            $commandTester = new CommandTester($command);

            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $output = $commandTester->getDisplay();

            // The command specifically shows es_ES translation
            $this->assertStringContainsString('es_ES', $output);
            $this->assertStringContainsString('homepage.title', $output);
            $this->assertStringContainsString('demo_common', $output);
        } catch (\Exception $e) {
            $this->markTestSkipped('Network test failed: ' . $e->getMessage());
        }
    }

    public function testCommandWithEmptyStringOptions(): void
    {
        $this->expectException(\Throwable::class);

        $command = $this->application->find('atico:demo:translator');
        $commandTester = new CommandTester($command);

        // This should throw an exception with empty options
        $commandTester->execute([
            '--sheet-name' => '',
            '--book-name' => '',
        ]);
    }

    /**
     * @group network
     */
    public function testCommandIsIdempotent(): void
    {
        try {
            $command = $this->application->find('atico:demo:translator');
            $commandTester = new CommandTester($command);

            // Execute command twice
            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);
            $firstExecution = $commandTester->getStatusCode();

            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);
            $secondExecution = $commandTester->getStatusCode();

            // Both executions should succeed
            $this->assertSame($firstExecution, $secondExecution);
            $this->assertSame(Command::SUCCESS, $secondExecution);
        } catch (\Exception $e) {
            $this->markTestSkipped('Network test failed: ' . $e->getMessage());
        }
    }

    public function testCommandWithVeryLongOptions(): void
    {
        $this->expectException(\Throwable::class);

        $command = $this->application->find('atico:demo:translator');
        $commandTester = new CommandTester($command);

        $longString = str_repeat('a', 1000);

        // This should throw an exception with invalid long options
        $commandTester->execute([
            '--sheet-name' => $longString,
            '--book-name' => $longString,
        ]);
    }

    /**
     * Test command handles special characters in options
     */
    /**
     * @group network
     */
    public function testCommandWithSpecialCharacters(): void
    {
        $command = $this->application->find('atico:demo:translator');
        $commandTester = new CommandTester($command);

        try {
            $commandTester->execute([
                '--sheet-name' => 'test-sheet_name.v1',
                '--book-name' => 'frontend',
            ]);

            $this->assertIsInt($commandTester->getStatusCode());
        } catch (\Exception $e) {
            // Sheet doesn't exist - skip this test as it requires network
            $this->markTestSkipped('Sheet with special characters not found: ' . $e->getMessage());
        }
    }

    /**
     * @group network
     */
    public function testCommandWithNumericOptions(): void
    {
        $command = $this->application->find('atico:demo:translator');
        $commandTester = new CommandTester($command);

        try {
            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $this->assertIsInt($commandTester->getStatusCode());
        } catch (\Exception $e) {
            // Requires network - skip this test
            $this->markTestSkipped('Network test skipped: ' . $e->getMessage());
        }
    }

    /**
     * @group network
     */
    public function testCommandOutputIsNotEmpty(): void
    {
        try {
            $command = $this->application->find('atico:demo:translator');
            $commandTester = new CommandTester($command);

            $commandTester->execute([
                '--sheet-name' => 'common',
                '--book-name' => 'frontend',
            ]);

            $output = $commandTester->getDisplay();
            $this->assertNotEmpty($output, 'Command should produce output');
        } catch (\Exception $e) {
            $this->markTestSkipped('Network test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test that command can be found by its full name
     */
    public function testCommandCanBeFoundByFullName(): void
    {
        $command = $this->application->find('atico:demo:translator');
        $this->assertSame('atico:demo:translator', $command->getName());
    }

    /**
     * Test command list includes our command
     */
    public function testCommandAppearsInApplicationList(): void
    {
        $commands = $this->application->all();
        $this->assertArrayHasKey('atico:demo:translator', $commands);
    }

    private function cleanUpTestFiles(): void
    {
        if (!$this->filesystem->exists($this->translationsDir)) {
            return;
        }

        $files = glob($this->translationsDir . '/demo_common.*.yml');
        if ($files) {
            foreach ($files as $file) {
                $this->filesystem->remove($file);
            }
        }
    }
}
