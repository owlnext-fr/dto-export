<?php

namespace OwlnextFr\DtoExport\Twig;

use OwlnextFr\DtoExport\Service\languages\dart\DartLanguageUtils;
use OwlnextFr\DtoExport\Service\languages\typescript\TypeScriptLanguageUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('dart_to_path', [DartLanguageUtils::class, 'toPath']),
            new TwigFilter('dart_to_dto_path', [DartLanguageUtils::class, 'toDTOPath']),
            new TwigFilter('dart_filter_type', [DartLanguageUtils::class, 'filterType']),
            new TwigFilter('dart_to_object_basename', [DartLanguageUtils::class, 'toObjectBasename']),
            new TwigFilter('ts_to_path', [TypeScriptLanguageUtils::class, 'toPath']),
            new TwigFilter('ts_to_dto_path', [TypeScriptLanguageUtils::class, 'toDTOPath']),
            new TwigFilter('ts_filter_type', [TypeScriptLanguageUtils::class, 'filterType']),
            new TwigFilter('ts_to_object_basename', [TypeScriptLanguageUtils::class, 'toObjectBasename']),

            // generics
            new TwigFilter('ucfirst', [self::class, 'ucfirst']),
        ];
    }
    
    public static function ucfirst(string $string): string
    {
        return ucfirst($string);
    }

}