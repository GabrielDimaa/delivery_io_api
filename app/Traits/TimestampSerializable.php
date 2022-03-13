<?php

namespace App\Traits;

use DateTimeInterface;

trait TimestampSerializable
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('America/Sao_Paulo')->format('Y-m-d H:i:s');
    }
}
