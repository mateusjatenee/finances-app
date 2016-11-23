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

    public function actionAuthorizationError()
    {
        return $this->error([
            'authorization' => [
                'You are not authorized to perform this action.',
            ],
        ], 403);
    }
}
