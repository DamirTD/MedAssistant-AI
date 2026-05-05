<?php

namespace App\Application\Diagnosis\Support;

class DiagnosisModelJsonParser
{
    public function parse(string $rawText): ?array
    {
        $decoded = json_decode($rawText, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($rawText, '{');
        $end = strrpos($rawText, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $jsonSlice = substr($rawText, $start, $end - $start + 1);
        $decoded = json_decode($jsonSlice, true);

        return is_array($decoded) ? $decoded : null;
    }
}
