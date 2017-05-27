<?php

namespace ALS\Core\Support;

use Illuminate\Http\Response;

trait RestfulResponseTrait
{
    protected function jsonResponse($content, $message = 'OK', $status = 200,
        $errors = []
    ) {
        $formattedContent = [
            'api'    => [
                'format' => 'json'
            ],
            'status' => [
                'code'    => $status,
                'message' => $message
            ],
            'output' => [
                'data'   => $content,
                'errors' => $errors
            ]
        ];

        return $this->response($formattedContent, 200, 'application/json');
    }

    protected function response($content, $status = 200,
        $contentType = 'application/json', $headers = []
    ) {
        $headers = array_merge(['Content-Type' => $contentType], $headers);
        return new Response($content, $status, $headers);
    }
}