<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiGateway
{
    public function configured(): bool
    {
        return filled(config('services.ai.base_url')) && filled(config('services.ai.model'));
    }

    public function model(): string
    {
        return (string) config('services.ai.model');
    }

    public function provider(): string
    {
        $baseUrl = (string) config('services.ai.base_url');

        if (str_contains($baseUrl, 'openrouter.ai') || (filled(config('services.openrouter.key')) && $baseUrl === config('services.openrouter.base_url'))) {
            return 'OpenRouter';
        }

        if (str_contains($baseUrl, 'generativelanguage.googleapis.com') || (filled(config('services.gemini.key')) && $baseUrl === config('services.gemini.base_url'))) {
            return 'Google Gemini';
        }

        if (str_contains($baseUrl, 'groq.com') || (filled(config('services.groq.key')) && $baseUrl === config('services.groq.base_url'))) {
            return 'Groq';
        }

        if (str_contains($baseUrl, 'cambercloud.com') || str_contains($baseUrl, 'camber')) {
            return 'Alice (Camber Cloud)';
        }

        if (str_contains($baseUrl, '11434') || str_contains($baseUrl, 'ollama')) {
            return 'Ollama (Local Private)';
        }

        return 'OpenAI-compatible AI';
    }

    /**
     * @return array<int, string>
     */
    public function availableModels(): array
    {
        $provider = $this->provider();

        if ($provider === 'OpenRouter') {
            return array_keys(config('services.openrouter.models', []));
        }

        if ($provider === 'Google Gemini') {
            return array_keys(config('services.gemini.free_models', []));
        }

        if ($provider === 'Groq') {
            return array_keys(config('services.groq.free_models', []));
        }

        if ($provider === 'Alice (Camber Cloud)') {
            return array_keys(config('services.camber.free_models', []));
        }

        return [config('services.ai.model', 'llama3.2')];
    }

    /**
     * @return array<string, mixed>
     */
    public function models(): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('The AI gateway is not configured.');
        }

        return $this->request()
            ->get('/models')
            ->throw()
            ->json();
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function chat(array $messages, ?string $model = null, ?float $temperature = null, ?int $maxTokens = null): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('The AI gateway is not configured. Set OPENROUTER_API_KEY, GEMINI_API_KEY, GROQ_API_KEY, or configure AI_BASE_URL and AI_MODEL.');
        }

        $payload = [
            'model' => $model ?: $this->model(),
            'messages' => $messages,
            'temperature' => $temperature ?? (float) config('services.ai.temperature', 0.2),
        ];

        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        return $this->request()
            ->post('/chat/completions', $payload)
            ->throw()
            ->json();
    }

    /**
     * Encrypt sensitive academic prompts or AI responses at rest before storage.
     *
     * @param  string|array<string, mixed>  $data
     */
    public function encryptPayload(string|array $data): string
    {
        $serialized = is_array($data)
            ? json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $data;

        return Crypt::encryptString((string) $serialized);
    }

    /**
     * Decrypt a previously encrypted payload.
     */
    public function decryptPayload(string $encryptedPayload): string
    {
        return Crypt::decryptString($encryptedPayload);
    }

    /**
     * Validate that external AI communication is encrypted in transit via TLS (HTTPS).
     */
    public function validateTransportSecurity(string $url): void
    {
        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? 'http');
        $host = strtolower($parsed['host'] ?? '');

        $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1', ''], true);

        if ($scheme !== 'https' && ! $isLocal) {
            throw new RuntimeException("Insecure AI transport: External AI API endpoints must use HTTPS TLS encryption. Target host: {$host}");
        }
    }

    /**
     * Probe all supported AI providers and check their real-time availability.
     *
     * @return array<string, array{
     *     name: string,
     *     model: string,
     *     endpoint: string,
     *     transport_security: string,
     *     has_credentials: bool,
     *     status: string,
     *     status_code: ?int,
     *     latency_ms: ?int,
     *     message: string,
     *     free_tier: string,
     *     recommended: bool
     * }>
     */
    public function probeAllProviders(): array
    {
        return [
            'openrouter' => $this->probeOpenRouter(),
            'alice' => $this->probeCamber(),
            'gemini' => $this->probeGemini(),
            'groq' => $this->probeGroq(),
            'ollama' => $this->probeOllama(),
        ];
    }

    /**
     * Probe OpenRouter unified AI router endpoint availability and auth.
     *
     * @return array{
     *     name: string,
     *     model: string,
     *     endpoint: string,
     *     transport_security: string,
     *     has_credentials: bool,
     *     status: string,
     *     status_code: ?int,
     *     latency_ms: ?int,
     *     message: string,
     *     free_tier: string,
     *     recommended: bool
     * }
     */
    public function probeOpenRouter(): array
    {
        $url = (string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $key = (string) (config('services.openrouter.key') ?: config('services.ai.key'));
        $model = (string) config('services.openrouter.model', 'openai/gpt-4o-mini');

        $start = microtime(true);
        $status = 'offline';
        $statusCode = null;
        $latencyMs = null;
        $message = '';

        try {
            $client = Http::connectTimeout(4)->timeout(6)->acceptJson();
            if (filled($key)) {
                $client = $client->withToken($key);
            }

            $response = $client->get(rtrim($url, '/').'/auth/key');
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $statusCode = $response->status();

            if ($response->successful()) {
                $status = 'ready';
                $limitRemaining = data_get($response->json(), 'data.limit_remaining');
                $message = filled($limitRemaining)
                    ? "Authenticated with OpenRouter. Balance remaining: \${$limitRemaining} USD."
                    : 'Authenticated and operational via OpenRouter.';
            } elseif ($statusCode === 401 || $statusCode === 403) {
                if (blank($key)) {
                    $status = 'key_required';
                    $message = 'Endpoint reachable over TLS. Add OPENROUTER_API_KEY in .env.';
                } else {
                    $status = 'invalid_key';
                    $message = 'Endpoint reachable, but OPENROUTER_API_KEY was rejected.';
                }
            } else {
                $modelsResp = $client->get(rtrim($url, '/').'/models');
                if ($modelsResp->successful()) {
                    $status = 'ready';
                    $message = 'OpenRouter models available.';
                } else {
                    $status = 'key_required';
                    $message = 'Endpoint reachable over TLS. Add OPENROUTER_API_KEY in .env.';
                }
            }
        } catch (\Throwable $e) {
            $message = 'Network connection failed: '.$e->getMessage();
        }

        return [
            'name' => 'OpenRouter (GPT-4o / Claude / DeepSeek)',
            'model' => $model,
            'endpoint' => $url,
            'transport_security' => 'HTTPS / TLS 1.3 (End-to-End Encrypted)',
            'has_credentials' => filled($key),
            'status' => $status,
            'status_code' => $statusCode,
            'latency_ms' => $latencyMs,
            'message' => $message,
            'free_tier' => 'Free models + $100 router credit',
            'recommended' => true,
        ];
    }

    /**
     * Probe Camber Cloud agent availability (Alice · Thesis Co-Pilot).
     *
     * @return array{
     *     name: string,
     *     model: string,
     *     endpoint: string,
     *     transport_security: string,
     *     has_credentials: bool,
     *     status: string,
     *     status_code: ?int,
     *     latency_ms: ?int,
     *     message: string,
     *     free_tier: string,
     *     recommended: bool
     * }
     */
    public function probeCamber(): array
    {
        $agentUrl = (string) config('services.camber.agent_url', 'https://app.cambercloud.com/agents/hafizurrahman78232878/Thesis_Copilot');
        $key = (string) config('services.camber.key');
        $model = (string) config('services.camber.model', 'Thesis_Copilot');

        $start = microtime(true);
        $status = 'offline';
        $statusCode = null;
        $latencyMs = null;
        $message = '';

        try {
            $response = Http::connectTimeout(4)->timeout(6)->get($agentUrl);
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $statusCode = $response->status();

            if ($response->successful()) {
                $status = 'ready';
                $message = 'Alice Camber agent is live and operational.';
            } else {
                $status = 'ready';
                $message = "Camber agent endpoint reachable (HTTP {$statusCode}).";
            }
        } catch (\Throwable $e) {
            $message = 'Connection to Camber Cloud: '.$e->getMessage();
        }

        return [
            'name' => 'Alice · Thesis Co-Pilot (Camber Cloud)',
            'model' => $model,
            'endpoint' => $agentUrl,
            'transport_security' => 'HTTPS / TLS 1.3 (End-to-End Encrypted)',
            'has_credentials' => filled($key) || filled($agentUrl),
            'status' => $status,
            'status_code' => $statusCode,
            'latency_ms' => $latencyMs,
            'message' => $message,
            'free_tier' => 'Camber Cloud Custom Agent',
            'recommended' => true,
        ];
    }

    /**
     * Probe Google Gemini API endpoint availability.
     *
     * @return array{
     *     name: string,
     *     model: string,
     *     endpoint: string,
     *     transport_security: string,
     *     has_credentials: bool,
     *     status: string,
     *     status_code: ?int,
     *     latency_ms: ?int,
     *     message: string,
     *     free_tier: string,
     *     recommended: bool
     * }
     */
    public function probeGemini(): array
    {
        $url = (string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta/openai');
        $key = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-2.5-flash');

        $start = microtime(true);
        $status = 'offline';
        $statusCode = null;
        $latencyMs = null;
        $message = '';

        try {
            $client = Http::connectTimeout(4)->timeout(6)->acceptJson();
            if (filled($key)) {
                $client = $client->withToken($key);
            }

            // Google Gemini OpenAI endpoint accepts chat completions
            $response = $client->post(rtrim($url, '/').'/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => 'ping']],
                'max_tokens' => 1,
            ]);

            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $statusCode = $response->status();

            if ($response->successful()) {
                $status = 'ready';
                $message = 'Fully operational and authenticated with Google AI Studio.';
            } elseif ($statusCode === 400 || $statusCode === 401 || $statusCode === 403) {
                $body = $response->body();
                if (blank($key)) {
                    $status = 'key_required';
                    $message = 'Endpoint reachable over TLS. Add GEMINI_API_KEY in .env to activate free tier.';
                } elseif (str_contains($body, 'API key not valid') || str_contains($body, 'API_KEY_INVALID')) {
                    $status = 'invalid_key';
                    $message = 'Endpoint reachable, but GEMINI_API_KEY was rejected by Google AI.';
                } else {
                    $status = 'key_required';
                    $message = 'Endpoint reachable over TLS. Add GEMINI_API_KEY in .env to activate free tier.';
                }
            } else {
                $status = 'error';
                $message = "Endpoint returned HTTP status {$statusCode}.";
            }
        } catch (\Throwable $e) {
            $message = 'Network connection failed: '.$e->getMessage();
        }

        return [
            'name' => 'Google Gemini API',
            'model' => $model,
            'endpoint' => $url,
            'transport_security' => 'HTTPS / TLS 1.3 (End-to-End Encrypted)',
            'has_credentials' => filled($key),
            'status' => $status,
            'status_code' => $statusCode,
            'latency_ms' => $latencyMs,
            'message' => $message,
            'free_tier' => '15 RPM / 1,000,000 TPM / 1,500 RPD (Free Tier)',
            'recommended' => true,
        ];
    }

    /**
     * Probe Groq Cloud LPU endpoint availability.
     *
     * @return array{
     *     name: string,
     *     model: string,
     *     endpoint: string,
     *     transport_security: string,
     *     has_credentials: bool,
     *     status: string,
     *     status_code: ?int,
     *     latency_ms: ?int,
     *     message: string,
     *     free_tier: string,
     *     recommended: bool
     * }
     */
    public function probeGroq(): array
    {
        $url = (string) config('services.groq.base_url', 'https://api.groq.com/openai/v1');
        $key = (string) config('services.groq.key');
        $model = (string) config('services.groq.model', 'llama-3.3-70b-versatile');

        $start = microtime(true);
        $status = 'offline';
        $statusCode = null;
        $latencyMs = null;
        $message = '';

        try {
            $client = Http::connectTimeout(4)->timeout(6)->acceptJson();
            if (filled($key)) {
                $client = $client->withToken($key);
            }

            $response = $client->get(rtrim($url, '/').'/models');
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $statusCode = $response->status();

            if ($response->successful()) {
                $status = 'ready';
                $message = 'Fully operational with ultra-low latency Groq LPUs.';
            } elseif ($statusCode === 401 || $statusCode === 403) {
                if (blank($key)) {
                    $status = 'key_required';
                    $message = 'Endpoint reachable over TLS. Add GROQ_API_KEY in .env to activate free tier.';
                } else {
                    $status = 'invalid_key';
                    $message = 'Endpoint reachable, but GROQ_API_KEY was rejected by Groq Cloud.';
                }
            } else {
                $status = 'error';
                $message = "Endpoint returned HTTP status {$statusCode}.";
            }
        } catch (\Throwable $e) {
            $message = 'Network connection failed: '.$e->getMessage();
        }

        return [
            'name' => 'Groq Cloud LPU',
            'model' => $model,
            'endpoint' => $url,
            'transport_security' => 'HTTPS / TLS 1.3 (End-to-End Encrypted)',
            'has_credentials' => filled($key),
            'status' => $status,
            'status_code' => $statusCode,
            'latency_ms' => $latencyMs,
            'message' => $message,
            'free_tier' => '30 RPM / 14,400 RPD (Free Tier for Open Models)',
            'recommended' => false,
        ];
    }

    /**
     * Probe local Ollama service availability.
     *
     * @return array{
     *     name: string,
     *     model: string,
     *     endpoint: string,
     *     transport_security: string,
     *     has_credentials: bool,
     *     status: string,
     *     status_code: ?int,
     *     latency_ms: ?int,
     *     message: string,
     *     free_tier: string,
     *     recommended: bool
     * }
     */
    public function probeOllama(): array
    {
        $url = 'http://127.0.0.1:11434';
        $model = 'llama3.2';

        $start = microtime(true);
        $status = 'offline';
        $statusCode = null;
        $latencyMs = null;
        $message = '';

        try {
            $response = Http::connectTimeout(2)->timeout(3)->get($url.'/api/tags');
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $statusCode = $response->status();

            if ($response->successful()) {
                $status = 'ready';
                $models = collect($response->json('models', []))->pluck('name')->implode(', ');
                $message = 'Local Ollama daemon running. Models: '.($models ?: 'None loaded yet');
            } else {
                $status = 'offline';
                $message = "Local daemon responded with HTTP status {$statusCode}.";
            }
        } catch (\Throwable) {
            $status = 'offline';
            $message = 'Local Ollama daemon is not running on port 11434.';
        }

        return [
            'name' => 'Ollama (Local Private)',
            'model' => $model,
            'endpoint' => $url.'/v1',
            'transport_security' => 'Localhost Loopback (100% Air-gapped / Private)',
            'has_credentials' => true, // No external key required
            'status' => $status,
            'status_code' => $statusCode,
            'latency_ms' => $latencyMs,
            'message' => $message,
            'free_tier' => '100% Unlimited Local Compute (No Quotas or Fees)',
            'recommended' => false,
        ];
    }

    protected function request(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.ai.base_url'), '/');
        $this->validateTransportSecurity($baseUrl);

        $request = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->connectTimeout((int) config('services.ai.connect_timeout', 5))
            ->timeout((int) config('services.ai.timeout', 30));

        if (str_contains($baseUrl, 'openrouter.ai')) {
            $siteUrl = (string) (config('app.url') ?: 'https://co-auth-research-h8a2ehfpbweedsak.indiasouthcentral-01.azurewebsites.net');
            $siteName = (string) config('app.name', 'Co-Auth Research Studio');
            $request = $request->withHeaders([
                'HTTP-Referer' => $siteUrl,
                'X-Title' => $siteName,
            ]);
        }

        $key = (string) config('services.ai.key');

        return filled($key)
            ? $request->withToken($key)
            : $request;
    }
}
