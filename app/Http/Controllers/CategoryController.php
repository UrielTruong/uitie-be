<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Lấy danh sách các chủ đề (category) thịnh hành nhất
     * Sắp xếp theo số lượng bài viết giảm dần
     */
    public function getTrending(): JsonResponse
    {
        $categories = Category::select('categories.id', 'categories.category_name')
            ->leftJoin('posts', function($join) {
                $join->on('categories.id', '=', 'posts.category_id')
                     ->whereNull('posts.deleted_at'); // Bỏ qua các post đã bị xóa (soft delete)
            })
            ->selectRaw('COUNT(posts.id) as posts_count')
            ->groupBy('categories.id', 'categories.category_name')
            ->orderByDesc('posts_count')
            ->take(5) // Lấy Top 5 chủ đề
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $categories,
        ]);
    }
}
