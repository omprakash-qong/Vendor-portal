<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\ValidatePostSize as BaseValidatePostSize;

class ValidatePostSize extends BaseValidatePostSize
{
    // Allow up to 50MB total POST size for vendor file uploads
    protected function getPostMaxSize(): int
    {
        return 50 * 1048576; // 50MB
    }
}
