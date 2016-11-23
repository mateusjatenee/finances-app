<?php

namespace App\Traits;

trait ApiResponses
{
    public function error($errors, $statusCode = 404)
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], $statusCode);
    }
}
