<?php

namespace App\Services;

class Service
{
    private $error;

    public function setError(string $error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}
