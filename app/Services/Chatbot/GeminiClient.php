<?php

namespace App\Services\Chatbot;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiClient
{
    /**
     * @param  list<array{role: string, text: string}>  $turns
     */
    public function generate(string $systemInstruction, array $turns): string
    {
        $apiKey = (string) config('chatbot.gemini.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('gemini_not_configured');
        }

        $model = (string) config('chatbot.gemini.model');
        $url = sprintf((string) config('chatbot.gemini.endpoint'), $model);
        $timeout = (int) config('chatbot.gemini.timeout', 15);

        $contents = [];
        foreach ($turns as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $turn['text']]],
            ];
        }

        try {
            $response = Http::timeout($timeout)
                ->retry([500, 1000], function ($exception) {
                    return $exception instanceof RequestException
                        && in_array($exception->response->status(), [408, 429, 500, 502, 503, 504], true);
                }, throw: false)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->asJson()
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemInstruction]],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => (float) config('chatbot.gemini.temperature', 0.2),
                        'maxOutputTokens' => (int) config('chatbot.gemini.max_output_tokens', 512),
                        'thinkingConfig' => [
                            'thinkingLevel' => (string) config('chatbot.gemini.thinking_level', 'minimal'),
                        ],
                    ],
                ]);
        } catch (ConnectionException $e) {
            $detail = strtolower($e->getMessage());
            $code = str_contains($detail, 'timed out') ? 'gemini_timeout' : 'gemini_unavailable';

            Log::warning('Gemini connection failed.', [
                'code' => $code,
                'exception' => $e->getMessage(),
            ]);

            throw new RuntimeException($code, 0, $e);
        }

        if ($response->failed()) {
            Log::warning('Gemini request failed.', ['status' => $response->status()]);
            throw new RuntimeException('gemini_unavailable');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('gemini_invalid');
        }

        if (! empty($payload['promptFeedback']['blockReason'])) {
            throw new RuntimeException('gemini_blocked');
        }

        $candidate = $payload['candidates'][0] ?? null;

        if (! is_array($candidate)) {
            throw new RuntimeException('gemini_invalid');
        }

        if (($candidate['finishReason'] ?? null) === 'MAX_TOKENS') {
            Log::warning('Gemini response reached its output token limit.');
            throw new RuntimeException('gemini_incomplete');
        }

        $parts = $candidate['content']['parts'] ?? [];
        $textParts = [];

        foreach ($parts as $part) {
            if (is_array($part) && is_string($part['text'] ?? null) && empty($part['thought'])) {
                $textParts[] = $part['text'];
            }
        }

        $text = implode("\n", $textParts);

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('gemini_invalid');
        }

        return trim($text);
    }
}
