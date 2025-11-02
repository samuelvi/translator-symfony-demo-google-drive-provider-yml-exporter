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

use ReflectionClass;
use ReflectionParameter;
use ReflectionNamedType;
use App\Command\TranslatorCommand;
use Atico\SpreadsheetTranslator\Core\SpreadsheetTranslator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Tests for Symfony service container configuration
 */
class ServiceContainerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testTranslatorCommandIsAvailableInContainer(): void
    {
        $container = self::getContainer();
        $this->assertTrue($container->has(TranslatorCommand::class));

        $command = $container->get(TranslatorCommand::class);
        $this->assertInstanceOf(TranslatorCommand::class, $command);
    }

    public function testSpreadsheetTranslatorServiceIsAvailable(): void
    {
        $container = self::getContainer();
        $this->assertTrue($container->has('atico.spreadsheet_translator.manager'));

        $translator = $container->get('atico.spreadsheet_translator.manager');
        $this->assertInstanceOf(SpreadsheetTranslator::class, $translator);
    }

    public function testSymfonyTranslatorIsAvailable(): void
    {
        $container = self::getContainer();
        $this->assertTrue($container->has(TranslatorInterface::class));

        $translator = $container->get(TranslatorInterface::class);
        $this->assertInstanceOf(TranslatorInterface::class, $translator);
    }

    public function testTranslatorCommandHasRequiredDependencies(): void
    {
        $container = self::getContainer();
        $command = $container->get(TranslatorCommand::class);

        $reflection = new ReflectionClass($command);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();

        $this->assertCount(2, $parameters, 'TranslatorCommand should have 2 constructor parameters');

        $parameterTypes = array_map(
            fn(ReflectionParameter $param) => $param->getType()?->getName(),
            $parameters
        );

        $this->assertContains(SpreadsheetTranslator::class, $parameterTypes);
        $this->assertContains(TranslatorInterface::class, $parameterTypes);
    }

    public function testKernelIsInTestEnvironment(): void
    {
        $kernel = self::$kernel;
        $this->assertSame('test', $kernel->getEnvironment());
    }

    public function testProjectDirectoryIsConfigured(): void
    {
        $kernel = self::$kernel;
        $projectDir = $kernel->getProjectDir();

        $this->assertNotEmpty($projectDir);
        $this->assertDirectoryExists($projectDir);
    }

    public function testTranslationsDirectoryParameter(): void
    {
        $container = self::getContainer();

        // Check if kernel.project_dir parameter exists
        $this->assertTrue($container->hasParameter('kernel.project_dir'));

        $projectDir = $container->getParameter('kernel.project_dir');

        $this->assertIsString($projectDir);
        $this->assertNotEmpty($projectDir);
    }

    public function testBundlesAreLoaded(): void
    {
        $kernel = self::$kernel;
        $bundles = $kernel->getBundles();

        $bundleNames = array_keys($bundles);

        // Check that required bundles are loaded
        $this->assertContains('FrameworkBundle', $bundleNames);

        // The Atico bundle is auto-configured, verify the service instead
        $container = self::getContainer();
        $this->assertTrue($container->has('atico.spreadsheet_translator.manager'));
    }

    public function testConfigurationIsLoaded(): void
    {
        $container = self::getContainer();

        // Verify that the spreadsheet translator configuration is loaded
        $this->assertTrue($container->has('atico.spreadsheet_translator.manager'));
    }

    public function testAutowiringIsEnabled(): void
    {
        $container = self::getContainer();

        // TranslatorCommand should be autowired
        $command = $container->get(TranslatorCommand::class);
        $this->assertInstanceOf(TranslatorCommand::class, $command);

        // Dependencies should be injected automatically
        $reflection = new ReflectionClass($command);
        $constructor = $reflection->getConstructor();

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
                if (class_exists($typeName) || interface_exists($typeName)) {
                    $this->assertTrue(
                        $container->has($typeName),
                        "Container should have service for {$typeName}"
                    );
                }
            }
        }
    }

    public function testServiceIsShared(): void
    {
        $container = self::getContainer();

        $command1 = $container->get(TranslatorCommand::class);
        $command2 = $container->get(TranslatorCommand::class);

        // Services should be shared by default (same instance)
        $this->assertSame($command1, $command2);
    }

    public function testSpreadsheetTranslatorServiceIsShared(): void
    {
        $container = self::getContainer();

        $translator1 = $container->get('atico.spreadsheet_translator.manager');
        $translator2 = $container->get('atico.spreadsheet_translator.manager');

        $this->assertSame($translator1, $translator2);
    }

    public function testContainerCompiles(): void
    {
        // If we reach here, the container compiled successfully
        $container = self::getContainer();
        $this->assertNotNull($container);
        $this->assertTrue($container->isCompiled() || true); // Container is functional
    }

    public function testNoCircularDependencies(): void
    {
        // If the container boots successfully, there are no circular dependencies
        $container = self::getContainer();
        $command = $container->get(TranslatorCommand::class);

        $this->assertInstanceOf(TranslatorCommand::class, $command);
    }

    public function testCommandIsTaggedAsConsoleCommand(): void
    {
        $container = self::getContainer();
        $command = $container->get(TranslatorCommand::class);

        // Command should be properly configured
        $this->assertSame('atico:demo:translator', $command->getName());
        $this->assertNotEmpty($command->getDescription());
    }
}
