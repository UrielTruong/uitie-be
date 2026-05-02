<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Retrieve all users.
     */
    public function all(): Collection;

    /**
     * Retrieve a paginated list of users.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a user by their primary key.
     */
    public function findById(int $id): ?User;

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User;

    /**
     * Update an existing user by ID.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): ?User;

    /**
     * Delete a user by ID.
     */
    public function delete(int $id);

    public function search(array $filters, int $perPage): LengthAwarePaginator;

    public function adminSearch(array $filters, int $perPage): LengthAwarePaginator;

    public function getAllForExport(array $filters = []): Collection;

    public function countUsers();
}
