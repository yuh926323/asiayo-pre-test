<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\FormatOrderRequest;
use App\Interfaces\Services\OrderServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    protected OrderServiceInterface $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    public function formatOrder(FormatOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->orderService->validate($data);
            $order = $this->orderService->transform($data);

            return response()->json($order);
        } catch (ValidationException $e) {
            return response()->json([
                'errorMessage' => $e->getMessage(),
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}