<?php

namespace App\Services\Search;

use App\Services\GroqService;

class ResearchPathGenerator
{
    protected GroqService $groqService;

    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }

    /**
     * Generate 5-stage Research Path for a user search query and list of sources.
     */
    public function generate(string $query, array $sources): array
    {
        return $this->groqService->generateResearchPath($query, $sources);
    }
}
