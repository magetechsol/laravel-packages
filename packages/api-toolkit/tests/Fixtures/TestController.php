<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use MageTech\ApiToolkit\Concerns\HasApiResponse;

class TestController extends Controller
{
    use HasApiResponse;

    /**
     * Return a success response.
     */
    public function success(): JsonResponse
    {
        return $this->apiSuccess(
            data: ['name' => 'John Doe'],
            message: 'User retrieved successfully',
        );
    }

    /**
     * Return a created response.
     */
    public function created(): JsonResponse
    {
        return $this->apiCreated(
            data: ['id' => 1, 'name' => 'John Doe'],
            message: 'User created successfully',
        );
    }

    /**
     * Return a not found response.
     */
    public function notFound(): JsonResponse
    {
        return $this->apiNotFound('User not found');
    }

    /**
     * Return an unauthorized response.
     */
    public function unauthorized(): JsonResponse
    {
        return $this->apiUnauthorized();
    }

    /**
     * Return a forbidden response.
     */
    public function forbidden(): JsonResponse
    {
        return $this->apiForbidden();
    }

    /**
     * Return a conflict response.
     */
    public function conflict(): JsonResponse
    {
        return $this->apiConflict('User already exists');
    }

    /**
     * Return a server error response.
     */
    public function serverError(): JsonResponse
    {
        return $this->apiServerError('Something went wrong');
    }
}
