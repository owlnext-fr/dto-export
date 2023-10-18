<?php

namespace OwlnextFr\DtoExport\Attribute;

use Attribute;

/**
 * Class ListOf - Attribute to specify the type of the values contained in the array.
 * @package OwlnextFr\DtoExport\Attribute\DTOExport
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ListOf
{

    /** @var string $type Type of the values contained in the array. */
    public string $type;

    /**
     * ListOf Constructor.
     *
     * @param string $type Type of the values contained in the array.
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

}