<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('message') && is_string($this->input('message'))) {
            $this->merge([
                'message' => trim($this->input('message')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxInput = (int) config('chatbot.max_input_chars', 500);
        $maxHistory = (int) config('chatbot.max_history_messages', 6);
        $maxHistoryChars = (int) config('chatbot.max_history_chars_per_message', 500);

        return [
            'message' => ['required', 'string', 'min:1', 'max:'.$maxInput],
            'history' => ['sometimes', 'array', 'max:'.$maxHistory],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:'.$maxHistoryChars],
            
            // NUEVA REGLA: Aceptamos el array de principios activos
            'principios_activos' => ['sometimes', 'array'],
            'principios_activos.*' => ['integer', 'min:1', 'max:12'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Escribe una pregunta para el asistente de SIVIA.',
            'message.min' => 'Escribe una pregunta para el asistente de SIVIA.',
            'message.max' => 'La pregunta es demasiado larga.',
            'history.max' => 'El historial enviado no es válido.',
            'history.*.role.in' => 'El historial enviado no es válido.',
            'history.*.content.max' => 'El historial enviado no es válido.',
            'principios_activos.array' => 'El formato de los principios no es válido.',
            'principios_activos.*.integer' => 'El principio debe ser un número.',
            'principios_activos.*.min' => 'El principio debe estar entre 1 y 12.',
            'principios_activos.*.max' => 'El principio debe estar entre 1 y 12.',
        ];
    }
}