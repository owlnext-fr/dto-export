<?php

namespace OwlnextFr\DtoExport\Service\languages\dart;

use Symfony\Component\String\UnicodeString;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class DartLanguageUtils - Twig utils for dart language
 * @package App\Service\Utils\DTOExport\languages\dart
 */
class DartLanguageUtils implements RuntimeExtensionInterface
{

    /**
     * Transforms object FQDN to object basename
     *
     * @param string $objectFQDN object FQDN
     *
     * @return string object basename
     */
    public static function toObjectBasename(string $objectFQDN): string
    {
        $comPoundTypePathParts = explode("\\", $objectFQDN);

        return end($comPoundTypePathParts);
    }

    /**
     * Transforms PHP type to dart type using field metadata
     *
     * @param string $type PHP type
     * @param array $fieldMetadata field metadata
     *
     * @return string dart type
     */
    public static function filterType(string $type, array $fieldMetadata = []): string
    {
        if(true === is_object($type) && true === str_starts_with($type, 'App\\Entity')) {
            $type = 'String';
        } elseif ('string' === $type) {
            $type = 'String';
        } elseif (true === in_array($type, ['DateTime', 'DateTimeImmutable', 'DateTimeInterface'])) {
            $type = 'DateTime';
        } elseif ('array' === $type) {
            $type = sprintf('List<%s?>', self::filterType(self::toObjectBasename($fieldMetadata['list_of'])));
        } elseif ('mixed' === $type) {
            $type = 'dynamic';
        } elseif ('float' === $type) {
            $type = 'double';
        } elseif (true === array_key_exists('is_built_in', $fieldMetadata)) {
            if (false === $fieldMetadata['is_built_in']) {
                $type = self::filterType(self::toObjectBasename($type));
            }
        }

        return $type;
    }

    /**
     * Transforms PHP path to dart path (namespace to path)
     *
     * @param string $path PHP path
     *
     * @return string dart path
     */
    public static function toDTOPath(string $path): string
    {
        $result = str_replace("App\\", "", $path);
        $result = self::toPath($result);

        $pathParts = explode('/', $result);
        $pathParts[sizeof($pathParts) - 1] = (new UnicodeString($pathParts[sizeof($pathParts) - 1]))->snake();

        return implode('/', $pathParts);
    }

    /**
     * Applies path transformations for dart
     *
     * @param string $path path
     *
     * @return string transformed path
     */
    public static function toPath(string $path): string
    {
        return str_replace("\\", "/", $path);
    }

}