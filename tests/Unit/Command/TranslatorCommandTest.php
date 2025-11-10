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
namespace App\Tests\Unit\Command;

use ReflectionClass;
use App\Command\TranslatorCommand;
use Atico\SpreadsheetTranslator\Core\SpreadsheetTranslator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Unit tests for TranslatorCommand
 *
 * Tests the command in isolation by mocking all dependencies
 */
final class TranslatorCommandTest extends TestCase
{
    private MockObject $spreadsheetTranslator;

    private MockObject $translator;

    private TranslatorCommand $translatorCommand;

    protected function setUp(): void
    {
        $this->spreadsheetTranslator = $this->createMock(SpreadsheetTranslator::class);

        // Create a mock that supports TranslatorInterface and setFallbackLocales method
        $this->translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['trans', 'getLocale'])
            ->addMethods(['setFallbackLocales'])
            ->getMock();

        $this->translatorCommand = new TranslatorCommand($this->spreadsheetTranslator, $this->translator);
    }

    public function testCommandIsConfiguredCorrectly(): void
    {
        $this->assertSame('atico:demo:translator', $this->translatorCommand->getName());
        $this->assertSame('Translate From an Excel File to Symfony Translation format', $this->translatorCommand->getDescription());

        $inputDefinition = $this->translatorCommand->getDefinition();
        $this->assertTrue($inputDefinition->hasOption('sheet-name'));
        $this->assertTrue($inputDefinition->hasOption('book-name'));
    }

    public function testExecuteWithBothOptions(): void
    {
        $arrayInput = new ArrayInput([
            '--sheet-name' => 'common',
            '--book-name' => 'frontend',
        ]);
        $bufferedOutput = new BufferedOutput();

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

        $statusCode = $this->translatorCommand->run($arrayInput, $bufferedOutput);

        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('Translation text for "homepage.title" in "es_ES": "Translated text"', $bufferedOutput->fetch());
    }

    public function testExecuteWithSheetNameOnly(): void
    {
        $arrayInput = new ArrayInput([
            '--sheet-name' => 'common',
        ]);
        $bufferedOutput = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('common', '');

        $this->translator
            ->method('trans')
            ->willReturn('Translated text');

        $statusCode = $this->translatorCommand->run($arrayInput, $bufferedOutput);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testExecuteWithBookNameOnly(): void
    {
        $arrayInput = new ArrayInput([
            '--book-name' => 'frontend',
        ]);
        $bufferedOutput = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('', 'frontend');

        $this->translator
            ->method('trans')
            ->willReturn('Translated text');

        $statusCode = $this->translatorCommand->run($arrayInput, $bufferedOutput);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testExecuteWithNoOptions(): void
    {
        $arrayInput = new ArrayInput([]);
        $bufferedOutput = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('', '');

        $this->translator
            ->method('trans')
            ->willReturn('Translated text');

        $statusCode = $this->translatorCommand->run($arrayInput, $bufferedOutput);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testBuildParamsFromInputWithAllOptions(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('hasOption')->willReturn(true);
        $input->method('getOption')->willReturnCallback(fn(string $option): ?string => match ($option) {
            'sheet-name' => 'test_sheet',
            'book-name' => 'test_book',
            default => null,
        });

        $reflectionClass = new ReflectionClass($this->translatorCommand);
        $reflectionMethod = $reflectionClass->getMethod('buildParamsFromInput');

        $result = $reflectionMethod->invoke($this->translatorCommand, $input);

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

        $reflectionClass = new ReflectionClass($this->translatorCommand);
        $reflectionMethod = $reflectionClass->getMethod('buildParamsFromInput');

        $result = $reflectionMethod->invoke($this->translatorCommand, $input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sheet_name', $result);
        $this->assertArrayHasKey('book_name', $result);
        $this->assertSame('', $result['sheet_name']);
        $this->assertSame('', $result['book_name']);
    }

    public function testShowTranslatedFragmentUsesCorrectParameters(): void
    {
        $bufferedOutput = new BufferedOutput();

        $this->translator
            ->expects($this->once())
            ->method('setFallbackLocales')
            ->with(['en', 'es_ES']);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with(
                'homepage.title',
                [],
                'demo_common'
            )
            ->willReturn('Título de inicio seguro del traductor de hojas de cálculo');

        $this->spreadsheetTranslator
            ->method('processSheet');

        $reflectionClass = new ReflectionClass($this->translatorCommand);
        $reflectionMethod = $reflectionClass->getMethod('showTranslatedFragment');

        $reflectionMethod->invoke($this->translatorCommand, $bufferedOutput);

        $outputText = $bufferedOutput->fetch();
        $this->assertStringContainsString('Translation text for "homepage.title" in "es_ES"', $outputText);
        $this->assertStringContainsString('Título de inicio seguro del traductor de hojas de cálculo', $outputText);
    }

    public function testExecuteCallsProcessSheetExactlyOnce(): void
    {
        $arrayInput = new ArrayInput([
            '--sheet-name' => 'test',
            '--book-name' => 'demo',
        ]);
        $bufferedOutput = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet');

        $this->translator
            ->method('trans')
            ->willReturn('Test');

        $this->translatorCommand->run($arrayInput, $bufferedOutput);
    }

    public function testExecuteReturnsSuccessEvenWithEmptyTranslation(): void
    {
        $arrayInput = new ArrayInput([]);
        $bufferedOutput = new BufferedOutput();

        $this->spreadsheetTranslator
            ->method('processSheet');

        $this->translator
            ->method('trans')
            ->willReturn('');

        $statusCode = $this->translatorCommand->run($arrayInput, $bufferedOutput);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    public function testCommandInheritFromSymfonyCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->translatorCommand);
    }

    /**
     * Test that special characters in options are handled correctly
     */
    public function testExecuteWithSpecialCharactersInOptions(): void
    {
        $arrayInput = new ArrayInput([
            '--sheet-name' => 'common_with-dash',
            '--book-name' => 'frontend.test',
        ]);
        $bufferedOutput = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('common_with-dash', 'frontend.test');

        $this->translator
            ->method('trans')
            ->willReturn('Test');

        $statusCode = $this->translatorCommand->run($arrayInput, $bufferedOutput);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }

    /**
     * Test with Unicode characters
     */
    public function testExecuteWithUnicodeCharactersInOptions(): void
    {
        $arrayInput = new ArrayInput([
            '--sheet-name' => 'común',
            '--book-name' => '前端',
        ]);
        $bufferedOutput = new BufferedOutput();

        $this->spreadsheetTranslator
            ->expects($this->once())
            ->method('processSheet')
            ->with('común', '前端');

        $this->translator
            ->method('trans')
            ->willReturn('Test');

        $statusCode = $this->translatorCommand->run($arrayInput, $bufferedOutput);

        $this->assertSame(Command::SUCCESS, $statusCode);
    }
}
