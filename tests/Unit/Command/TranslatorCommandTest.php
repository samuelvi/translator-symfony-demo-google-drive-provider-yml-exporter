<?php

/*
 * This file is part of the Atico/SpreadsheetTranslator package.
 *
 * (c) Samuel Vicent <samuelvicent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Command;

use App\Command\TranslatorCommand;
use Atico\SpreadsheetTranslator\Core\SpreadsheetTranslator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Unit tests for TranslatorCommand
 *
 * Tests the command in isolation by mocking all dependencies
 */
class TranslatorCommandTest extends TestCase
{
    private MockObject $spreadsheetTranslator;
    private MockObject $translator;
    private TranslatorCommand $command;

    protected function setUp(): void
    {
        $this->spreadsheetTranslator = $this->createMock(SpreadsheetTranslator::class);

        // Create a mock that supports TranslatorInterface and setFallbackLocales method
        $this->translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['trans', 'getLocale'])
            ->addMethods(['setFallbackLocales'])
            ->getMock();

        $this->command = new TranslatorCommand($this->spreadsheetTranslator, $this->translator);
    }

    public function testCommandIsConfiguredCorrectly(): void
    {
        $this->assertSame('atico:demo:translator', $this->command->getName());
        $this->assertSame('Translate From an Excel File to Symfony Translation format', $this->command->getDescription());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('sheet-name'));
        $this->assertTrue($definition->hasOption('book-name'));
    }

    public function testExecuteWithBothOptions(): void
    {
        $input = new ArrayInput([
            '--sheet-name' => 'common',
            '--book-name' => 'frontend',
        ]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('common', 'frontend');

        $this->translator
            ->expects($this->once())
            ->method('setFallbackLocales')
            ->with(['en', 'es_ES']);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('homepage.title', [], 'demo_common')
            ->willReturn('Translated text');

        $statusCode = $this->command->run($input, $output);

        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('Translation text for "homepage.title" in "es_ES": "Translated text"', $output->fetch());
    }

    public function testExecuteWithSheetNameOnly(): void
    {
        $input = new ArrayInput([
            '--sheet-name' => 'common',
        ]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('common', '');

        $this->translator
            ->method('trans')
            ->willReturn('Translated text');

        $statusCode = $this->command->run($input, $output);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testExecuteWithBookNameOnly(): void
    {
        $input = new ArrayInput([
            '--book-name' => 'frontend',
        ]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('', 'frontend');

        $this->translator
            ->method('trans')
            ->willReturn('Translated text');

        $statusCode = $this->command->run($input, $output);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testExecuteWithNoOptions(): void
    {
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('', '');

        $this->translator
            ->method('trans')
            ->willReturn('Translated text');

        $statusCode = $this->command->run($input, $output);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testBuildParamsFromInputWithAllOptions(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('hasOption')->willReturn(true);
        $input->method('getOption')->willReturnCallback(function ($option) {
            return match ($option) {
                'sheet-name' => 'test_sheet',
                'book-name' => 'test_book',
                default => null,
            };
        });

        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('buildParamsFromInput');

        $result = $method->invoke($this->command, $input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sheet_name', $result);
        $this->assertArrayHasKey('book_name', $result);
        $this->assertSame('test_sheet', $result['sheet_name']);
        $this->assertSame('test_book', $result['book_name']);
    }

    public function testBuildParamsFromInputWithEmptyOptions(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('hasOption')->willReturn(false);

        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('buildParamsFromInput');

        $result = $method->invoke($this->command, $input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sheet_name', $result);
        $this->assertArrayHasKey('book_name', $result);
        $this->assertSame('', $result['sheet_name']);
        $this->assertSame('', $result['book_name']);
    }

    public function testShowTranslatedFragmentUsesCorrectParameters(): void
    {
        $output = new BufferedOutput();

        $this->translator
            ->expects($this->once())
            ->method('setFallbackLocales')
            ->with(['en', 'es_ES']);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with(
                $this->equalTo('homepage.title'),
                $this->equalTo([]),
                $this->equalTo('demo_common')
            )
            ->willReturn('Título de inicio seguro del traductor de hojas de cálculo');

        $this->spreadsheetTranslator
            ->method('processSheet');

        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('showTranslatedFragment');

        $method->invoke($this->command, $output);

        $outputText = $output->fetch();
        $this->assertStringContainsString('Translation text for "homepage.title" in "es_ES"', $outputText);
        $this->assertStringContainsString('Título de inicio seguro del traductor de hojas de cálculo', $outputText);
    }

    public function testExecuteCallsProcessSheetExactlyOnce(): void
    {
        $input = new ArrayInput([
            '--sheet-name' => 'test',
            '--book-name' => 'demo',
        ]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet');

        $this->translator
            ->method('trans')
            ->willReturn('Test');

        $this->command->run($input, $output);
    }

    public function testExecuteReturnsSuccessEvenWithEmptyTranslation(): void
    {
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->method('processSheet');

        $this->translator
            ->method('trans')
            ->willReturn('');

        $statusCode = $this->command->run($input, $output);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testCommandInheritFromSymfonyCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    /**
     * Test that special characters in options are handled correctly
     */
    public function testExecuteWithSpecialCharactersInOptions(): void
    {
        $input = new ArrayInput([
            '--sheet-name' => 'common_with-dash',
            '--book-name' => 'frontend.test',
        ]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('common_with-dash', 'frontend.test');

        $this->translator
            ->method('trans')
            ->willReturn('Test');

        $statusCode = $this->command->run($input, $output);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    /**
     * Test with Unicode characters
     */
    public function testExecuteWithUnicodeCharactersInOptions(): void
    {
        $input = new ArrayInput([
            '--sheet-name' => 'común',
            '--book-name' => '前端',
        ]);
        $output = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('común', '前端');

        $this->translator
            ->method('trans')
            ->willReturn('Test');

        $statusCode = $this->command->run($input, $output);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }
}
