<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class LineMessageService
{
    public function push($lineUserId, $text)
    {
        Http::withToken(config('services.line.channel_access_token'))
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $text,
                    ]
                ]
            ]);
    }
}
