<?php

namespace Application\Logger;

interface LoggerInterface
{
    public function log($message, $tag);

    public function addWriter($writer, array $options);
}