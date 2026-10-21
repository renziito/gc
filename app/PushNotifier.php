<?php
declare(strict_types=1);

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

final class PushNotifier
{
    private WebPush $webPush;

    public function __construct(private PDO $pdo, array $config)
    {
        $push = $config['push'] ?? [];
        $public = (string)($push['public_key'] ?? '');
        $private = (string)($push['private_key'] ?? '');
        $subject = (string)($push['subject'] ?? '');
        if ($public === '' || $private === '' || $subject === '') {
            throw new RuntimeException('Web Push VAPID keys are not configured.');
        }
        $this->webPush = new WebPush(['VAPID' => [
            'subject' => $subject,
            'publicKey' => $public,
            'privateKey' => $private,
        ]]);
    }

    public function send(string $title, string $body, string $url = '/', string $topic = 'general'): array
    {
        $q = $this->pdo->query('SELECT endpoint_hash,subscription_json,preferences_json FROM push_subscriptions');
        $sent = 0; $expired = [];
        $payload = json_encode([
            'title' => mb_substr($title, 0, 120),
            'body' => mb_substr($body, 0, 500),
            'url' => $url,
            'tag' => 'galactic-command-' . substr(hash('sha256', $title . $body), 0, 16),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        foreach ($q->fetchAll() as $row) {
            $preferences = json_decode((string)($row['preferences_json'] ?? ''), true);
            if ($topic !== 'general' && is_array($preferences) && array_key_exists($topic, $preferences) && !$preferences[$topic]) continue;
            $data = json_decode((string)$row['subscription_json'], true);
            if (!is_array($data) || empty($data['endpoint']) || empty($data['keys']['p256dh']) || empty($data['keys']['auth'])) {
                $expired[] = (string)$row['endpoint_hash'];
                continue;
            }
            $this->webPush->queueNotification(Subscription::create($data), (string)$payload, ['TTL' => 300]);
        }
        foreach ($this->webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
            } elseif ($report->isSubscriptionExpired()) {
                $expired[] = hash('sha256', (string)$report->getEndpoint());
            }
        }
        if ($expired) {
            $del = $this->pdo->prepare('DELETE FROM push_subscriptions WHERE endpoint_hash=?');
            foreach (array_unique($expired) as $hash) $del->execute([$hash]);
        }
        return ['sent' => $sent, 'removed_expired' => count(array_unique($expired))];
    }
}
