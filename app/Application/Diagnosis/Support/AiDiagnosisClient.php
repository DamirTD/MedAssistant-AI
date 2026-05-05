<?php

namespace App\Application\Diagnosis\Support;

use App\Infrastructure\AI\GroqClient;
use Illuminate\Http\UploadedFile;

class AiDiagnosisClient
{
    public function __construct(
        private readonly GroqClient $groqClient
    ) {
    }

    public function request(AiProviderConfig $config, string $prompt, ?UploadedFile $image): AiDiagnosisResponse
    {
        $usedVision = false;
        $imageNote = null;
        $deepSeekAttempted = false;
        $aiProvider = 'groq';
        $aiModel = $config->groqModel;

        if ($image && is_string($config->groqVisionModel) && $config->groqVisionModel !== '') {
            $response = $this->groqClient->vision(
                apiKey: $config->groqApiKey,
                baseUrl: $config->groqBaseUrl,
                verifyOption: $config->groqVerifyOption,
                model: $config->groqVisionModel,
                prompt: $prompt,
                image: $image
            );

            if (! $response->failed()) {
                $usedVision = true;
                $aiProvider = 'groq_vision';
                $aiModel = $config->groqVisionModel;
            } else {
                $imageNote = 'Vision-модель недоступна для вашего ключа, выполнен текстовый fallback.';
                $response = $this->groqClient->text(
                    apiKey: $config->groqApiKey,
                    baseUrl: $config->groqBaseUrl,
                    verifyOption: $config->groqVerifyOption,
                    model: $config->groqModel,
                    prompt: $prompt
                );
                $aiProvider = 'groq';
                $aiModel = $config->groqModel;
            }
        } else {
            $response = $this->groqClient->text(
                apiKey: $config->groqApiKey,
                baseUrl: $config->groqBaseUrl,
                verifyOption: $config->groqVerifyOption,
                model: $config->groqModel,
                prompt: $prompt
            );

            if ($image) {
                $imageNote = 'Изображение получено, но vision-модель не настроена в .env.';
            }

            if ($response->failed() && is_string($config->deepSeekApiKey) && $config->deepSeekApiKey !== '') {
                $deepSeekAttempted = true;
                $response = $this->groqClient->deepSeekText(
                    apiKey: $config->deepSeekApiKey,
                    baseUrl: $config->deepSeekBaseUrl,
                    verifyOption: $config->deepSeekVerifyOption,
                    model: $config->deepSeekModel,
                    prompt: $prompt
                );
                $aiProvider = 'deepseek';
                $aiModel = $config->deepSeekModel;
            }
        }

        return new AiDiagnosisResponse(
            response: $response,
            usedVision: $usedVision,
            deepSeekAttempted: $deepSeekAttempted,
            imageNote: $imageNote,
            aiProvider: $aiProvider,
            aiModel: $aiModel
        );
    }
}
