<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function getFeed(int $perPage);

    public function findById(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);

    public function adminSearch(array $filters, int $perPage);

    public function getAllForExport(array $filters = []): Collection;

    public function countPostsByCategory();

    public function countPosts();

    //add thêm vì bên PostController có gọi $this->postRepository->paginate($perPage)
    public function paginate(int $perPage);
}
