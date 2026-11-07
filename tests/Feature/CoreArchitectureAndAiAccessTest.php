<?php

namespace Tests\Feature;

use App\Models\ResearchProject;
use App\Models\User;
use App\Services\AiGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class CoreArchitectureAndAiAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_architecture_audit_endpoint_returns_six_data_structure_verifications(): void
    {
        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Protected Thesis Architecture',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $project->citations()->create([
            'key' => 'turing1950',
            'in_text_tag' => '(Turing, 1950)',
            'title' => 'Computing Machinery and Intelligence',
            'authors' => 'Alan Turing',
            'year' => 1950,
        ]);

        $project->tasks()->create([
            'title' => 'Complete literature review',
            'created_by' => $user->id,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}/architecture/audit");

        $response->assertOk()
            ->assertJsonPath('data.project_id', $project->id)
            ->assertJsonPath('data.architectural_integrity_passed', true)
            ->assertJsonPath('data.structures.linked_list.active', true)
            ->assertJsonPath('data.structures.binary_tree.active', true)
            ->assertJsonPath('data.structures.recursion.active', true)
            ->assertJsonPath('data.structures.graph_mapping.active', true)
            ->assertJsonPath('data.structures.linear_search.active', true)
            ->assertJsonPath('data.structures.hash_integrity.active', true);

        $this->assertNotEmpty($response->json('data.sealed_envelope.hash'));
        $this->assertNotEmpty($response->json('data.sealed_envelope.signature'));
    }

    public function test_ai_generated_content_is_free_to_use_for_all_authenticated_users(): void
    {
        // User has NO active trial and NO subscription
        $freeUser = User::factory()->create([
            'trial_ends_at' => now()->subDays(30),
        ]);

        $project = ResearchProject::create([
            'owner_id' => $freeUser->id,
            'title' => 'Free AI Thesis',
        ]);
        $project->members()->attach($freeUser->id, ['role' => 'student']);

        config()->set('services.ai.free_access', true);
        config()->set('services.ai.base_url', 'https://ai.example.test/v1');
        config()->set('services.ai.model', 'gemini-2.5-flash');

        Http::fake([
            'https://ai.example.test/v1/chat/completions' => Http::response([
                'model' => 'gemini-2.5-flash',
                'choices' => [['message' => ['content' => 'Free AI assistant analysis completed.']]],
            ]),
        ]);

        $this->actingAs($freeUser)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", [])
            ->assertOk()
            ->assertJsonPath('data.content', 'Free AI assistant analysis completed.');
    }

    public function test_ai_access_respects_free_access_toggle_when_restricted(): void
    {
        $expiredUser = User::factory()->create([
            'trial_ends_at' => now()->subDays(5),
        ]);

        $project = ResearchProject::create([
            'owner_id' => $expiredUser->id,
            'title' => 'Restricted Thesis',
        ]);
        $project->members()->attach($expiredUser->id, ['role' => 'student']);

        // Explicitly disable free access to test subscription check fallback
        config()->set('services.ai.free_access', false);

        $this->actingAs($expiredUser)
            ->postJson("/api/v1/projects/{$project->id}/ai/summary", [])
            ->assertForbidden()
            ->assertJsonPath('status', 'access_denied');

        config()->set('services.ai.free_access', true);
    }

    public function test_ai_gateway_encrypts_and_decrypts_sensitive_payloads_at_rest(): void
    {
        $gateway = app(AiGateway::class);
        $sensitivePrompt = ['research_question' => 'How to optimize quantum error correction?', 'author_pii' => 'Dr. Secret'];

        $encrypted = $gateway->encryptPayload($sensitivePrompt);

        $this->assertNotSame(json_encode($sensitivePrompt), $encrypted);
        $this->assertStringNotContainsString('quantum error correction', $encrypted);

        $decryptedJson = $gateway->decryptPayload($encrypted);
        $this->assertSame($sensitivePrompt, json_decode($decryptedJson, true));
    }

    public function test_ai_gateway_enforces_tls_transport_security_for_external_endpoints(): void
    {
        $gateway = app(AiGateway::class);

        // Localhost / 127.0.0.1 allowed on http for local Ollama
        $gateway->validateTransportSecurity('http://127.0.0.1:11434/v1');
        $gateway->validateTransportSecurity('http://localhost:11434/v1');

        // External HTTPS allowed
        $gateway->validateTransportSecurity('https://generativelanguage.googleapis.com/v1beta/openai');
        $gateway->validateTransportSecurity('https://api.groq.com/openai/v1');

        // External unencrypted HTTP must throw an exception
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insecure AI transport');
        $gateway->validateTransportSecurity('http://insecure-external-ai.com/v1');
    }

    public function test_ai_gateway_recognizes_google_gemini_provider(): void
    {
        config()->set('services.ai.base_url', 'https://generativelanguage.googleapis.com/v1beta/openai');
        config()->set('services.ai.model', 'gemini-2.5-flash');

        $gateway = app(AiGateway::class);

        $this->assertSame('Google Gemini', $gateway->provider());
        $this->assertContains('gemini-2.5-flash', $gateway->availableModels());
    }

    public function test_ai_status_endpoint_returns_structured_provider_availability(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions' => Http::response(['choices' => []], 200),
            'https://api.groq.com/openai/v1/models' => Http::response(['data' => []], 200),
            'http://127.0.0.1:11434/api/tags' => Http::response(['models' => [['name' => 'llama3.2']]], 200),
            'https://app.cambercloud.com*' => Http::response(['status' => 'ok'], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/ai/status');

        $response->assertOk()
            ->assertJsonPath('data.providers.alice.status', 'ready')
            ->assertJsonPath('data.providers.gemini.status', 'ready')
            ->assertJsonPath('data.providers.groq.status', 'ready')
            ->assertJsonPath('data.providers.ollama.status', 'ready');
    }

    public function test_ai_check_command_runs_successfully(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions' => Http::response(['choices' => []], 200),
            'https://api.groq.com/openai/v1/models' => Http::response(['data' => []], 200),
            'http://127.0.0.1:11434/api/tags' => Http::response(['models' => []], 200),
            'https://app.cambercloud.com*' => Http::response(['status' => 'ok'], 200),
        ]);

        $this->artisan('ai:check')
            ->assertExitCode(0);
    }

    public function test_ai_methodology_matrix_endpoint_returns_structured_matrix_with_rules_and_sections(): void
    {
        config()->set('services.ai.free_access', true);

        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Real-Time Collaborative Scientific Writing',
            'discipline' => 'Computer Science',
            'methodology' => 'Design science',
            'research_question' => 'How can AI agents assist thesis authors in maintaining rigorous methodology?',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/methodology-matrix", []);

        $response->assertOk()
            ->assertJsonPath('data.overall_methodology', 'Design Science Research (DSR)')
            ->assertJsonPath('data.matrix.0.section', '1. Introduction & Problem Space')
            ->assertJsonPath('data.matrix.0.status', 'AI Recommended');

        $this->assertCount(6, $response->json('data.matrix'));
    }

    public function test_ai_methodology_matrix_respects_qualitative_preference(): void
    {
        config()->set('services.ai.free_access', true);

        $user = User::factory()->create();
        $project = ResearchProject::create([
            'owner_id' => $user->id,
            'title' => 'Lived Experience of Doctoral Researchers',
            'discipline' => 'Education',
            'methodology' => 'Qualitative',
        ]);
        $project->members()->attach($user->id, ['role' => 'student']);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/ai/methodology-matrix", [
                'methodology_preference' => 'Qualitative',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.overall_methodology', 'Qualitative Inquiry (Constructivism)')
            ->assertJsonPath('data.matrix.0.methodology', 'Contextual Inquiry & Problem Framing');
    }
}
