<?php

namespace App\Services\Chatbot;

use RuntimeException;

class ChatbotService
{
    public function __construct(
        private KnowledgeBaseService $knowledge,
        private GeminiClient $gemini,
    ) {}

    /**
     * @param  list<array{role?: mixed, content?: mixed}>  $history
     * @return array{success: bool, reply: string, code: string|null, status: int}
     */
    public function reply(string $message, array $history = []): array
    {
        $normalized = $this->normalize($message);

        if ($normalized === '') {
            return $this->ok(config('chatbot.messages.insufficient'), 'insufficient');
        }

        if ($this->looksLikeInjection($normalized)) {
            return $this->ok(config('chatbot.messages.out_of_scope'), 'out_of_scope');
        }

        try {
            $knowledge = $this->knowledge->context();
            $system = $this->systemPrompt().$this->knowledgeBlock($knowledge);
            $turns = $this->sanitizeHistory($history);
            $turns[] = ['role' => 'user', 'text' => "<PREGUNTA_DEL_USUARIO>\n".$normalized."\n</PREGUNTA_DEL_USUARIO>"];

            $answer = $this->gemini->generate($system, $turns);

            return $this->ok($this->postProcess($answer));
        } catch (RuntimeException $e) {
            return $this->failure($e->getMessage());
        }
    }

    private function normalize(string $message): string
    {
        $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message) ?? $message;
        $message = preg_replace("/\n{3,}/", "\n\n", $message) ?? $message;

        return trim($message);
    }

    private function looksLikeInjection(string $message): bool
    {
        $haystack = mb_strtolower($message);

        $needles = [
            'ignora tus instrucciones',
            'ignora las instrucciones',
            'olvida tus instrucciones',
            'olvida las instrucciones',
            'system prompt',
            'modo desarrollador',
            'developer mode',
            'actúa como',
            'actua como',
            'ignore your instructions',
            'ignore previous instructions',
        ];

        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array{role?: mixed, content?: mixed}>  $history
     * @return list<array{role: string, text: string}>
     */
    private function sanitizeHistory(array $history): array
    {
        $maxMessages = (int) config('chatbot.max_history_messages', 6);
        $maxChars = (int) config('chatbot.max_history_chars_per_message', 500);
        $clean = [];

        foreach ($history as $item) {
            if (! is_array($item)) {
                continue;
            }

            $role = $item['role'] ?? null;
            $content = $item['content'] ?? null;

            if (! in_array($role, ['user', 'assistant'], true) || ! is_string($content)) {
                continue;
            }

            $text = $this->normalize($content);

            if ($text === '') {
                continue;
            }

            $clean[] = [
                'role' => $role,
                'text' => mb_substr($text, 0, $maxChars),
            ];
        }

        if (count($clean) > $maxMessages) {
            $clean = array_slice($clean, -$maxMessages);
        }

        return $clean;
    }

    private function systemPrompt(): string
    {
        $path = (string) config('chatbot.system_prompt_path');
        $template = is_readable($path) ? (string) file_get_contents($path) : '';

        return strtr($template, [
            '{{mensaje_sin_informacion}}' => (string) config('chatbot.messages.insufficient'),
            '{{mensaje_fuera_de_dominio}}' => (string) config('chatbot.messages.out_of_scope'),
        ]);
    }

    private function knowledgeBlock(string $knowledge): string
    {
        return "\n\n<BASE_DE_CONOCIMIENTO>\n".$knowledge."\n</BASE_DE_CONOCIMIENTO>\n";
    }

    private function postProcess(string $answer): string
    {
        $max = (int) config('chatbot.max_output_chars', 1200);
        $answer = trim($answer);

        if ($answer === '') {
            return (string) config('chatbot.messages.insufficient');
        }

        if (preg_match('/AIza[0-9A-Za-z_-]{10,}/', $answer) === 1) {
            return (string) config('chatbot.messages.out_of_scope');
        }

        if (mb_strlen($answer) > $max) {
            return (string) config('chatbot.messages.too_long');
        }

        return $answer;
    }

    /**
     * @return array{success: bool, reply: string, code: string|null, status: int}
     */
    private function ok(string $reply, ?string $code = null): array
    {
        return [
            'success' => true,
            'reply' => $reply,
            'code' => $code,
            'status' => 200,
        ];
    }

    /**
     * @return array{success: bool, reply: string, code: string|null, status: int}
     */
    private function failure(string $code): array
    {
        $messages = [
            'gemini_not_configured' => config('chatbot.messages.unavailable'),
            'gemini_unavailable' => config('chatbot.messages.unavailable'),
            'gemini_invalid' => config('chatbot.messages.unavailable'),
            'gemini_incomplete' => config('chatbot.messages.too_long'),
            'gemini_blocked' => config('chatbot.messages.out_of_scope'),
            'gemini_timeout' => config('chatbot.messages.timeout'),
            'knowledge_unavailable' => config('chatbot.messages.unavailable'),
        ];

        $status = $code === 'gemini_blocked' ? 200 : 503;

        return [
            'success' => false,
            'reply' => $messages[$code] ?? config('chatbot.messages.unavailable'),
            'code' => $code,
            'status' => $status,
        ];
    }
}
