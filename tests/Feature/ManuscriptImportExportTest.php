<?php

namespace Tests\Feature;

use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManuscriptImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_manuscript_exports_to_compilable_latex_source(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Quantum Error Correcting Codes',
            'discipline' => 'Computer Science',
            'methodology' => 'Design science',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $project->citations()->create([
            'key' => 'shor1995',
            'title' => 'Scheme for reducing decoherence in quantum computer memory',
            'authors' => 'Peter W. Shor',
            'year' => 1995,
            'in_text_tag' => '[1]',
        ]);

        $project->documentVersions()->create([
            'author_id' => $user->id,
            'version_number' => 1,
            'content' => [
                'html' => '<h2>Introduction</h2><p>Quantum computing requires fault tolerance <span class="cite-ref" data-cite-key="shor1995">[1]</span>.</p>',
            ],
        ]);

        $response = $this->actingAs($user)
            ->get("/api/v1/projects/{$project->id}/export/latex");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/x-latex; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('\documentclass', $content);
        $this->assertStringContainsString('\title{Quantum Error Correcting Codes}', $content);
        $this->assertStringContainsString('\section{Introduction}', $content);
        $this->assertStringContainsString('\cite{shor1995}', $content);
        $this->assertStringContainsString('\bibitem{shor1995}', $content);
    }

    public function test_manuscript_exports_to_microsoft_word_doc(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Deep Learning in Clinical Diagnosis',
            'discipline' => 'Medicine',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $response = $this->actingAs($user)
            ->get("/api/v1/projects/{$project->id}/export/word");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/msword; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('xmlns:w="urn:schemas-microsoft-com:office:word"', $content);
        $this->assertStringContainsString('Deep Learning in Clinical Diagnosis', $content);
    }

    public function test_citations_export_to_ris_format_for_zotero_and_mendeley(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Distributed Systems Research',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $project->citations()->create([
            'key' => 'lamport1978',
            'title' => 'Time, Clocks, and the Ordering of Events in a Distributed System',
            'authors' => 'Leslie Lamport',
            'source' => 'Communications of the ACM',
            'year' => 1978,
            'doi' => '10.1145/359545.359563',
        ]);

        $response = $this->actingAs($user)
            ->get("/api/v1/projects/{$project->id}/citations/export-ris");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/x-research-info-systems; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('TY  - JOUR', $content);
        $this->assertStringContainsString('TI  - Time, Clocks, and the Ordering of Events in a Distributed System', $content);
        $this->assertStringContainsString('AU  - Leslie Lamport', $content);
        $this->assertStringContainsString('DO  - 10.1145/359545.359563', $content);
        $this->assertStringContainsString('ER  -', $content);
    }

    public function test_bibtex_citations_can_be_batch_imported(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Empirical Software Engineering',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $bibtexSample = <<<'BIB'
@article{vaswani2017attention,
  title={Attention is all you need},
  author={Vaswani, Ashish and Shazeer, Noam and Parmar, Niki},
  journal={Advances in neural information processing systems},
  year={2017},
  doi={10.48550/arXiv.1706.03762}
}

@article{devlin2018bert,
  title={Bert: Pre-training of deep bidirectional transformers for language understanding},
  author={Devlin, Jacob and Chang, Ming-Wei and Lee, Kenton},
  year={2018}
}
BIB;

        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/citations/import-bibtex", [
                'bibtex' => $bibtexSample,
            ]);

        $response->assertOk()
            ->assertJsonPath('imported_count', 2)
            ->assertJsonPath('total_citations', 2);

        $this->assertDatabaseHas('citations', [
            'research_project_id' => $project->id,
            'key' => 'vaswani2017attention',
            'title' => 'Attention is all you need',
        ]);

        $this->assertDatabaseHas('citations', [
            'research_project_id' => $project->id,
            'key' => 'devlin2018bert',
        ]);
    }
}
