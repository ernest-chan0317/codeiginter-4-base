<?php

namespace Config;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends \Fluent\Cors\Config\Cors
{
    public $allowedHeaders = ['*'];
    public $allowedMethods = ['*'];
    public $allowedOrigins = ['*'];
    public $allowedOriginsPatterns = [];
    public $exposedHeaders = [];
    public $maxAge = 0;
    public $supportsCredentials = false;
}
