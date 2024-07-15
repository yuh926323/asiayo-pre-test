<?php

namespace App\Services;

use App\Interfaces\Services\OrderServiceInterface;
use App\Interfaces\Validators\OrderValidatorInterface;

class OrderService implements OrderServiceInterface
{
    protected $validator;

    public function __construct(OrderValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($data): void
    {
        $this->validator->validate($data);
    }

    public function transform($data): array
    {
        // 處理貨幣轉換
        if ($data['currency'] === 'USD') {
            $data['price'] *= config('constants.exchange.USD.TWD');
            $data['currency'] = 'TWD';
        }

        return $data;
    }
}