<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model,
    ) {}

    /**
     * Retrieve all users.
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Retrieve a paginated list of users.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    /**
     * Find a user by their primary key.
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing user by ID.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): ?User
    {
        $user = $this->findById($id);

        if (! $user) {
            return null;
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Delete a user by ID.
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);

        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }

    /**
     * Admin search users.
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->adminSearch($filters, $perPage);
    }

    public function adminSearch(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Tìm theo tên hoặc email
        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', "%{$filters['keyword']}%")
                    ->orWhere('email', 'like', "%{$filters['keyword']}%");
            });
        }
        if (!empty($filters['mssv'])) {
            $query->where('mssv', 'like', "%{$filters['mssv']}%");
        }
        if (!empty($filters['class_name'])) {
            $query->where('class_name', 'like', "%{$filters['class_name']}%");
        }
        if (!empty($filters['faculty'])) {
            $query->where('faculty', 'like', "%{$filters['faculty']}%");
        }


        // Admin-only filters
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);       // khớp chính xác
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);   // khớp chính xác
        }

        return $query->latest()->paginate($perPage);
    }
}
