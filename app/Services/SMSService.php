<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Mobishastra (mshastra.com) SMS — same JSON API call as KANISASOFT's
 * demo_backend SMSService, configured under services.mshastra.
 */
class SMSService
{
    public function isConfigured(): bool
    {
        return config('services.mshastra.user') && config('services.mshastra.password')
            && config('services.mshastra.sender') && config('services.mshastra.json_url');
    }

    /**
     * @return array{status: bool, response: string, error_type: ?string}
     */
    public function sendSMS(string $phone, string $message): array
    {
        $payload = [[
            'user' => config('services.mshastra.user'),
            'pwd' => config('services.mshastra.password'),
            'number' => $phone,
            'msg' => $message,
            'sender' => config('services.mshastra.sender'),
            'language' => 'English',
        ]];

        try {
            [$ok, $body] = $this->postJson(config('services.mshastra.json_url'), $payload);

            if (!$ok) {
                Log::error('SMS request failed: ' . $body);

                return ['status' => false, 'response' => $body, 'error_type' => stripos($body, 'Invalid Password') !== false ? 'Invalid Password' : 'Other'];
            }
            if (stripos($body, 'Invalid Password') !== false) {
                return ['status' => false, 'response' => $body, 'error_type' => 'Invalid Password'];
            }
            if (stripos($body, 'success') !== false || stripos($body, 'Sent') !== false) {
                return ['status' => true, 'response' => $body, 'error_type' => null];
            }

            return ['status' => false, 'response' => $body, 'error_type' => 'Other'];
        } catch (\Throwable $e) {
            Log::error('SMS sending error: ' . $e->getMessage());

            return ['status' => false, 'response' => $e->getMessage(), 'error_type' => 'Exception'];
        }
    }

    /**
     * POST JSON with PHP's cURL. (This project doesn't ship Guzzle, which
     * Laravel's Http client needs, so the demo's Http::post can't be used here.)
     *
     * @return array{0: bool, 1: string} 2xx?, response body
     */
    protected function postJson(string $url, array $payload): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($body === false) {
            throw new \RuntimeException('SMS gateway unreachable: ' . $error);
        }

        return [$code >= 200 && $code < 300, (string) $body];
    }

    /** One SMS is 160 characters. (Phone number formatting: App\Helpers\Phone.) */
    public static function partsFor(string $message): int
    {
        $length = mb_strlen($message);

        return $length === 0 ? 0 : (int) ceil($length / 160);
    }
}
