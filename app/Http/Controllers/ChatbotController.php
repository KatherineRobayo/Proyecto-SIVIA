<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatbotMessageRequest;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    public function message(ChatbotMessageRequest $request, ChatbotService $chatbot): JsonResponse
    {
        if (! config('chatbot.enabled')) {
            return response()->json([
                'success' => false,
                'reply' => config('chatbot.messages.disabled'),
                'code' => 'disabled',
            ], 503);
        }

        // 1. Recibimos el historial limpio (sin textos ocultos para no confundir a Gemini)
        $history = $request->validated('history') ?? [];

        // 2. Llamamos a Gemini directamente
        $result = $chatbot->reply(
            $request->validated('message'),
            $history,
        );

        $reply = $result['reply'];
        $principioId = null;

        // 3. Extraer la etiqueta [PRINCIPIO: X] para los puntitos del frontend
        if (preg_match('/\[PRINCIPIO:\s*(\d+)\]/i', $reply, $matches)) {
            $principioId = (int) $matches[1];
            $reply = trim(preg_replace('/\[PRINCIPIO:\s*\d+\]/i', '', $reply));
        }

        return response()->json([
            'success' => $result['success'],
            'reply' => $reply,
            'code' => $result['code'],
            'principio' => $principioId,
        ], $result['status']);
    }
}