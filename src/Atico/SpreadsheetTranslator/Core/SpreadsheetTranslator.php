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

namespace Atico\SpreadsheetTranslator\Core;

use Atico\SpreadsheetTranslator\Core\Processor\BookProcessor;
use Atico\SpreadsheetTranslator\Core\Processor\SheetProcessor;
use Exception;

/**
 * Main facade class for Spreadsheet Translator functionality
 */
class SpreadsheetTranslator
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        private readonly array $configuration
    ) {
    }

    /**
     * Process a single sheet from a specific book
     *
     * @param string $sheetName The name of the sheet to process
     * @param string $bookName The name of the book (configuration key)
     * @throws Exception
     */
    public function processSheet(string $sheetName, string $bookName): void
    {
        $bookName = $this->resolveBookName($bookName);
        $wrappedConfiguration = $this->wrapConfigurationForProcessor($bookName);

        $sheetProcessor = new SheetProcessor($wrappedConfiguration);
        $sheetProcessor->processSheet($sheetName);
    }

    /**
     * Process all sheets from a specific book
     *
     * @param string $bookName The name of the book (configuration key)
     * @throws Exception
     */
    public function processBook(string $bookName): void
    {
        $bookName = $this->resolveBookName($bookName);
        $wrappedConfiguration = $this->wrapConfigurationForProcessor($bookName);

        $bookProcessor = new BookProcessor($wrappedConfiguration);
        $bookProcessor->processBook();
    }

    /**
     * Resolve the book name, using the first available if not provided
     *
     * @throws Exception
     */
    private function resolveBookName(string $bookName): string
    {
        if ($bookName === '' || $bookName === '0') {
            $keys = array_keys($this->configuration);
            if ($keys === []) {
                throw new Exception('No configuration available');
            }

            return $keys[0];
        }

        if (!isset($this->configuration[$bookName])) {
            throw new Exception(sprintf('Configuration for book "%s" not found', $bookName));
        }

        return $bookName;
    }

    /**
     * Wrap configuration in the format expected by Configuration class
     * The Configuration class expects: array_values($config)[0][$groupName]
     *
     * @return array<string, mixed>
     */
    private function wrapConfigurationForProcessor(string $bookName): array
    {
        return [$bookName => $this->configuration[$bookName]];
    }
}
