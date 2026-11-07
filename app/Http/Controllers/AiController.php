<?php

namespace App\Http\Controllers;

use App\Models\ResearchProject;
use App\Services\AiGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiController extends Controller
{
    public function status(AiGateway $ai): JsonResponse
    {
        return response()->json([
            'data' => [
                'active_provider' => $ai->provider(),
                'active_model' => $ai->model(),
                'configured' => $ai->configured(),
                'encryption' => 'TLS in-transit & AES-256-CBC at-rest',
                'free_access' => (bool) config('services.ai.free_access', true),
                'providers' => $ai->probeAllProviders(),
            ],
        ]);
    }

    public function summarize(Request $request, ResearchProject $project, AiGateway $ai): JsonResponse
    {
        $validated = $request->validate([
            'instruction' => ['nullable', 'string', 'max:1000'],
        ]);

        $project->load(['members:id,name', 'tasks', 'feedback', 'papers:id,research_project_id,title,abstract,status']);

        try {
            $result = $ai->chat([
                [
                    'role' => 'system',
                    'content' => 'You are a careful university research assistant. Do not invent sources, findings, or citations. Return concise, actionable academic guidance.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'instruction' => $validated['instruction'] ?? 'Summarize the current project state and recommend the next three research actions.',
                        'project' => [
                            'title' => $project->title,
                            'discipline' => $project->discipline,
                            'description' => $project->description,
                            'progress' => $project->progress,
                            'open_tasks' => $project->tasks->where('status', '!=', 'done')->pluck('title')->values(),
                            'open_feedback' => $project->feedback->where('status', 'open')->pluck('body')->values(),
                            'papers' => $project->papers->map(fn ($paper): array => [
                                'title' => $paper->title,
                                'abstract' => $paper->abstract,
                                'status' => $paper->status,
                            ])->values(),
                        ],
                    ], JSON_THROW_ON_ERROR),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('AI summary service unavailable: '.$exception->getMessage());

            return response()->json(['message' => 'The research assistant is unavailable right now.'], 503);
        }

        return response()->json([
            'data' => [
                'model' => $result['model'] ?? $ai->model(),
                'provider' => $ai->provider(),
                'content' => data_get($result, 'choices.0.message.content'),
            ],
        ]);
    }

    public function evaluate(Request $request, ResearchProject $project, AiGateway $ai): JsonResponse
    {
        $validated = $request->validate([
            'instruction' => ['required', 'string', 'max:1000'],
            'test_data' => ['required', 'array', 'max:50'],
        ]);

        try {
            $result = $ai->chat([
                ['role' => 'system', 'content' => 'You are a rigorous research test evaluator. Evaluate only the supplied test data. Do not fabricate results, citations, or claims. Return JSON with pass, score, findings, and next_actions.'],
                ['role' => 'user', 'content' => json_encode([
                    'project' => $project->title,
                    'instruction' => $validated['instruction'],
                    'test_data' => $validated['test_data'],
                ], JSON_THROW_ON_ERROR)],
            ]);
        } catch (Throwable $exception) {
            return response()->json(['message' => 'The research assistant is unavailable right now.'], 503);
        }

        return response()->json(['data' => [
            'model' => $result['model'] ?? $ai->model(),
            'provider' => $ai->provider(),
            'content' => data_get($result, 'choices.0.message.content'),
        ]]);
    }

    /**
     * Generate an Excel-style methodology & section governance matrix via Alice / AI Copilot.
     */
    public function recommendMethodologyMatrix(Request $request, ResearchProject $project, AiGateway $ai): JsonResponse
    {
        $validated = $request->validate([
            'methodology_preference' => ['nullable', 'string', 'max:100'],
        ]);

        $project->load(['members:id,name', 'citations']);

        $defaultMatrix = $this->generateFallbackMethodologyMatrix($project, $validated['methodology_preference'] ?? null);

        try {
            $prompt = [
                [
                    'role' => 'system',
                    'content' => 'You are Alice, Lead Academic Research Methodologist and Thesis Co-Pilot for the Co-Auth Research Platform. Recommend an academic section breakdown, research methodologies, and rigor rules formatted as a spreadsheet matrix for this study. Return strictly a JSON object with keys: "overall_methodology", "paradigm", "theoretical_grounding", and "matrix" (array of objects with: row_id, section, methodology, ai_recommendation, author_rule, target_words, min_citations, status). Do not fabricate citations.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'project_title' => $project->title,
                        'discipline' => $project->discipline,
                        'research_question' => $project->research_question,
                        'current_methodology' => $project->methodology,
                        'preference' => $validated['methodology_preference'] ?? $project->methodology,
                    ], JSON_THROW_ON_ERROR),
                ],
            ];

            $result = $ai->chat($prompt);
            $rawContent = data_get($result, 'choices.0.message.content');

            if ($rawContent) {
                if (preg_match('/\{[\s\S]*\}/', $rawContent, $matches)) {
                    $parsed = json_decode($matches[0], true);
                    if (is_array($parsed) && ! empty($parsed['matrix'])) {
                        return response()->json([
                            'data' => [
                                'provider' => $ai->provider(),
                                'model' => $result['model'] ?? $ai->model(),
                                'source' => 'ai_generated',
                                'overall_methodology' => $parsed['overall_methodology'] ?? $defaultMatrix['overall_methodology'],
                                'paradigm' => $parsed['paradigm'] ?? $defaultMatrix['paradigm'],
                                'theoretical_grounding' => $parsed['theoretical_grounding'] ?? $defaultMatrix['theoretical_grounding'],
                                'matrix' => $parsed['matrix'],
                            ],
                        ]);
                    }
                }
            }
        } catch (Throwable $e) {
            Log::info('AI methodology matrix fallback used: '.$e->getMessage());
        }

        return response()->json([
            'data' => array_merge($defaultMatrix, [
                'provider' => $ai->provider(),
                'model' => $ai->model(),
                'source' => 'methodology_rule_engine',
            ]),
        ]);
    }

    /**
     * Provide an academically grounded fallback methodology matrix.
     *
     * @return array{overall_methodology: string, paradigm: string, theoretical_grounding: string, matrix: list<array<string, mixed>>}
     */
    protected function generateFallbackMethodologyMatrix(ResearchProject $project, ?string $preferredMethod): array
    {
        $method = $preferredMethod ?: ($project->methodology ?: 'Design science');

        if (stripos($method, 'qualitative') !== false) {
            return [
                'overall_methodology' => 'Qualitative Inquiry (Constructivism)',
                'paradigm' => 'Interpretivism & Inductive Thematic Synthesis',
                'theoretical_grounding' => 'Grounded Theory (Strauss & Corbin, 1998; Braun & Clarke, 2006)',
                'matrix' => [
                    [
                        'row_id' => 1,
                        'section' => '1. Introduction & Research Problem',
                        'methodology' => 'Contextual Inquiry & Problem Framing',
                        'ai_recommendation' => 'Articulate human-centered problem statement, target participants, and research questions.',
                        'author_rule' => 'Focus on qualitative lived experience; state researcher positionality clearly.',
                        'target_words' => '1,200',
                        'min_citations' => 8,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 2,
                        'section' => '2. Theoretical Framework & Literature Review',
                        'methodology' => 'Thematic Literature Synthesis',
                        'ai_recommendation' => 'Synthesize >= 12 peer-reviewed studies; identify conceptual blindspots in current literature.',
                        'author_rule' => 'Ground theoretical stance in socio-technical systems and constructivist learning theory.',
                        'target_words' => '2,200',
                        'min_citations' => 15,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 3,
                        'section' => '3. Qualitative Methodology & Fieldwork Protocol',
                        'methodology' => 'Semi-Structured Interviews & Thematic Analysis',
                        'ai_recommendation' => 'Detail sampling criteria (N >= 15), interview protocol, transcription integrity, and coding codebook.',
                        'author_rule' => 'Conduct two pilot interviews before full cohort; maintain audit trail of codebook iterations.',
                        'target_words' => '2,500',
                        'min_citations' => 10,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 4,
                        'section' => '4. Findings & Thematic Interpretation',
                        'methodology' => 'Iterative Thematic Coding (Braun & Clarke)',
                        'ai_recommendation' => 'Present 3-5 core themes supported by direct verbatim quotes, code matrices, and inter-coder reliability.',
                        'author_rule' => 'Include qualitative code matrix table and anonymized participant demographic summaries.',
                        'target_words' => '3,000',
                        'min_citations' => 6,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 5,
                        'section' => '5. Discussion & Theoretical Contributions',
                        'methodology' => 'Comparative Interpretation',
                        'ai_recommendation' => 'Connect empirical themes back to theoretical framework; highlight unexpected nuances.',
                        'author_rule' => 'Contrast findings against prior survey benchmarks to highlight qualitative depth.',
                        'target_words' => '1,800',
                        'min_citations' => 10,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 6,
                        'section' => '6. Reflexivity, Limitations & Conclusion',
                        'methodology' => 'Reflexive Validity & Ethical Synthesis',
                        'ai_recommendation' => 'Reflect on researcher bias, transferability boundaries, ethical consent compliance, and future inquiries.',
                        'author_rule' => 'Detail IRB ethics protocol approval and member-checking validation steps.',
                        'target_words' => '1,000',
                        'min_citations' => 4,
                        'status' => 'AI Recommended',
                    ],
                ],
            ];
        }

        if (stripos($method, 'quantitative') !== false) {
            return [
                'overall_methodology' => 'Quantitative Empirical Investigation',
                'paradigm' => 'Positivism / Post-Positivism & Deductive Hypothesis Testing',
                'theoretical_grounding' => 'Statistical Inference & Controlled Benchmarking (Cohen, 1988)',
                'matrix' => [
                    [
                        'row_id' => 1,
                        'section' => '1. Introduction & Hypotheses Formulation',
                        'methodology' => 'Deductive Problem Framing',
                        'ai_recommendation' => 'Define independent/dependent variables and state null (H0) and alternative (H1) hypotheses.',
                        'author_rule' => 'State explicit quantitative performance targets (e.g. >25% latency reduction at 99th percentile).',
                        'target_words' => '1,200',
                        'min_citations' => 8,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 2,
                        'section' => '2. Related Work & Empirical Benchmarks',
                        'methodology' => 'Quantitative Meta-Synthesis',
                        'ai_recommendation' => 'Compare prior state-of-the-art benchmarks in tabular summary with reported effect sizes.',
                        'author_rule' => 'Include comparative baseline matrix of top 3 related open-source frameworks.',
                        'target_words' => '2,000',
                        'min_citations' => 16,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 3,
                        'section' => '3. Experimental Design & Measurement Protocol',
                        'methodology' => 'Controlled Lab / Benchmark Experiment',
                        'ai_recommendation' => 'Specify instrumentation, sampling frequency, power analysis (alpha=0.05, power=0.80), and controls.',
                        'author_rule' => 'Execute minimum 50 trials per condition to establish statistical normal distribution.',
                        'target_words' => '2,200',
                        'min_citations' => 10,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 4,
                        'section' => '4. Statistical Analysis & Data Distribution',
                        'methodology' => 'Inferential Statistics & Hypothesis Testing',
                        'ai_recommendation' => 'Report mean, standard deviation, ANOVA/t-test p-values, confidence intervals, and effect sizes (Cohen d).',
                        'author_rule' => 'Render box plots and violin distribution plots; report p-value thresholds explicitly.',
                        'target_words' => '2,500',
                        'min_citations' => 5,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 5,
                        'section' => '5. Discussion & Ablation Findings',
                        'methodology' => 'Empirical Triangulation & Ablation',
                        'ai_recommendation' => 'Discuss hypothesis rejection/acceptance; evaluate component ablation performance.',
                        'author_rule' => 'Highlight sensitivity analysis when system load scales past 100 concurrent requests.',
                        'target_words' => '1,800',
                        'min_citations' => 8,
                        'status' => 'AI Recommended',
                    ],
                    [
                        'row_id' => 6,
                        'section' => '6. Threats to Validity & Conclusion',
                        'methodology' => 'Internal & External Validity Audit',
                        'ai_recommendation' => 'Address construct validity, internal confounders, and external generalizability limits.',
                        'author_rule' => 'Provide GitHub link to reproducible benchmark suite and raw dataset CSV.',
                        'target_words' => '1,100',
                        'min_citations' => 4,
                        'status' => 'AI Recommended',
                    ],
                ],
            ];
        }

        // Default: Design Science Research (Engineering, CS, Interactive Systems)
        return [
            'overall_methodology' => 'Design Science Research (DSR)',
            'paradigm' => 'Pragmatism & Artifact Utility Theory',
            'theoretical_grounding' => 'DSR Guidelines for Information Systems (Hevner et al., 2004; Peffers et al., 2007)',
            'matrix' => [
                [
                    'row_id' => 1,
                    'section' => '1. Introduction & Problem Space',
                    'methodology' => 'Problem Identification & Value Proposition',
                    'ai_recommendation' => 'Articulate real-world research problem, stakeholder friction, and SMART research objectives.',
                    'author_rule' => 'Emphasize real-time co-authoring friction and supervisory review overhead.',
                    'target_words' => '1,200',
                    'min_citations' => 8,
                    'status' => 'AI Recommended',
                ],
                [
                    'row_id' => 2,
                    'section' => '2. Theoretical Grounding & Literature Gap',
                    'methodology' => 'Knowledge Base Foundation (Hevner Guideline 2)',
                    'ai_recommendation' => 'Ground in peer-reviewed seminal works; map state-of-the-art tools and articulate verifiable research gap.',
                    'author_rule' => 'Cite seminal DSR guidelines (Hevner 2004, Gregor 2006) and collaborative editing systems.',
                    'target_words' => '2,000',
                    'min_citations' => 14,
                    'status' => 'AI Recommended',
                ],
                [
                    'row_id' => 3,
                    'section' => '3. Artifact Architecture & Methodological Cycles',
                    'methodology' => 'Design as a Search Process (Hevner Guideline 6)',
                    'ai_recommendation' => 'Detail iterative build cycles, architectural blueprints, data models, and functional specifications.',
                    'author_rule' => 'Document multi-agent orchestration, WebSocket sync pipelines, and SQLite persistence engine.',
                    'target_words' => '2,500',
                    'min_citations' => 9,
                    'status' => 'AI Recommended',
                ],
                [
                    'row_id' => 4,
                    'section' => '4. Implementation & Rigorous Artifact Evaluation',
                    'methodology' => 'Design Evaluation (Hevner Guideline 3)',
                    'ai_recommendation' => 'Evaluate artifact utility, throughput, and reliability through empirical benchmarks or user studies.',
                    'author_rule' => 'Conduct concurrent editing stress test (50 agents) and report latency percentiles.',
                    'target_words' => '2,200',
                    'min_citations' => 6,
                    'status' => 'AI Recommended',
                ],
                [
                    'row_id' => 5,
                    'section' => '5. Discussion & Research Contributions',
                    'methodology' => 'Research Contributions (Hevner Guideline 4)',
                    'ai_recommendation' => 'Contrast artifact capabilities against existing literature; explain design principles and generalizable patterns.',
                    'author_rule' => 'Articulate clear architectural design principles for future multi-agent research tools.',
                    'target_words' => '1,800',
                    'min_citations' => 8,
                    'status' => 'AI Recommended',
                ],
                [
                    'row_id' => 6,
                    'section' => '6. Limitations, Rigor Audit & Future Horizons',
                    'methodology' => 'Research Rigor & Communication (Hevner Guidelines 5 & 7)',
                    'ai_recommendation' => 'Address technical constraints, scalability bottlenecks, deployment limitations, and next research vectors.',
                    'author_rule' => 'Outline future work on distributed cloud federation and automated peer-review consensus.',
                    'target_words' => '1,000',
                    'min_citations' => 4,
                    'status' => 'AI Recommended',
                ],
            ],
        ];
    }
}
