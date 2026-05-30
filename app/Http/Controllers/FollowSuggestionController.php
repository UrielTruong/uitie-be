<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowSuggestionController extends Controller
{
    public function getSuggestedFollows(Request $request): JsonResponse
    {
        $currentUserId = $request->attributes->get('user_id');
        $currentUser = User::find($currentUserId);

        if (!$currentUser) {
            return response()->json(['status' => false, 'data' => []]);
        }

        // Chuẩn bị các giá trị của current user để đem đi so sánh
        $bindings = [
            $currentUser->faculty ?? '',
            $currentUser->class_name ?? '',
            $currentUser->academic_year ?? ''
        ];

        // Tính Match Score: 3 = Giống cả 3, 2 = Giống 2, 1 = Giống 1
        $users = User::select('users.*')
            ->selectRaw("
                ((CASE WHEN faculty = ? AND faculty != '' AND faculty IS NOT NULL THEN 1 ELSE 0 END) +
                (CASE WHEN class_name = ? AND class_name != '' AND class_name IS NOT NULL THEN 1 ELSE 0 END) +
                (CASE WHEN academic_year = ? AND academic_year != '' AND academic_year IS NOT NULL THEN 1 ELSE 0 END)) AS match_score
            ", $bindings)
            ->where('id', '!=', $currentUserId)
            ->where('status', User::STATUS_ACTIVE)
            ->whereNotIn('id', function ($query) use ($currentUserId) {
                $query->select('following_id')
                      ->from('follows')
                      ->where('follower_id', $currentUserId);
            })
            ->orderByDesc('match_score')
            ->inRandomOrder() // Chọn ngẫu nhiên giữa những người có cùng điểm số
            ->take(5)
            ->get();

        return response()->json(['status' => true, 'data' => $users]);
    }
}
