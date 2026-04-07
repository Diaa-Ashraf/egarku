<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = '', int $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function error(string $message, int $code = 400, $errors = null)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    protected function created($data = null, string $message = 'تم الإنشاء بنجاح')
    {
        return $this->success(data: $data, message: $message, code: 201);
    }

    protected function notFound(string $message = 'غير موجود')
    {
        return $this->error($message, 404);
    }

    protected function unauthorized(string $message = 'غير مصرح')
    {
        return $this->error($message, 403);
    }
}
