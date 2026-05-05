<?php

namespace App\Application\Diagnosis\Support;

use RuntimeException;

class AiProviderConfig
{
    public function __construct(
        public readonly string $groqApiKey,
        public readonly string $groqModel,
        public readonly ?string $groqVisionModel,
        public readonly string $groqBaseUrl,
        public readonly bool|string $groqVerifyOption,
        public readonly ?string $deepSeekApiKey,
        public readonly string $deepSeekModel,
        public readonly string $deepSeekBaseUrl,
        public readonly bool|string $deepSeekVerifyOption
    ) {
    }

    public static function fromConfig(): self
    {
        $groqApiKey = (string) config('services.groq_ai.api_key', '');
        if ($groqApiKey === '') {
            throw new RuntimeException('GROQ_AI не задан в .env');
        }

        return new self(
            groqApiKey: $groqApiKey,
            groqModel: (string) config('services.groq_ai.model', 'openai/gpt-oss-120b'),
            groqVisionModel: self::nullableString(config('services.groq_ai.vision_model')),
            groqBaseUrl: rtrim((string) config('services.groq_ai.base_url', 'https://api.groq.com/openai/v1'), '/'),
            groqVerifyOption: self::resolveVerifyOption(
                config('services.groq_ai.verify_ssl', true),
                config('services.groq_ai.ca_bundle')
            ),
            deepSeekApiKey: self::nullableString(config('services.deepseek.api_key')),
            deepSeekModel: (string) config('services.deepseek.model', 'deepseek-chat'),
            deepSeekBaseUrl: rtrim((string) config('services.deepseek.base_url', 'https://api.deepseek.com/v1'), '/'),
            deepSeekVerifyOption: self::resolveVerifyOption(
                config('services.deepseek.verify_ssl', true),
                config('services.deepseek.ca_bundle')
            )
        );
    }

    private static function resolveVerifyOption(mixed $verifySsl, mixed $caBundle): bool|string
    {
        if (is_string($caBundle) && trim($caBundle) !== '') {
            return trim($caBundle);
        }

        return filter_var($verifySsl, FILTER_VALIDATE_BOOL);
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
