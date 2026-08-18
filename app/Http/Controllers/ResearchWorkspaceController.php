<?php

namespace App\Http\Controllers;

use App\Models\ExternalSource;
use App\Models\ResearchTopic;
use App\Models\SavedSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ResearchWorkspaceController extends Controller
{
    /**
     * Render Research Workspace Vue page.
     */
    public function index(): Response
    {
        $userId = Auth::id();

        $topics = ResearchTopic::where('user_id', $userId)
            ->withCount('savedSources')
            ->orderBy('created_at', 'desc')
            ->get();

        $savedSources = SavedSource::where('user_id', $userId)
            ->with(['book.category', 'externalSource', 'researchTopic'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Litera/Workspace', [
            'topics'       => $topics,
            'savedSources' => $savedSources,
        ]);
    }

    /**
     * Store a saved source (bookmark).
     */
    public function storeSavedSource(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_type'        => 'required|in:local,external',
            'book_id'            => 'nullable|required_if:source_type,local|exists:books,id',
            'external_source_id' => 'nullable|required_if:source_type,external|exists:external_sources,id',
            'research_topic_id'  => 'nullable|exists:research_topics,id',
            'notes'              => 'nullable|string|max:1000',
            'status'             => 'nullable|in:unread,reading,completed',
            // Or if raw external item is passed:
            'raw_external'       => 'nullable|array',
        ]);

        $userId = Auth::id();

        // If raw external source data is passed, persist external source first if needed
        $externalSourceId = $validated['external_source_id'] ?? null;
        if ($validated['source_type'] === 'external' && empty($externalSourceId) && !empty($request->input('raw_external'))) {
            $raw = $request->input('raw_external');
            $ext = ExternalSource::updateOrCreate(
                [
                    'external_id'     => $raw['external_id'] ?? uniqid('ext_'),
                    'source_provider' => $raw['source_provider'] ?? 'openalex',
                ],
                [
                    'title'                => $raw['title'] ?? 'Tanpa Judul',
                    'authors'              => $raw['authors'] ?? [],
                    'publication_year'     => $raw['publication_year'] ?? null,
                    'publisher_or_journal' => $raw['publisher_or_journal'] ?? null,
                    'doi'                  => $raw['doi'] ?? null,
                    'url'                  => $raw['url'] ?? null,
                    'pdf_url'              => $raw['pdf_url'] ?? null,
                    'abstract'             => $raw['abstract'] ?? null,
                    'citation_count'       => $raw['citation_count'] ?? 0,
                    'open_access'          => $raw['open_access'] ?? false,
                ]
            );
            $externalSourceId = $ext->id;
        }

        // Avoid duplicate save
        $existing = SavedSource::where('user_id', $userId)
            ->where('source_type', $validated['source_type'])
            ->where(function ($q) use ($validated, $externalSourceId) {
                if ($validated['source_type'] === 'local') {
                    $q->where('book_id', $validated['book_id']);
                } else {
                    $q->where('external_source_id', $externalSourceId);
                }
            })->first();

        if ($existing) {
            return redirect()->back()->with('warning', 'Sumber ini sudah tersimpan di Research Workspace Anda.');
        }

        SavedSource::create([
            'user_id'            => $userId,
            'research_topic_id'  => $validated['research_topic_id'] ?? null,
            'source_type'        => $validated['source_type'],
            'book_id'            => $validated['source_type'] === 'local' ? $validated['book_id'] : null,
            'external_source_id' => $validated['source_type'] === 'external' ? $externalSourceId : null,
            'notes'              => $validated['notes'] ?? null,
            'status'             => $validated['status'] ?? 'unread',
        ]);

        return redirect()->back()->with('success', 'Sumber berhasil disimpan ke Research Workspace.');
    }

    /**
     * Update saved source notes / topic / status.
     */
    public function updateSavedSource(Request $request, SavedSource $savedSource): RedirectResponse
    {
        if ($savedSource->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'research_topic_id' => 'nullable|exists:research_topics,id',
            'notes'             => 'nullable|string|max:1000',
            'status'            => 'required|in:unread,reading,completed',
        ]);

        $savedSource->update($validated);

        return redirect()->back()->with('success', 'Status sumber berhasil diperbarui.');
    }

    /**
     * Delete saved source from workspace.
     */
    public function deleteSavedSource(SavedSource $savedSource): RedirectResponse
    {
        if ($savedSource->user_id !== Auth::id()) {
            abort(403);
        }

        $savedSource->delete();

        return redirect()->back()->with('success', 'Sumber berhasil dihapus dari workspace.');
    }

    /**
     * Store new research topic.
     */
    public function storeTopic(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        ResearchTopic::create([
            'user_id'     => Auth::id(),
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Topik penelitian baru berhasil dibuat.');
    }

    /**
     * Delete research topic.
     */
    public function deleteTopic(ResearchTopic $topic): RedirectResponse
    {
        if ($topic->user_id !== Auth::id()) {
            abort(403);
        }

        $topic->delete();

        return redirect()->back()->with('success', 'Topik penelitian berhasil dihapus.');
    }
}
