<?php

namespace App\Services;

use GuzzleHttp\Client;

class ChatGptService
{
    public function generateText($messageArrs, $needSkipChars = null)
    {
        $apiKey = env('GPT_API_KEY');
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer ${apiKey}",
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => $messageArrs
            ],
        ]);

        $result = json_decode($response->getBody(), true);
        $content = $result['choices'][0]['message']['content'];
        if (!empty($needSkipChars)) {
            foreach($needSkipChars as $needSkipChar) {
                $content = str_replace($needSkipChar, "", $content);
            }
        }
        return $content;
    }
}
