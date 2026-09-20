<?php

namespace App\Services;

use App\Exceptions\AiRequestException;
use Illuminate\Support\Facades\Http;

class AiClientService
{
    /** @return array{text: string, tokens: int|null} */
    public function generate(string $instructions, string $input, int $maxOutputTokens = 300): array
    {
        if (blank(config('ai.key'))) {
            throw new AiRequestException('AI service is not configured. Add AI_API_KEY to .env.');
        }

        try {
            $response = config('ai.provider') === 'gemini'
                ? $this->geminiRequest($instructions, $input, $maxOutputTokens)
                : $this->openAiRequest($instructions, $input, $maxOutputTokens);
        } catch (\Throwable $exception) {
            throw new AiRequestException('AI service timed out or could not be reached.', previous: $exception);
        }

        if ($response->failed()) {
            $status = $response->status();
            $providerCode = $response->json('error.code') ?? $response->json('error.status');
            $providerMessage = (string) ($response->json('error.message') ?? 'No provider error message was returned.');
            $message = match ($status) {
                401, 403 => 'AI authentication failed. Check AI_API_KEY.',
                429 => $providerCode === 'insufficient_quota'
                    ? 'OpenAI API quota is unavailable. Add API billing credits or raise the project budget.'
                    : 'OpenAI rate limit reached. Please try again shortly.',
                default => 'AI service request failed. Please try again later.',
            };
            // Contains HTTP/provider diagnostics only; the request and API key are never logged.
            throw new AiRequestException("$message [HTTP $status; provider: $providerCode; $providerMessage]");
        }

        $json = $response->json();
        if (config('ai.provider') === 'gemini') {
            $text = trim(collect($json['candidates'][0]['content']['parts'] ?? [])->pluck('text')->implode(''));
            if ($text === '') throw new AiRequestException('AI service returned no text.');
            return ['text' => $text, 'tokens' => $json['usageMetadata']['totalTokenCount'] ?? null];
        }

        $text = trim((string) ($json['output_text'] ?? ''));
        if ($text === '') {
            foreach ($json['output'] ?? [] as $output) foreach ($output['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text') $text .= $content['text'] ?? '';
            }
            $text = trim($text);
        }
        if ($text === '') throw new AiRequestException('AI service returned no text.');

        return ['text' => $text, 'tokens' => $json['usage']['total_tokens'] ?? null];
    }

    private function geminiRequest(string $instructions, string $input, int $maxOutputTokens)
    {
        return Http::baseUrl(config('ai.base_url'))
            ->withQueryParameters(['key' => config('ai.key')])
            ->acceptJson()->timeout(config('ai.timeout'))
            ->post('/models/'.config('ai.model').':generateContent', [
                'systemInstruction' => ['parts' => [['text' => $instructions]]],
                'contents' => [['role' => 'user', 'parts' => [['text' => $input]]]],
                'generationConfig' => [
                    'maxOutputTokens' => $maxOutputTokens,
                    'temperature' => 0.4,
                    // Support replies are simple: avoid spending the small response
                    // budget on extended reasoning before visible text is produced.
                    'thinkingConfig' => ['thinkingLevel' => 'minimal'],
                ],
            ]);
    }

    private function openAiRequest(string $instructions, string $input, int $maxOutputTokens)
    {
        return Http::baseUrl(config('ai.base_url'))->withToken(config('ai.key'))->acceptJson()->timeout(config('ai.timeout'))
            ->post('/responses', ['model' => config('ai.model'), 'instructions' => $instructions, 'input' => $input, 'max_output_tokens' => $maxOutputTokens, 'store' => false]);
    }
}
