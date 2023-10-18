<?php

namespace OwlnextFr\DtoExport\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class DTOExportException - Exception thrown when an error occurs during DTO export.
 * @package OwlnextFr\DtoExport\Exception
 */
class DTOExportException extends HttpException
{
    /**
     * DTOExportException constructor.
     *
     * @param string $message Exception message.
     * @param \Throwable|null $previous Previous exception.
     * @param array $headers Headers to add to the current response.
     * @param int $code Exception code.
     */
    public function __construct(
        string $message = '',
        \Throwable $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $previous, $headers, $code);
    }
}