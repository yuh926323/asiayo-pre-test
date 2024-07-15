<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_order_validation_fails_for_wrong_price_type()
    {
        $response = $this->postJson('/api/orders', [
            'id' => 'A0000001',
            'name' => '!@_%$*(@!#)',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road'
            ],
            'price' => '1500a',
            'currency' => 'TWD'
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'errorMessage' => 'The price field must be a number.',
                 ]);
    }

    public function test_order_validation_fails_for_non_english_name()
    {
        $response = $this->postJson('/api/orders', [
            'id' => 'A0000001',
            'name' => '!@_%$*(@!#)',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road'
            ],
            'price' => '1500',
            'currency' => 'TWD'
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'errorMessage' => 'Name contains non-English characters',
                 ]);
    }

    public function test_order_validation_fails_for_uncapitalized_name()
    {
        $response = $this->postJson('/api/orders', [
            'id' => 'A0000001',
            'name' => 'melody holiday inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road'
            ],
            'price' => '1500',
            'currency' => 'TWD'
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'errorMessage' => 'Name is not capitalized',
                 ]);
    }

    public function test_order_validation_fails_for_price_over_2000()
    {
        $response = $this->postJson('/api/orders', [
            'id' => 'A0000001',
            'name' => 'Melody Holiday Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road'
            ],
            'price' => '2050',
            'currency' => 'TWD'
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'errorMessage' => 'Price is over 2000',
                 ]);
    }

    public function test_order_validation_fails_for_invalid_currency()
    {
        $response = $this->postJson('/api/orders', [
            'id' => 'A0000001',
            'name' => 'Melody Holiday Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road'
            ],
            'price' => '1500',
            'currency' => 'JPY'
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'errorMessage' => 'Currency format is wrong',
                 ]);
    }

    public function test_order_processing_for_usd_currency()
    {
        $input = [
            'id' => 'A0000001',
            'name' => 'Melody Holiday Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road'
            ],
            'price' => '50',
            'currency' => 'USD'
        ];
        $response = $this->postJson('/api/orders', $input);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => 'A0000001',
                     'name' => 'Melody Holiday Inn',
                     'address' => [
                         'city' => 'taipei-city',
                         'district' => 'da-an-district',
                         'street' => 'fuxing-south-road'
                     ],
                     'price' => $input['price'] * 31,
                     'currency' => 'TWD'
                 ]);
    }

    public function test_order_processing_successful()
    {
        $input = [
            'id' => 'A0000001',
            'name' => 'Melody Holiday Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road'
            ],
            'price' => '1500',
            'currency' => 'TWD'
        ];
        $expect = $input;
        $response = $this->postJson('/api/orders', $input);

        $response->assertStatus(200)
                 ->assertJson($expect);
    }
}
