<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'publicKey' => config('vapid.public_key'),
                'privateKey' => config('vapid.private_key'),
                'subject' => config('vapid.subject'),
            ],
        ]);
    }

    public function send(User $user, string $title, string $body, array $data = []): void
    {
        $subscriptions = $user->pushSubscriptions;

        if ($subscriptions->isEmpty()) {
            Log::info("Push: No subscriptions for user {$user->id} ({$user->email})");
            return;
        }

        Log::info("Push: Sending to user {$user->id} ({$user->email}) — {$subscriptions->count()} subscription(s)");

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => '/favicon.ico',
            'badge' => '/favicon.ico',
            'data' => $data,
            'tag' => $data['tag'] ?? null,
            'renotify' => true,
        ]);

        foreach ($subscriptions as $sub) {
            $this->webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'authToken' => $sub->auth_token,
                    'publicKey' => $sub->public_key,
                ]),
                $payload
            );
        }

        $reports = $this->webPush->flush();

        $successCount = 0;
        $failCount = 0;
        foreach ($reports as $report) {
            if ($report->isSuccess()) {
                $successCount++;
                continue;
            }

            $failCount++;
            Log::warning("Push: Failed for user {$user->id} — endpoint: {$report->getEndpoint()} — reason: {$report->getReason()}");

            $subscription = $subscriptions->firstWhere('endpoint', $report->getEndpoint());
            if ($subscription) {
                $subscription->delete();
                Log::info("Push: Deleted expired subscription for user {$user->id}");
            }
        }

        Log::info("Push: Completed for user {$user->id} — {$successCount} sent, {$failCount} failed");
    }
}
