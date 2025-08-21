<?php
namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse($data, $message, $code )
    {
        return response()->json([
            'success' => true,
            'status' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function errorResponse($message, $code , $data = null)
    {
        return response()->json([
            'success' => false,
            'status' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
