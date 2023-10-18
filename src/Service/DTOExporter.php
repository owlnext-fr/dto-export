<?php

namespace OwlnextFr\DtoExport\Service;

use OwlnextFr\DtoExport\Attribute\ListOf;
use InvalidArgumentException;
use OwlnextFr\DtoExport\Exception\DTOExportException;
use OwlnextFr\DtoExport\Traits\ConsoleLoggableServiceTrait;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\UnicodeString;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DTOExporter
{

    use ConsoleLoggableServiceTrait;

    /** @var array $storage DTO object storage. */
    private array $storage;

    /** @var Environment $renderer Twig renderer. */
    private Environment $renderer;

    private string $templateRootDirectory;

    /** @var string[] List of languages that requires a project name. */
    public const REQUIRES_PROJECT_NAME = [
        'dart'
    ];

    public function __construct(#[TaggedIterator('app.exportable_dto')] $iterator, Environment $renderer, KernelInterface $kernel)
    {
        $this->storage = [];

        foreach ($iterator as $dto) {
            $this->storage[] = $dto;
        }

        $this->renderer = $renderer;
        $this->templateRootDirectory = sprintf("%s/vendor/owlnext-fr/dto-export/templates", $kernel->getProjectDir());
    }

    #region dispatcher

    /**
     * Executes a DTO export for the given type, path and options.
     *
     * @param string $type Type of export to execute.
     * @param string $exportPath Path where to export the DTOs.
     * @param array $options Options to pass to the exporter.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the given type is not supported.
     */
    public function export(string $type, string $exportPath, array $options = []): void
    {
        $methodName = sprintf("export%s", ucfirst($type));
        if (false === method_exists($this, $methodName)) {
            throw new InvalidArgumentException(sprintf("Cannot find export method for type %s", $type));
        }

        $this->{$methodName}($exportPath, $options);
    }

    #endregion

    #region exporters

    /**
     * Exports DTOs to typescript types and objects.
     *
     * @param string $exportPath Path where to export the DTOs.
     * @param array $options Options to pass to the exporter.
     *
     * @return void
     *
     * @throws LoaderError when the template cannot be found
     * @throws RuntimeError when an error occurred during template rendering
     * @throws SyntaxError when an error occurred during template parsing
     */
    private function exportTypescript(string $exportPath, array $options = []): void
    {
        $fs = new Filesystem();

        $totalStep = sizeof($this->storage) + 1;

        $step = 1;

        $this->getIO()->writeln(sprintf("[%s/%s] %s", $step, $totalStep, "Generating classmap metadata"));
        $metadata = $this->generateMetadata();

        foreach ($metadata as $m) {
            $classSubDir = "";
            if ('standard' !== $m['dto_type']) {
                $classSubDir = sprintf(
                    '%s/%s',
                    $m['package'],
                    ucfirst($m['dto_type']),
                );
            }

            $realClassSubDir = sprintf("%s/%s", $exportPath, $classSubDir);
            if (false === $fs->exists($realClassSubDir)) {
                $fs->mkdir($realClassSubDir);
            }

            $classPath = sprintf(
                '%s/%s.ts',
                $classSubDir,
                (new UnicodeString($m['object_name']))->camel()->toString()
            );

            $this->getIO()->writeln(sprintf("[%s/%s] %s", ++$step, $totalStep, sprintf("Generating %s", $classPath)));

            $rendered = $this->renderer->render(sprintf('@owlnext_fr.dto_export/ts/%s.ts.twig', $m['dto_type']), [
                'metadata' => $m,
                'options' => $options
            ]);

            $filePath = sprintf("%s/%s", $exportPath, $classPath);

            $createdBytes = file_put_contents($filePath, $rendered);

            if (false === $createdBytes) {
                throw new DTOExportException(sprintf("Cannot create file : %s", $filePath));
            }
        }
    }

    /**
     * Exports DTOs to dart classes.
     *
     * @param string $exportPath Path where to export the DTOs.
     * @param array $options Options to pass to the exporter.
     *
     * @return void
     *
     * @throws LoaderError when the template cannot be found
     * @throws RuntimeError when an error occurred during template rendering
     * @throws SyntaxError when an error occurred during template parsing
     */
    private function exportDart(string $exportPath, array $options = []): void
    {
        $fs = new Filesystem();

        $totalStep = sizeof($this->storage) + 1;

        $step = 1;

        $this->getIO()->writeln(sprintf("[%s/%s] %s", $step, $totalStep, "Generating classmap metadata"));
        $metadata = $this->generateMetadata();

        foreach ($metadata as $m) {
            $classSubDir = "";
            if ('standard' !== $m['dto_type']) {
                $classSubDir = sprintf(
                    '%s/%s',
                    $m['package'],
                    ucfirst($m['dto_type']),
                );
            }

            $realClassSubDir = sprintf("%s/%s", $exportPath, $classSubDir);
            if (false === $fs->exists($realClassSubDir)) {
                $fs->mkdir($realClassSubDir);
            }

            $classPath = sprintf(
                '%s/%s.dart',
                $classSubDir,
                (new UnicodeString($m['object_name']))->snake()
            );

            $this->getIO()->writeln(sprintf("[%s/%s] %s", ++$step, $totalStep, sprintf("Generating %s", $classPath)));

            $rendered = $this->renderer->render(sprintf('@owlnext_fr.dto_export/dart/%s.dart.twig', $m['dto_type']), [
                'metadata' => $m,
                'options' => $options
            ]);

            $filePath = sprintf("%s/%s", $exportPath, $classPath);

            $createdBytes = file_put_contents($filePath, $rendered);

            if (false === $createdBytes) {
                throw new DTOExportException(sprintf("Cannot create file : %s", $filePath));
            }
        }
    }

    #endregion

    #region utilities

    /**
     * Generates an array of classmap metadata.
     *
     * @return array Array of classmap metadata.
     */
    private function generateMetadata(): array
    {
        return $this->generateObjectAndFieldMetadata();
    }

    /**
     * Generates a storage with classpath as key.
     *
     * @return array Storage with classpath as key.
     */
    private function generateStorageWithClasspath(): array
    {
        $withClassPath = [];

        foreach ($this->storage as $dtoObject) {
            $withClassPath[get_class($dtoObject)] = $dtoObject;
        }

        return $withClassPath;
    }

    /**
     * Processes the DTOs to generate object and field metadata.
     *
     * @return array Object and field metadata.
     *
     * @throws \ReflectionException If a reflection error occurs.
     */
    private function generateObjectAndFieldMetadata(): array
    {
        $withClasspath = $this->generateStorageWithClasspath();

        $withFields = [];

        foreach ($withClasspath as $classPath => $dtoObject) {
            $objectData = new ReflectionClass($dtoObject);

            $dtoType = 'standard';
            if (true === str_contains(strtolower($classPath), 'input')) {
                $dtoType = 'input';
            }
            if (true === str_contains(strtolower($classPath), 'output')) {
                $dtoType = 'output';
            }

            $package = "";

            if ('standard' !== $dtoType) {
                $classPathSplit = explode('\\', $classPath);
                $package = $classPathSplit[2];
            }

            $withFields[$classPath] = [];
            $withFields[$classPath]['reflection_class'] = $objectData;
            $withFields[$classPath]['class_path'] = $classPath;
            $withFields[$classPath]['abstract_path'] = str_replace("App\\DTO\\", "", $classPath);
            $withFields[$classPath]['object_name'] = $objectData->getShortName();
            $withFields[$classPath]['dto_type'] = $dtoType;
            $withFields[$classPath]['package'] = $package;
            $withFields[$classPath]['imports'] = [];
            $withFields[$classPath]['fields'] = [];

            foreach ($objectData->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                if (false === ($property->getType() instanceof ReflectionNamedType)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            "Field %s in class %s is not single typed (is union or intersect) which is not supported",
                            $property->getName(),
                            $property->getDeclaringClass()->getName()
                        )
                    );
                }

                $fieldType = $property->getType()->getName();

                $fieldData = [
                    'type' => $fieldType,
                    'list_of' => null,
                    'is_nullable' => $property->getType()->allowsNull(),
                    'name' => $property->getName(),
                    'has_default_value' => $property->hasDefaultValue(),
                    'default_value' => $property->getDefaultValue(),
                    'is_built_in' => $property->getType()->isBuiltin()
                ];

                if (true === str_starts_with($fieldType, "App\\") &&
                    false === in_array($fieldType, $withFields[$classPath]['imports'])) {
                    $withFields[$classPath]['imports'][] = $fieldType;
                }

                if ('array' === $fieldType) {
                    $attributes = $property->getAttributes(ListOf::class);

                    if (1 !== sizeof($attributes)) {
                        throw new DTOExportException(
                            sprintf(
                                '0 or more than 1 "ListOf" attribute is found on %s::%s. Please specify only one type of element in the array with the ListOf attribute.',
                                $objectData->getName(),
                                $property->getName()
                            )
                        );
                    }

                    $listOfAttribute = $attributes[0];

                    $compoundType = $listOfAttribute->getArguments()['type'];

                    $fieldData['list_of'] = $compoundType;

                    if (true === str_starts_with($compoundType, "App\\") &&
                        false === in_array($compoundType, $withFields[$classPath]['imports'])) {
                        $withFields[$classPath]['imports'][] = $compoundType;
                    }
                }

                $withFields[$classPath]['fields'][$property->getName()] = $fieldData;
            }
        }

        return $withFields;
    }

    #endregion

}