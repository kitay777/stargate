<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class LineWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $events = $request->input('events', []);

        foreach ($events as $event) {


            if (!isset($event['source']['userId'])) {
                continue;
            }

            $lineUserId = $event['source']['userId'];
            $user = User::where('line_user_id', $lineUserId)->first();

            if (!$user) {
                continue;
            }

            if ($event['type'] === 'follow') {
                $user->update([
                    'is_line_friend' => true
                ]);
            }

            if ($event['type'] === 'unfollow') {
                $user->update([
                    'is_line_friend' => false
                ]);
            }
        }

        return response('OK', 200);
    }
}
