<?php

namespace App\Interfaces\Validators;

interface OrderValidatorInterface
{
    public function validate(array $data): void;
}