<?php

namespace OwlnextFr\DtoExport\DTO\Impl;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface ExportableDTOInterface - This interface is used to autoconfigure DTOs that can be exported.
 * @package App\DTO\Impl
 */
#[AutoconfigureTag('app.exportable_dto')]
interface ExportableDTOInterface
{

}