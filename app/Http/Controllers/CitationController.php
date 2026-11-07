<?php

namespace App\Http\Controllers;

use App\Models\Citation;
use App\Models\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CitationController extends Controller
{
    public function index(ResearchProject $project): JsonResponse
    {
        $citations = $project->citations()->with('addedBy:id,name')->latest()->get();

        return response()->json(['data' => $citations]);
    }

    public function export(Request $request, ResearchProject $project): JsonResponse|Response
    {
        $citations = $project->citations()->orderBy('id')->get();

        $bibtexEntries = $citations->map(function (Citation $c): string {
            $key = $c->key ?: 'ref'.$c->id;
            $fields = [];

            if ($c->authors) {
                $fields[] = '  author = {'.addcslashes($c->authors, '{}').'}';
            }
            if ($c->title) {
                $fields[] = '  title = {'.addcslashes($c->title, '{}').'}';
            }
            if ($c->source) {
                $fields[] = '  journal = {'.addcslashes($c->source, '{}').'}';
            }
            if ($c->year) {
                $fields[] = '  year = {'.$c->year.'}';
            }
            if ($c->doi) {
                $fields[] = '  doi = {'.$c->doi.'}';
            }
            if ($c->url) {
                $fields[] = '  url = {'.$c->url.'}';
            }

            return "@article{{$key},\n".implode(",\n", $fields)."\n}";
        })->implode("\n\n");

        if ($request->query('download') || $request->query('format') === 'bibtex_file') {
            $filename = Str::slug($project->title).'-references.bib';

            return response($bibtexEntries, 200, [
                'Content-Type' => 'application/x-bibtex; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return response()->json([
            'count' => $citations->count(),
            'bibtex' => $bibtexEntries,
            'citations' => $citations,
        ]);
    }

    public function store(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'authors' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'string', 'max:255'],
            'doi' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'key' => ['nullable', 'string', 'max:50'],
        ]);

        $count = $project->citations()->count() + 1;
        $authorFirst = explode(',', $validated['authors'])[0] ?? 'Source';
        $authorLastName = trim(explode(' ', trim($authorFirst))[0] ?? 'Ref');
        $year = $validated['year'] ?: date('Y');

        $key = ! empty($validated['key'])
            ? Str::slug($validated['key'])
            : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $authorLastName).$year);

        $inTextTag = "[{$count}]";

        $citation = $project->citations()->create([
            ...$validated,
            'key' => $key,
            'year' => $year,
            'in_text_tag' => $inTextTag,
            'added_by_id' => $request->user()?->id,
        ])->load('addedBy:id,name');

        return response()->json(['data' => $citation], 201);
    }

    /**
     * Export citations in RIS format for Zotero, Mendeley, and EndNote.
     */
    public function exportRis(Request $request, ResearchProject $project): Response
    {
        $citations = $project->citations()->orderBy('id')->get();

        $risLines = [];
        foreach ($citations as $c) {
            $risLines[] = 'TY  - JOUR';
            $risLines[] = 'ID  - '.($c->key ?: 'ref'.$c->id);
            $risLines[] = 'TI  - '.($c->title ?: 'Untitled Document');
            if ($c->authors) {
                foreach (explode(',', $c->authors) as $author) {
                    $trimmed = trim($author);
                    if ($trimmed) {
                        $risLines[] = 'AU  - '.$trimmed;
                    }
                }
            }
            if ($c->source) {
                $risLines[] = 'JO  - '.$c->source;
            }
            if ($c->year) {
                $risLines[] = 'PY  - '.$c->year;
            }
            if ($c->doi) {
                $risLines[] = 'DO  - '.$c->doi;
            }
            if ($c->url) {
                $risLines[] = 'UR  - '.$c->url;
            }
            $risLines[] = 'ER  - ';
            $risLines[] = '';
        }

        $content = implode("\n", $risLines);
        $filename = Str::slug($project->title).'-references.ris';

        return response($content, 200, [
            'Content-Type' => 'application/x-research-info-systems; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Batch import citations from raw BibTeX text or file upload.
     */
    public function importBibtex(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'bibtex' => ['nullable', 'string', 'max:100000'],
            'file' => ['nullable', 'file', 'max:2048'],
        ]);

        $rawText = $validated['bibtex'] ?? '';
        if ($request->hasFile('file')) {
            $rawText = file_get_contents($request->file('file')->getRealPath()) ?: '';
        }

        if (trim($rawText) === '') {
            return response()->json(['message' => 'Please provide valid BibTeX text or upload a .bib file.'], 422);
        }

        preg_match_all('/@([a-zA-Z]+)\s*\{\s*([^,\s]+)\s*,([^@]+)\}/s', $rawText, $entries, PREG_SET_ORDER);

        $imported = 0;
        $currentCount = $project->citations()->count();

        foreach ($entries as $entry) {
            $key = trim($entry[2]);
            $body = $entry[3];

            $extractField = function (string $name) use ($body): string {
                if (preg_match('/'.$name.'\s*=\s*[\{"]([^"\}]+)[\}"]/i', $body, $match)) {
                    return trim($match[1]);
                }
                if (preg_match('/'.$name.'\s*=\s*([0-9a-zA-Z]+)/i', $body, $match)) {
                    return trim($match[1]);
                }

                return '';
            };

            $title = $extractField('title') ?: $key;
            $authors = $extractField('author') ?: 'Unknown Author';
            $year = $extractField('year') ?: date('Y');
            $journal = $extractField('journal') ?: ($extractField('booktitle') ?: 'Academic Reference');
            $doi = $extractField('doi');
            $url = $extractField('url');

            $currentCount++;
            $project->citations()->create([
                'key' => Str::slug($key) ?: 'ref'.$currentCount,
                'title' => Str::limit($title, 250),
                'authors' => Str::limit($authors, 250),
                'year' => Str::limit($year, 20),
                'source' => Str::limit($journal, 250),
                'doi' => Str::limit($doi, 250),
                'url' => Str::limit($url, 490),
                'in_text_tag' => "[{$currentCount}]",
                'added_by_id' => $request->user()?->id,
            ]);

            $imported++;
        }

        return response()->json([
            'message' => "Successfully imported {$imported} citation".($imported === 1 ? '' : 's').'.',
            'imported_count' => $imported,
            'total_citations' => $project->citations()->count(),
        ]);
    }

    /**
     * Auto-lookup citation metadata by DOI via Crossref.
     */
    public function lookupDoi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doi' => ['required', 'string', 'max:255'],
        ]);

        $doi = trim($validated['doi']);
        $doi = preg_replace('/^https?:\/\/(dx\.)?doi\.org\//i', '', $doi);
        $doi = preg_replace('/^doi:\s*/i', '', $doi);

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'User-Agent' => 'Co-Auth-Studio/1.0 (https://co-auth-research.azurewebsites.net; mailto:research@co-auth.studio)',
                ])
                ->get('https://api.crossref.org/works/'.urlencode($doi));

            if ($response->successful()) {
                $item = $response->json('message');

                $title = data_get($item, 'title.0', 'Untitled Work');
                $authorsList = [];
                foreach (data_get($item, 'author', []) as $author) {
                    $family = $author['family'] ?? '';
                    $given = $author['given'] ?? '';
                    $authorsList[] = trim("{$given} {$family}");
                }
                $authors = implode(', ', array_filter($authorsList)) ?: 'Unknown Author';

                $year = data_get($item, 'published.date-parts.0.0')
                    ?? data_get($item, 'issued.date-parts.0.0')
                    ?? date('Y');

                $journal = data_get($item, 'container-title.0')
                    ?? data_get($item, 'publisher', 'Academic Publisher');

                $url = $item['URL'] ?? "https://doi.org/{$doi}";

                $firstAuthor = explode(' ', trim($authorsList[0] ?? 'Ref'))[1] ?? 'Ref';
                $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstAuthor).$year);

                return response()->json([
                    'found' => true,
                    'data' => [
                        'key' => $key,
                        'title' => $title,
                        'authors' => $authors,
                        'year' => (string) $year,
                        'source' => $journal,
                        'doi' => $doi,
                        'url' => $url,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            // Failover handled below
        }

        return response()->json([
            'found' => false,
            'message' => 'DOI metadata could not be fetched from Crossref. You can enter details manually.',
            'doi' => $doi,
        ], 404);
    }

    public function destroy(ResearchProject $project, Citation $citation): JsonResponse
    {
        abort_unless($citation->research_project_id === $project->id, 404);

        $citation->delete();

        return response()->json(['message' => 'Citation removed successfully']);
    }
}
