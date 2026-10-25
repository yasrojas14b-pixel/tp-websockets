<?php

namespace App\Exceptions;

use RuntimeException;

class SlotUnavailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Ese horario ya no está disponible. Elegí otro, por favor.');
    }
}
