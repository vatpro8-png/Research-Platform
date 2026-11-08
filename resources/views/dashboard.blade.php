<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Co-Auth Research Studio</title>
        @include('partials.favicons')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="live-tech-ui">
        <div class="app-shell">
            <aside class="sidebar">
                <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><img src="{{ asset('images/logo.png') }}" alt="Co-Auth Logo" class="brand-logo-img"></span><span><strong>Co-Auth</strong><small>research studio</small></span></a>
                @include('partials.navigation')
                <div class="sidebar-foot">
                    <div class="status-dot"><span></span>Workspace online</div>
                    <p>Build a clearer thesis, together.</p>
                    <form method="POST" action="{{ route('logout') }}" style="margin-top: 0.75rem;">
                        @csrf
                        <button type="submit" class="text-button" style="color: var(--muted, #71717a); font-size: 0.8125rem; cursor: pointer; background: none; border: none; padding: 0;">Sign out</button>
                    </form>
                </div>
            </aside>
            <main class="main-content">
                @php
                    $currentUser = Auth::user();
                    $userName = $currentUser?->name ?? 'Researcher';
                    $userFirstName = explode(' ', $userName)[0] ?? 'Researcher';
                    $userInitials = collect(explode(' ', $userName))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('') ?: 'RS';
                    $userRole = ($currentUser?->is_teacher ?? false) ? 'Supervisor' : 'Student researcher';
                @endphp
                <header class="topbar"><div class="breadcrumbs"><span>Workspace</span><b>/</b><strong>Overview</strong></div><div class="profile-chip"><span class="avatar avatar-teal">{{ $userInitials }}</span><span><strong>{{ $userName }}</strong><small>{{ $userRole }}</small></span><span class="chevron">⌄</span></div></header>
                <section class="welcome-row"><div><p class="eyebrow">{{ now()->format('l, d F Y') }}</p><h1>Welcome back, {{ $userFirstName }}<span>.</span></h1><p class="lede">Your research spaces, shared notes, and feedback in one calm place.</p></div><button class="button button-primary" type="button" data-open-project-form>＋ New project</button></section>
                <section class="metric-grid" aria-label="Workspace summary"><div class="metric"><span class="metric-icon amber">◈</span><div><strong id="project-count">0</strong><small>Active projects</small></div><em>+1 this month</em></div><div class="metric"><span class="metric-icon coral">↗</span><div><strong>12</strong><small>Open feedback</small></div><em class="muted">3 due this week</em></div><div class="metric"><span class="metric-icon teal">◎</span><div><strong>86%</strong><small>Milestone health</small></div><em>On track</em></div></section>
                <section class="readiness-panel panel"><div class="panel-heading"><div><p class="eyebrow">Research readiness</p><h2>Know what is ready before you write</h2></div><span class="live-label" id="readiness-summary">Checking connections…</span></div><div class="readiness-grid" id="readiness-list"><div class="loading-line">Checking AI, sync, and realtime services…</div></div></section>
                <section class="proposal-compass panel" aria-labelledby="proposal-compass-title">
                    <div class="panel-heading">
                        <div><p class="eyebrow">Project proposal compass</p><h2 id="proposal-compass-title">Keep every draft aligned to the brief</h2></div>
                        <span class="proposal-status">4 research commitments</span>
                    </div>
                    <div class="proposal-compass-grid">
                        <article><span class="compass-index">01</span><div><strong>Research question</strong><p>Make the problem, audience, and intended contribution explicit before drafting.</p></div></article>
                        <article><span class="compass-index">02</span><div><strong>Methodological fit</strong><p>Connect evidence, theory, and rigor criteria to the selected research method.</p></div></article>
                        <article><span class="compass-index">03</span><div><strong>Verifiable evidence</strong><p>Keep citations, figures, data, and claims traceable for reviewers and supervisors.</p></div></article>
                        <article><span class="compass-index">04</span><div><strong>Responsible collaboration</strong><p>Preserve feedback, version history, ethics status, and authorship decisions.</p></div></article>
                    </div>
                    <p class="proposal-note">This compass reflects the Co-Auth proposal: a calm writing surface with grounded AI, shared review, and an auditable research trail.</p>
                </section>
                <section class="research-lifecycle panel" aria-labelledby="research-lifecycle-title">
                    <div class="panel-heading">
                        <div><p class="eyebrow">Research lifecycle</p><h2 id="research-lifecycle-title">From idea to defensible contribution</h2></div>
                        <span class="proposal-status">Foundation map</span>
                    </div>
                    <div class="lifecycle-grid">
                        @foreach (config('research.lifecycle') as $stage)
                            <article class="lifecycle-card lifecycle-card-{{ $stage['accent'] }}">
                                <div class="lifecycle-card-top"><span class="lifecycle-number">{{ $stage['number'] }}</span><span class="lifecycle-stage-key">{{ $stage['key'] }}</span></div>
                                <p class="eyebrow">{{ $stage['eyebrow'] }}</p>
                                <h3>{{ $stage['title'] }}</h3>
                                <p>{{ $stage['purpose'] }}</p>
                                <div class="lifecycle-output-list">
                                    @foreach ($stage['outputs'] as $output)
                                        <span>{{ $output }}</span>
                                    @endforeach
                                </div>
                                <a class="text-button" href="{{ $stage['key'] === 'review' ? route('feedback.index') : ($stage['key'] === 'presentation' ? '#projects' : '#projects') }}">Open {{ $stage['title'] }} <span>→</span></a>
                            </article>
                        @endforeach
                    </div>
                </section>
                <div class="section-heading" id="projects"><div><p class="eyebrow">Your research spaces</p><h2>Projects in motion</h2></div><a href="#projects">View all <span>→</span></a></div><section class="project-grid" id="project-list" aria-live="polite"><div class="empty-state">Loading your research spaces…</div></section>
                <section class="lower-grid" id="activity"><div class="panel activity-panel"><div class="panel-heading"><div><p class="eyebrow">Live workspace</p><h2>Recent activity</h2></div><span class="live-label"><i></i>Live</span></div><div class="activity-item"><span class="activity-icon teal">MS</span><p><strong>Mira Sen</strong> updated the methodology notes<small>Adaptive Learning in Distributed Teams · 18 min ago</small></p><span class="activity-arrow">→</span></div><div class="activity-item"><span class="activity-icon amber">AK</span><p><strong>Alex Kim</strong> left feedback on your literature review<small>Adaptive Learning in Distributed Teams · 1 hr ago</small></p><span class="activity-arrow">→</span></div><div class="activity-item"><span class="activity-icon coral">RN</span><p><strong>Ravi Nair</strong> joined a research project<small>Climate Resilience in Urban Systems · 3 hrs ago</small></p><span class="activity-arrow">→</span></div></div><div class="panel focus-panel"><div class="panel-heading"><div><p class="eyebrow">Today</p><h2>Next focus</h2></div><span class="focus-date">03 OCT</span></div><div class="focus-block"><span class="focus-number">01</span><div><strong>Refine research questions</strong><p>Adaptive Learning in Distributed Teams</p></div><span class="checkmark">✓</span></div><div class="focus-block"><span class="focus-number">02</span><div><strong>Review supervisor notes</strong><p>Climate Resilience in Urban Systems</p></div><span class="checkmark">✓</span></div><button class="text-button">Open task board <span>→</span></button></div></section>
            </main>
        </div>
        <dialog class="project-dialog" id="project-dialog"><form method="dialog" id="project-form"><button class="dialog-close" value="cancel" aria-label="Close">×</button><p class="eyebrow">Start a shared space</p><h2>New research project</h2><p class="dialog-copy">Give your thesis a home for drafts, feedback, and shared momentum.</p><label>Project title<input name="title" required maxlength="150" placeholder="e.g. Adaptive Learning in Distributed Teams"></label><label>Discipline<input name="discipline" maxlength="100" placeholder="e.g. Computer Science"></label><label>Short description<textarea name="description" maxlength="1000" rows="3" placeholder="What question is this project exploring?"></textarea></label><div class="dialog-actions"><button class="button button-quiet" value="cancel">Cancel</button><button class="button button-primary" value="default">Create project</button></div><p class="form-error" id="form-error" role="alert"></p></form></dialog>
    </body>
</html>