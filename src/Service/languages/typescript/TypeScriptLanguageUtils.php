<?php

namespace OwlnextFr\DtoExport\Service\languages\typescript;

use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class TypeScriptLanguageUtils - Twig utils for typescript language
 * @package App\Service\Utils\DTOExport\languages\typescript
 */
class TypeScriptLanguageUtils implements RuntimeExtensionInterface
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
     * Transforms PHP type to typescript type using field metadata
     *
     * @param string $type PHP type
     * @param array $fieldMetadata field metadata
     *
     * @return string typescript type
     */
    public static function filterType(string $type, array $fieldMetadata = []): string
    {
        if(true === is_object($type) && true === str_starts_with($type, 'App\\Entity')) {
            $type = 'string';
        } elseif ('string' === $type) {
            $type = 'string';
        } elseif ('bool' === $type) {
            $type = 'boolean';
        } elseif (true === in_array($type, ['int', 'integer', 'float', 'double'])) {
            $type = 'number';
        } elseif (true === in_array($type, ['DateTime', 'DateTimeImmutable', 'DateTimeInterface'])) {
            $type = 'Date';
        } elseif ('array' === $type) {
            $type = sprintf('%s[]', self::filterType(self::toObjectBasename($fieldMetadata['list_of'])));
        } elseif ('mixed' === $type) {
            $type = 'any';
        } elseif (true === array_key_exists('is_built_in', $fieldMetadata)) {
            if (false === $fieldMetadata['is_built_in']) {
                $type = self::filterType(self::toObjectBasename($type));
            }
        }

        return $type;
    }

    /**
     * Transforms PHP path to typescript path (namespace to path)
     *
     * @param string $path PHP path
     *
     * @return string typescript path
     */
    public static function toDTOPath(string $path): string
    {
        $result = $path;

        $result = str_replace("App\\", "", $result);
        $result = self::toPath($result);

        $split = explode("/", $result);
        $split[count($split) - 1] = lcfirst($split[count($split) - 1]);

        return implode("/", $split);
    }

    /**
     * Applies path transformations for typescript
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