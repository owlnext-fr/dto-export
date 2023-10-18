<?php

namespace OwlnextFr\DtoExport;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use OwlnextFr\DtoExport\DependencyInjection\OwlnextFrDtoExportExtension;

class OwlnextFrDtoExportBundle extends AbstractBundle
{

    public function getContainerExtension(): null|ExtensionInterface
    {
        return new OwlnextFrDtoExportExtension();
    }
}