<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;

class KnowledgeBaseService
{
    /**
     * @var list<string>
     */
    private array $files = [
        'general.json',
        'modulos.json',
        'pedidos_inteligentes.json',
        'beneficios.json',
        'faq.json',
        'limites.json',
    ];

    public function context(): string
    {
        $ttl = (int) config('chatbot.cache_ttl_seconds', 3600);

        return Cache::remember('sivia.chatbot.knowledge', $ttl, function () {
            return $this->buildContext();
        });
    }

    private function buildContext(): string
    {
        $basePath = (string) config('chatbot.knowledge_path');
        $sections = [];

        foreach ($this->files as $file) {
            $path = $basePath.DIRECTORY_SEPARATOR.$file;

            if (! is_readable($path)) {
                Log::error('Chatbot knowledge file missing or unreadable.', ['file' => $file]);
                throw new RuntimeException('knowledge_unavailable');
            }

            $raw = file_get_contents($path);

            if ($raw === false) {
                Log::error('Chatbot knowledge file could not be read.', ['file' => $file]);
                throw new RuntimeException('knowledge_unavailable');
            }

            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                Log::error('Chatbot knowledge file has invalid JSON.', ['file' => $file]);
                throw new RuntimeException('knowledge_unavailable', 0, $e);
            }

            if (! is_array($decoded)) {
                Log::error('Chatbot knowledge file is not a JSON object.', ['file' => $file]);
                throw new RuntimeException('knowledge_unavailable');
            }

            $label = pathinfo($file, PATHINFO_FILENAME);
            $sections[] = strtoupper($label)."\n".$this->toPlainText($decoded);
        }

        return trim(implode("\n\n", $sections));
    }

    private function toPlainText(mixed $data, int $depth = 0): string
    {
        if (is_string($data) || is_numeric($data) || is_bool($data)) {
            return (string) $data;
        }

        if (! is_array($data)) {
            return '';
        }

        $lines = [];
        $indent = str_repeat('  ', $depth);

        foreach ($data as $key => $value) {
            $prefix = is_int($key) ? $indent.'- ' : $indent.$key.': ';

            if (is_array($value)) {
                $lines[] = rtrim($prefix);
                $nested = $this->toPlainText($value, $depth + 1);
                if ($nested !== '') {
                    $lines[] = $nested;
                }
            } else {
                $lines[] = $prefix.(string) $value;
            }
        }

        return implode("\n", $lines);
    }
}
