<?php

namespace OwlnextFr\DtoExport\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class OwlnextFrDtoExportExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );

        $loader->load('services.yaml');

//        $container->loadFromExtension('twig', [
//            'paths' => [
//                '%kernel.project_dir%/vendor/owlnext-fr/dto-export/templates' => 'owlnext_fr.dto_export',
//            ],
//        ]);
    }
}