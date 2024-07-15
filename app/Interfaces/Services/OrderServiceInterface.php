<?php

namespace App\Interfaces\Services;

interface OrderServiceInterface
{
    public function validate(array $data): void;
    public function transform(array $data): array;
}