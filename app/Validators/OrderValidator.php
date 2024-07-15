<?php

namespace App\Validators;

use App\Interfaces\Validators\OrderValidatorInterface;
use Illuminate\Validation\ValidationException;

class OrderValidator implements OrderValidatorInterface
{
    public function validate(array $data): void
    {
        // 驗證 name 是否包含非英文字母
        if (preg_match('/[^a-zA-Z\s]/', $data['name'])) {
            throw ValidationException::withMessages(['name' => 'Name contains non-English characters']);
        }

        // 驗證 name 每個單字首字母是否大寫
        if (ucwords($data['name']) !== $data['name']) {
            throw ValidationException::withMessages(['name' => 'Name is not capitalized']);
        }

        // 驗證 price 是否超過 2000
        if ($data['price'] > 2000) {
            throw ValidationException::withMessages(['price' => 'Price is over 2000']);
        }

        // 驗證 currency 格式是否正確
        if (!in_array($data['currency'], ['TWD', 'USD'])) {
            throw ValidationException::withMessages(['currency' => 'Currency format is wrong']);
        }
    }
}
