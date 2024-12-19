<?php

namespace App\Traits;

trait AjaxResponse
{
    public function ajaxResponse($success, $message, $data="")
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
         ]);
    }
}