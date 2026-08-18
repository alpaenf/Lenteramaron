<?php

namespace App\Http\Controllers;

use App\Services\GroqService;
use App\Services\Search\HybridSearchService;
use App\Services\Search\ResearchPathGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LiteraSearchController extends Controller
{
    protected HybridSearchService $searchService;
    protected GroqService $groqService;
    protected ResearchPathGenerator $pathGenerator;

    public function __construct(
        HybridSearchService $searchService,
        GroqService $groqService,
        ResearchPathGenerator $pathGenerator
    ) {
        $this->searchService = $searchService;
        $this->groqService = $groqService;
        $this->pathGenerator = $pathGenerator;
    }

    /**
     * Render the main LITERA Search & Discovery UI.
     */
    public function index(Request $request): Response
    {
        $query = $request->query('q', '');
        $sourceType = $request->query('source_type', '');
        $yearRange = $request->query('year_range', '5_years');

        $results = [];
        if (!empty($query)) {
            $results = $this->searchService->search($query, [
                'source_type' => $sourceType,
                'year_range'  => $yearRange,
            ]);
        }

        return Inertia::render('Litera/Search', [
            'initialQuery'      => $query,
            'initialSourceType' => $sourceType,
            'initialYearRange'  => $yearRange,
            'results'           => $results,
        ]);
    }

    /**
     * Async Search API.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'           => 'required|string|min:2|max:1000',
            'source_type' => 'nullable|string|in:local,external',
            'year_range'  => 'nullable|string|in:all,3_years,5_years,10_years',
        ]);

        $results = $this->searchService->search($request->input('q'), [
            'source_type' => $request->input('source_type', ''),
            'year_range'  => $request->input('year_range', '5_years'),
        ]);

        return response()->json($results);
    }

    /**
     * Async Explain Relevance Endpoint.
     */
    public function explain(Request $request): JsonResponse
    {
        $request->validate([
            'q'    => 'required|string',
            'item' => 'required|array',
        ]);

        $explanation = $this->groqService->explainRelevance(
            $request->input('q'),
            $request->input('item')
        );

        return response()->json([
            'explanation' => $explanation,
        ]);
    }

    /**
     * Async Generate Research Path Endpoint.
     */
    public function generatePath(Request $request): JsonResponse
    {
        $request->validate([
            'q'       => 'required|string',
            'sources' => 'required|array',
        ]);

        $steps = $this->pathGenerator->generate(
            $request->input('q'),
            $request->input('sources')
        );

        return response()->json([
            'steps' => $steps,
        ]);
    }

    /**
     * Async Deep AI Content Analysis Endpoint.
     */
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'q'    => 'required|string',
            'item' => 'required|array',
        ]);

        $analysis = $this->groqService->analyzeContent(
            $request->input('q'),
            $request->input('item')
        );

        return response()->json([
            'analysis' => $analysis,
        ]);
    }
}
