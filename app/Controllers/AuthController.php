<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CollabModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\JWTManager;
use stdClass;

class AuthController extends BaseController
{
    use ResponseTrait;

    public function getUser(): ResponseInterface
    {
        try {
            $decoded = $this->decodeJwt();
            $user    = auth()->getProvider()->find($decoded->sub);

            if ($user === null) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                    ->setJSON([
                        'status'  => ResponseInterface::HTTP_NOT_FOUND,
                        'message' => 'User not found.',
                    ]);
            }

            return $this->respond([
                'actor' => 'staff',
                'user'  => $user,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status'  => ResponseInterface::HTTP_UNAUTHORIZED,
                    'message' => $e->getMessage(),
                ]);
        }
    }

    public function getToken(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? [];

        $username = $this->resolveUsernameFromCollab($data);

        if ($username === null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'token'   => null,
                    'type'    => 'no user',
                    'message' => 'keylink is required.',
                    'status'  => ResponseInterface::HTTP_BAD_REQUEST,
                ]);
        }

        $user = auth()->getProvider()
            ->where('username', $username)
            ->where('active', true)
            ->first();

        if ($user === null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'token'   => null,
                    'type'    => 'no user',
                    'message' => 'no user',
                    'status'  => ResponseInterface::HTTP_BAD_REQUEST,
                ]);
        }

        /** @var JWTManager $manager */
        $manager = service('jwtmanager');
        $jwt     = $manager->generateToken($user, ['actor' => 'staff']);

        return $this->respond([
            'access_token' => $jwt,
            'actor'        => 'staff',
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveUsernameFromCollab(array $data): ?string
    {
        if (ENVIRONMENT === 'production') {
            if (empty($data['keylink'])) {
                return null;
            }

            $collabUser = model(CollabModel::class)->getUserName($data['keylink']);

            return $collabUser['username'] ?? null;
        }

        return env('auth.devUsername', 'devuser');
    }

    private function getBearerToken(): string
    {
        $header = $this->request->getHeaderLine(config('AuthJWT')->authenticatorHeader ?? 'Authorization');

        if (str_starts_with($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        return trim($header);
    }

    private function decodeJwt(): stdClass
    {
        $token = $this->getBearerToken();

        if ($token === '') {
            throw new \RuntimeException('No token provided.');
        }

        return service('jwtmanager')->parse($token);
    }
}
