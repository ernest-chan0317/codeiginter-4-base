<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use Config\Services;

class AppJwtAuth implements FilterInterface
{
    /**
     * @param array|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        /** @var JWT $authenticator */
        $authenticator = auth('jwt')->getAuthenticator();
        $token         = $authenticator->getTokenFromRequest($request);

        if ($token === '') {
            return Services::response()
                ->setJSON([
                    'error' => lang('Auth.noToken', [config('AuthJWT')->authenticatorHeader]),
                ])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $result = $authenticator->attempt(['token' => $token]);

        if (! $result->isOK()) {
            return Services::response()
                ->setJSON(['error' => $result->reason()])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (setting('Auth.recordActiveDate')) {
            $authenticator->recordActiveDate();
        }
    }

    /**
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
