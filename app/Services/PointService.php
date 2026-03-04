<?php
namespace App\Services;

use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class PointService
{
    public function add(User $user, int $amount, string $type, array $extra = [])
    {
        return DB::transaction(function () use ($user, $amount, $type, $extra) {

            $user->increment('points', $amount);

            return PointTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $extra['description'] ?? null,
                'related_user_id' => $extra['related_user_id'] ?? null,
                'room_id' => $extra['room_id'] ?? null,
            ]);
        });
    }

    public function deduct(User $user, int $amount, string $type, array $extra = [])
    {
        if ($user->points < $amount) {
            throw new \Exception('ポイント不足');
        }

        return DB::transaction(function () use ($user, $amount, $type, $extra) {

            $user->decrement('points', $amount);

            return PointTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => $type,
                'description' => $extra['description'] ?? null,
                'related_user_id' => $extra['related_user_id'] ?? null,
                'room_id' => $extra['room_id'] ?? null,
            ]);
        });
    }
}
