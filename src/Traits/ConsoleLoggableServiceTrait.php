<?php

namespace OwlnextFr\DtoExport\Traits;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Trait ConsoleLoggableServiceTrait - Provides a console I/O helper for services.
 * @package OwlnextFr\DtoExport\Traits
 */
trait ConsoleLoggableServiceTrait
{

    /** @var null|SymfonyStyle $io The console I/O helper */
    private null|SymfonyStyle $io = null;

    /**
     * Gets the Symfony console I/O helper. If no helper is pushed by 'setIO()' then the helper will be a dull one.
     *
     * @return SymfonyStyle The Symfony console I/O helper.
     */
    public function getIO(): SymfonyStyle
    {
        if (true === is_null($this->io)) {
            $this->io = new SymfonyStyle(
                input: new StringInput(input: ''),
                output: new NullOutput());
        }

        return $this->io;
    }

    /**
     * Sets the Symfony console I/O helper for this service.
     *
     * @param SymfonyStyle $io The Symfony console I/O helper to set for this service.
     *
     * @return ConsoleLoggableServiceTrait This service.
     */
    public function setIO(SymfonyStyle $io): self
    {
        $this->io = $io;

        return $this;
    }

}