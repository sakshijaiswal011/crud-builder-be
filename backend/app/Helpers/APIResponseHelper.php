<?php

namespace App\Helpers;

class APIResponseHelper
{
    public const SUCCESS = 200;
    public const CREATED = 201;
    public const NO_CONTENT = 204;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const VALIDATION_ERROR = 422;
    public const LOCKED = 423;
    public const SOMETHING_WENT_WRONG = 500;

    /**
     * Build a successful API response payload.
     *
     * @param  int  $code
     * @param  string  $custom_message
     * @param  mixed  $data
     * @return array
     */
    public static function success(int $code = self::SUCCESS, string $custom_message = '', $data = null): array
    {
        return array_merge([
            'success' => true,
            'code' => $code,
            'message' => $custom_message,
        ], $data !== null ? ['data' => $data] : []);
    }

    /**
     * Build an error API response payload.
     *
     * @param  int  $code
     * @param  string  $custom_message
     * @param  mixed  $errors
     * @return array
     */
    public static function error(int $code = self::SOMETHING_WENT_WRONG, string $custom_message = '', $errors = null): array
    {
        return array_merge([
            'success' => false,
            'code' => $code,
            'message' => $custom_message,
        ], $errors !== null ? ['errors' => $errors] : []);
    }
}
