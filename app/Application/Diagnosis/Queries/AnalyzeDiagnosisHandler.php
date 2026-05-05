<?php

namespace App\Application\Diagnosis\Queries;

use App\Application\Diagnosis\Support\AiDiagnosisClient;
use App\Application\Diagnosis\Support\AiProviderConfig;
use App\Application\Diagnosis\Support\DiagnosisKnowledgeContext;
use App\Application\Diagnosis\Support\DiagnosisModelJsonParser;
use App\Application\Diagnosis\Support\PatientProfile;
use App\Application\Diagnosis\Support\DiagnosisPromptBuilder;
use App\Application\Diagnosis\Support\DiagnosisResponseMapper;
use App\Application\Diagnosis\Support\DiagnosisSourcesProcessor;
use App\Application\Diagnosis\Support\DiagnosisTriageAnalyzer;
use App\Infrastructure\AI\GroqClient;
use App\Infrastructure\Medical\MedicalSourcesProvider;
use App\Infrastructure\Medical\OwidInsightsProvider;
use RuntimeException;

class AnalyzeDiagnosisHandler
{
    private readonly DiagnosisTriageAnalyzer $triageAnalyzer;
    private readonly DiagnosisSourcesProcessor $sourcesProcessor;
    private readonly DiagnosisPromptBuilder $promptBuilder;
    private readonly AiDiagnosisClient $aiDiagnosisClient;
    private readonly DiagnosisModelJsonParser $jsonParser;

    public function __construct(
        GroqClient $groqClient,
        private readonly MedicalSourcesProvider $sourcesProvider,
        private readonly OwidInsightsProvider $owidInsightsProvider,
        private readonly DiagnosisResponseMapper $responseMapper,
        ?DiagnosisTriageAnalyzer $triageAnalyzer = null,
        ?DiagnosisSourcesProcessor $sourcesProcessor = null,
        ?DiagnosisPromptBuilder $promptBuilder = null,
        ?AiDiagnosisClient $aiDiagnosisClient = null,
        ?DiagnosisModelJsonParser $jsonParser = null
    ) {
        $this->triageAnalyzer = $triageAnalyzer ?? new DiagnosisTriageAnalyzer();
        $this->sourcesProcessor = $sourcesProcessor ?? new DiagnosisSourcesProcessor();
        $this->promptBuilder = $promptBuilder ?? new DiagnosisPromptBuilder();
        $this->aiDiagnosisClient = $aiDiagnosisClient ?? new AiDiagnosisClient($groqClient);
        $this->jsonParser = $jsonParser ?? new DiagnosisModelJsonParser();
    }

    public function __invoke(AnalyzeDiagnosisQuery $query): array
    {
        $config = AiProviderConfig::fromConfig();
        $profile = new PatientProfile($query->age, $query->gender);
        $domain = $this->triageAnalyzer->detectDomain($query->description);
        $triageSignals = $this->triageAnalyzer->evaluateSignals($query->description);

        $sources = $this->sourcesProvider->getSources($query->description, $config->groqVerifyOption, $domain);
        $sources = $this->sourcesProcessor->rerankWithinDomain($sources, $query->description, $domain);
        $sources = $this->sourcesProcessor->filterValidSources($sources);

        $owidInsights = $this->owidInsightsProvider->getInsights($query->description, $config->groqVerifyOption, $domain);
        $owidInsights = $this->sourcesProcessor->filterValidInsights($owidInsights);

        $knowledgeContext = new DiagnosisKnowledgeContext(
            domain: $domain,
            triageSignals: $triageSignals,
            sources: $sources,
            owidInsights: $owidInsights
        );

        $prompt = $this->promptBuilder->build(
            description: $query->description,
            knowledgeContext: $knowledgeContext,
            profile: $profile,
            hasImage: $query->image !== null
        );

        $aiResult = $this->aiDiagnosisClient->request($config, $prompt, $query->image);

        if ($aiResult->response->failed()) {
            $providerLabel = $aiResult->deepSeekAttempted ? 'Groq/DeepSeek' : 'Groq AI';
            throw new RuntimeException("Ошибка {$providerLabel}: ".$aiResult->response->body());
        }

        $rawText = data_get($aiResult->response->json(), 'choices.0.message.content', '{}');
        $decoded = $this->jsonParser->parse((string) $rawText);

        if (! is_array($decoded)) {
            throw new RuntimeException('Не удалось разобрать ответ модели.');
        }

        return $this->responseMapper->map(
            decoded: $decoded,
            knowledgeContext: $knowledgeContext,
            profile: $profile,
            hasImage: $query->image !== null,
            usedVision: $aiResult->usedVision,
            imageNote: $aiResult->imageNote,
            aiProvider: $aiResult->aiProvider,
            aiModel: $aiResult->aiModel
        );
    }
}

