<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class FcmService
{
    protected $projectId;

    public function __construct()
    {
        // Extract project ID from the JSON file or env
        $this->projectId = env('FCM_PROJECT_ID');
    }

    /**
     * Get OAuth2 access token for FCM v1 API.
     */
    private function getAccessToken()
    {
        $credentialsFilePath = storage_path('app/firebase-service-account.json');
        
        if (!file_exists($credentialsFilePath)) {
            Log::error('Firebase service account credentials file not found at ' . $credentialsFilePath);
            return null;
        }

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountCredentials($scopes, $credentialsFilePath);
        $token = $credentials->fetchAuthToken(HttpHandlerFactory::build());

        return $token['access_token'] ?? null;
    }

    /**
     * Send notification to a specific token.
     */
    public function sendToToken($token, $title, $body, $data = [])
    {
        return $this->send([
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Send notification to a specific user (to all their registered devices).
     */
    public function sendToUser($user, $title, $body, $data = [])
    {
        $tokens = $user->devices()->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            return false;
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send notification to multiple users/tokens.
     * FCM HTTP v1 doesn't support 'registration_ids' directly in one message block.
     * We have to send a message per token or use topics.
     * For simplicity, this loops over tokens.
     */
    public function sendToMultiple(array $tokens, $title, $body, $data = [])
    {
        $success = 0;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $success++;
            }
        }
        return $success === count($tokens);
    }

    /**
     * Send notification to a topic.
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        return $this->send([
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Core method to send message via FCM HTTP v1 API.
     */
    private function send(array $message)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('FCM Access Token generation failed.');
            return false;
        }

        if (!$this->projectId) {
            Log::error('FCM Project ID is not set in environment (FCM_PROJECT_ID).');
            return false;
        }

        // FCM v1 requires 'data' to be a map of strings. 
        // If it's an empty array, we should remove it to avoid API errors.
        if (isset($message['data'])) {
            if (empty($message['data'])) {
                unset($message['data']);
            } else {
                $message['data'] = array_map('strval', (array)$message['data']);
            }
        }

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'message' => $message,
        ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('FCM Send Error: ' . $response->body());
        return false;
    }
}
