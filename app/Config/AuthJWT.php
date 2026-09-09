<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthJWT as ShieldAuthJWT;

class AuthJWT extends ShieldAuthJWT
{
    public string $authenticatorHeader = 'Authorization';

    public array $defaultClaims = [
        'iss' => 'codeigniter-4-base-project',
    ];

    /**
     * Override the secret in production via .env:
     * authjwt.keys.default.0.secret = your-random-secret
     */
    public array $keys = [
        'default' => [
            [
                'kid'    => '',
                'alg'    => 'HS256',
                'secret' => 'change-me-generate-with-php-random-bytes-32',
            ],
        ],
    ];

    public int $timeToLive = DAY;

    public int $recordLoginAttempt = Auth::RECORD_LOGIN_ATTEMPT_FAILURE;
}
