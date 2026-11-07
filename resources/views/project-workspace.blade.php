<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $project->title }} · Research Studio · Co-Auth</title>
        @vite(['resources/css/app.css', 'resources/js/workspace.js'])
    </head>
    <body data-project-id="{{ $project->id }}" class="live-tech-ui layout-full theme-clean-white font-serif margin-normal spacing-1-5 layout-single-col">
        <div class="workspace-shell">
            <header class="workspace-topbar">
                <a class="brand" href="{{ route('dashboard') }}">
                    <span class="brand-mark">C</span>
                    <span><strong>Co-Auth</strong><small>research studio</small></span>
                </a>
                <div class="workspace-crumb">
                    <a href="{{ route('dashboard') }}">All projects</a>
                    <span>/</span>
                    <strong class="project-title-crumb">{{ $project->title }}</strong>
                </div>

                <!-- Live Collaboration Presence Indicator -->
                <div class="team-presence-bar" id="team-presence-bar" title="Active working session">
                    <span class="presence-pulse"></span>
                    <span id="active-editor-banner" class="active-editor-banner">Collaborators in room</span>
                </div>

                <!-- Layout & View Switcher -->
                <div class="layout-switch-group" role="toolbar" aria-label="Workspace Layout">
                    <button type="button" class="layout-btn is-active" data-layout-mode="full" title="Full Studio (Editor + Sidebars)">Studio</button>
                    <button type="button" class="layout-btn" data-layout-mode="focus" title="Focus Manuscript (Centered Paper)">Focus</button>
                    <button type="button" class="layout-btn" data-layout-mode="review" title="Reviewer Split (Manuscript + Feedback)">Review</button>
                </div>

                <!-- Theme / Palette Switcher -->
                <div class="theme-switch-group" role="toolbar" aria-label="Color Theme">
                    <button type="button" class="theme-btn theme-white is-active" data-theme-mode="clean_white" title="Clean White Paper"></button>
                    <button type="button" class="theme-btn theme-sepia" data-theme-mode="warm_sepia" title="Warm Sepia Manuscript"></button>
                    <button type="button" class="theme-btn theme-dark" data-theme-mode="academic_dark" title="Academic Dark Slate"></button>
                </div>

                <div class="workspace-actions">
                    <span class="save-state" id="save-state">All changes saved</span>
                    <button class="button button-quiet" type="button" id="open-instructions-guide" title="Full project instructions, biography, and manual">📖 Project Guide</button>
                    <button class="button button-quiet" type="button" id="btn-export-markdown" title="Export manuscript as Markdown (.md)">📥 Export .md</button>
                    <button class="button button-quiet" type="button" id="open-format-panel" title="Paper margins and typography">📐 Paper layout</button>
                    <button class="button button-quiet" type="button" id="print-paper" title="Print or export manuscript to PDF">🖨 Print / PDF</button>
                    <a class="button button-quiet" href="{{ route('dashboard') }}">← Back</a>
                </div>
            </header>

            <main class="workspace-main">
                <!-- Project Heading & AI Assistant -->
                <section class="workspace-heading">
                    <div>
                        <div class="heading-tag-row">
                            <span class="discipline-tag">{{ $project->discipline ?: 'Interdisciplinary Research' }}</span>
                            <span class="status-pill status-{{ $project->status ?: 'draft' }}">{{ ucfirst($project->status ?: 'Draft') }}</span>
                            <span class="ai-model-badge" style="background: rgba(45, 122, 102, 0.15); color: var(--teal); border: 1px solid rgba(45, 122, 102, 0.3);">🔒 AI-Encrypted (TLS / AES-256) · Free Access</span>
                        </div>
                        <h1>{{ $project->title }}</h1>
                        <p class="workspace-description">{{ $project->description ?: 'A shared collaborative space for thesis drafting, evidence capture, citations, and supervisor reviews.' }}</p>
                        <div class="ai-button-row">
                            <button class="button button-ai" id="ai-summary" type="button">✦ Ask research assistant</button>
                            <a href="https://app.cambercloud.com/agents/hafizurrahman78232878/Thesis_Copilot" target="_blank" rel="noopener noreferrer" class="button button-quiet" style="border-color: rgba(124, 58, 237, 0.4); color: #7c3aed; font-weight: 600;" title="Launch Alice: Lead Academic Methodologist on Camber Cloud">🎓 Launch Alice Copilot ↗</a>
                            <button class="button button-ai-quiet" type="button" data-ai-instruction="Review the current project context and identify the three most important next research actions.">Next best actions</button>
                            <button class="button button-ai-quiet" type="button" data-ai-instruction="Check whether the research question, methodology, evidence, and expected contribution are aligned.">Check alignment</button>
                            <button class="button button-quiet" id="open-theory-guide" type="button">📚 Methodology theory guide</button>
                            <button class="button button-quiet" id="trigger-arch-audit" type="button" title="Verify Binary Tree, Recursion, Graph Mapping, Linked List, Linear Search, and Hash">🛡 Audit Core Architecture</button>
                        </div>
                        <p class="ai-result" id="ai-result" role="status"></p>
                        <span class="ai-model-badge" id="ai-model-badge">Google Gemini · gemini-2.5-flash (Free Tier)</span>
                    </div>
                    <div class="workspace-progress">
                        <strong>{{ $project->progress }}%</strong>
                        <span>Milestone health</span>
                        <div class="progress-track">
                            <div class="progress-bar" style="width: {{ $project->progress }}%"></div>
                        </div>
                    </div>
                </section>
                @php
                    $activeLifecycleStage = $project->progress < 34 ? 'initialization' : ($project->progress < 67 ? 'presentation' : 'review');
                @endphp
                <nav class="workspace-stage-rail" aria-label="Research lifecycle stages">
                    @foreach (config('research.lifecycle') as $stage)
                        <a class="workspace-stage {{ $activeLifecycleStage === $stage['key'] ? 'is-active' : '' }} workspace-stage-{{ $stage['accent'] }}" href="#stage-{{ $stage['key'] }}">
                            <span class="workspace-stage-number">{{ $stage['number'] }}</span>
                            <span><strong>{{ $stage['title'] }}</strong><small>{{ $stage['eyebrow'] }}</small></span>
                        </a>
                        @if (!$loop->last)
                            <span class="workspace-stage-arrow" aria-hidden="true">→</span>
                        @endif
                    @endforeach
                </nav>

                <!-- Research Setup & Methodology Theory Differentiation Panel -->
                <section class="setup-panel panel" id="stage-initialization">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">Research Method & Theoretical Framework</p>
                            <h2>Define the study methodology and epistemological ground</h2>
                        </div>
                        <div class="setup-heading-actions">
                            <button type="button" class="setup-toggle-btn" id="toggle-setup-panel" title="Collapse or expand the setup panel">Collapse setup ↑</button>
                            <button type="button" class="text-button" id="trigger-theory-modal">Methodology theory comparison →</button>
                            <span class="save-state" id="setup-state">Project setup</span>
                        </div>
                    </div>
                    <form id="setup-form" class="setup-form">
                        <label>
                            Research question
                            <textarea name="research_question" rows="2" placeholder="What core problem or inquiry is this study investigating?"></textarea>
                        </label>
                        <div class="methodology-select-block">
                            <label>
                                Methodology & Theoretical Stance
                                <select name="methodology" id="methodology-select">
                                    <option value="">Choose an approach</option>
                                    <option value="Qualitative">Qualitative (Interpretivism / Constructivism)</option>
                                    <option value="Quantitative">Quantitative (Positivism / Post-Positivism)</option>
                                    <option value="Mixed methods">Mixed Methods (Pragmatism & Triangulation)</option>
                                    <option value="Design science">Design Science Research (Artifact & Utility Theory)</option>
                                    <option value="Literature review">Systematic Literature Review (PRISMA Synthesis)</option>
                                    <option value="Action research">Action Research (Critical Theory & Cyclical Intervention)</option>
                                </select>
                            </label>
                            <!-- Dynamic Methodology Theory Differentiation Card -->
                            <div class="theory-card" id="theory-card">
                                <div class="theory-card-header">
                                    <span class="theory-card-badge" id="theory-card-paradigm">Select a methodology</span>
                                </div>
                                <p class="theory-card-desc" id="theory-card-desc">Selecting a methodology will display its theoretical premise, seminal theorists, epistemological ground, and rigor criteria.</p>
                            </div>
                        </div>
                        <label>
                            Expected contribution / outcome
                            <textarea name="expected_outcome" rows="2" placeholder="What contribution, prototype, empirical evidence, or template do you expect to produce?"></textarea>
                        </label>
                        <label>
                            Institutional ethics status (IRB)
                            <select name="ethics_status">
                                <option value="not_assessed">Not assessed</option>
                                <option value="not_required">Not required</option>
                                <option value="submitted">Submitted for IRB/Ethics review</option>
                                <option value="approved">Approved by Ethics Committee</option>
                            </select>
                        </label>
                        <div class="setup-submit-row">
                            <button class="button button-primary" type="submit">Save research setup</button>
                        </div>
                    </form>
                </section>

                <section class="studio-architecture panel" aria-labelledby="studio-architecture-title">
                    <div class="panel-heading">
                        <div><p class="eyebrow">Studio architecture</p><h2 id="studio-architecture-title">Follow the research from question to contribution</h2></div>
                        <button class="text-button" type="button" id="toggle-architecture" aria-expanded="true">Collapse tree ↑</button>
                    </div>
                    <div class="architecture-tree" id="architecture-tree">
                        <button class="architecture-node is-root" type="button" data-architecture-node="question"><span class="node-marker">Q</span><span><strong>Research question</strong><small>Problem, scope, stakeholders, and expected outcome</small></span><span class="node-state">01</span></button>
                        <div class="architecture-branch">
                            <button class="architecture-node" type="button" data-architecture-node="method"><span class="node-marker">M</span><span><strong>Method & theory</strong><small>Paradigm, epistemology, design, and ethics</small></span><span class="node-state">02</span></button>
                            <button class="architecture-node" type="button" data-architecture-node="evidence"><span class="node-marker">E</span><span><strong>Evidence & sources</strong><small>Data, citations, figures, and reproducible records</small></span><span class="node-state">03</span></button>
                            <button class="architecture-node" type="button" data-architecture-node="review"><span class="node-marker">R</span><span><strong>Review & reflection</strong><small>Feedback, version history, limitations, and validity</small></span><span class="node-state">04</span></button>
                            <button class="architecture-node" type="button" data-architecture-node="contribution"><span class="node-marker">C</span><span><strong>Contribution</strong><small>Findings, implications, artifacts, and next work</small></span><span class="node-state">05</span></button>
                        </div>
                    </div>
                    <p class="architecture-detail" id="architecture-detail">Select a node to keep the manuscript structure connected to the proposal.</p>
                </section>

                <!-- Workspace Grid: Editor + Multi-Tab Collaboration Hub -->
                <div class="workspace-grid" id="workspace-grid">
                    <!-- Manuscript Editor Panel -->
                    <section class="editor-panel" id="stage-presentation">
                        <!-- Formatting & Academic Tools Toolbar -->
                        <div class="editor-toolbar">
                            <div class="toolbar-group">
                                <button type="button" data-command="bold" title="Bold (Ctrl+B)"><strong>B</strong></button>
                                <button type="button" data-command="italic" title="Italic (Ctrl+I)"><em>I</em></button>
                                <button type="button" data-command="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                                <span class="toolbar-separator"></span>
                                <button type="button" data-format-block="h2" title="Section Heading (H2)">H2</button>
                                <button type="button" data-format-block="h3" title="Sub-section Heading (H3)">H3</button>
                                <button type="button" data-command="insertUnorderedList" title="Bulleted List">☷</button>
                                <button type="button" data-command="insertOrderedList" title="Numbered List">1.</button>
                                <button type="button" data-format-block="blockquote" title="Blockquote">“</button>
                            </div>

                            <div class="toolbar-academic-group">
                                <button type="button" class="toolbar-chip-btn" id="btn-open-templates" title="Choose a ready-to-use research paper template">
                                    <span class="icon">📄</span> Templates
                                </button>
                                <button type="button" class="toolbar-chip-btn toolbar-chip-excel" id="btn-open-excel-matrix" title="Open Microsoft Excel Methodology & Section Matrix">
                                    <span class="icon" style="color: #107c41;">📊</span> Excel Matrix
                                </button>
                                <button type="button" class="toolbar-chip-btn" id="btn-open-media" title="Insert figures, diagrams, or media">
                                    <span class="icon">🖼</span> Insert Figure
                                </button>
                                <button type="button" class="toolbar-chip-btn" id="btn-open-citations" title="Insert citation in context">
                                    <span class="icon">“</span> Cite
                                </button>
                                <button type="button" class="toolbar-chip-btn" id="btn-append-bibliography" title="Generate bibliography at end of paper">
                                    <span class="icon">📚</span> Bibliography
                                </button>
                            </div>

                            <div class="toolbar-save-group">
                                <span class="version-label" id="version-label">Version 0</span>
                                <button class="button button-primary" id="save-version" type="button">Save version</button>
                            </div>
                        </div>

                        <!-- Paper Margins & Layout Quick Control Strip -->
                        <div class="paper-layout-strip" id="paper-layout-strip">
                            <div class="strip-item">
                                <span class="strip-label">Margin:</span>
                                <div class="strip-buttons" id="margin-buttons">
                                    <button type="button" class="strip-btn is-active" data-margin="normal" title="Standard 1.0 inch (25.4mm) academic margin">Normal (1")</button>
                                    <button type="button" class="strip-btn" data-margin="narrow" title="Narrow 0.5 inch (12.7mm)">Narrow (0.5")</button>
                                    <button type="button" class="strip-btn" data-margin="moderate" title="Moderate 0.75 inch (19mm)">Moderate (0.75")</button>
                                    <button type="button" class="strip-btn" data-margin="wide" title="Wide 1.25 inch (31.7mm) with room for review notes">Wide (1.25")</button>
                                </div>
                            </div>
                            <div class="strip-item">
                                <span class="strip-label">Columns:</span>
                                <div class="strip-buttons" id="column-buttons">
                                    <button type="button" class="strip-btn is-active" data-column="single_column" title="Single column standard thesis layout">Single Col</button>
                                    <button type="button" class="strip-btn" data-column="two_column" title="Two-column IEEE/ACM conference layout">Two Col</button>
                                </div>
                            </div>
                            <div class="strip-item">
                                <span class="strip-label">Font:</span>
                                <div class="strip-buttons" id="font-buttons">
                                    <button type="button" class="strip-btn is-active" data-font="serif" title="Academic Serif">Serif</button>
                                    <button type="button" class="strip-btn" data-font="sans" title="Modern Sans">Sans</button>
                                    <button type="button" class="strip-btn" data-font="mono" title="Technical Mono">Mono</button>
                                </div>
                            </div>
                            <div class="strip-item">
                                <span class="strip-label">Spacing:</span>
                                <div class="strip-buttons" id="spacing-buttons">
                                    <button type="button" class="strip-btn data-spacing="1.0" title="Single spacing">1.0</button>
                                    <button type="button" class="strip-btn is-active" data-spacing="1.5" title="1.5 spacing standard thesis">1.5</button>
                                    <button type="button" class="strip-btn" data-spacing="2.0" title="Double spacing APA submission">2.0</button>
                                </div>
                            </div>
                        </div>

                        <!-- Workspace View Switcher: Split / Excel Matrix Only / Manuscript Only -->
                        <div class="workspace-view-switcher" id="workspace-view-switcher" role="tablist">
                            <span class="view-switch-label">Workspace View:</span>
                            <div class="view-switch-buttons">
                                <button type="button" class="view-switch-btn is-active" data-view-mode="split" title="View Manuscript and Excel Matrix side-by-side / stacked">
                                    <span class="btn-icon">📑</span> Split View (Matrix + Canvas)
                                </button>
                                <button type="button" class="view-switch-btn" data-view-mode="excel" title="Full Focus on Microsoft Excel Methodology Matrix">
                                    <span class="btn-icon">📊</span> Excel Matrix Only
                                </button>
                                <button type="button" class="view-switch-btn" data-view-mode="canvas" title="Focus exclusively on the Manuscript Canvas">
                                    <span class="btn-icon">📄</span> Manuscript Canvas Only
                                </button>
                            </div>
                        </div>

                        <!-- Microsoft Excel Methodology & Section Governance Matrix Section -->
                        <section class="excel-matrix-wrapper" id="excel-matrix-wrapper" aria-label="Microsoft Excel Methodology and Section Governance Matrix">
                            <div class="excel-window-frame">
                                <!-- Excel Fluent Title Bar -->
                                <div class="excel-title-bar">
                                    <div class="excel-title-left">
                                        <span class="excel-app-icon" title="Microsoft Excel Spreadsheet">📊</span>
                                        <span class="excel-doc-title">Co-Auth_Methodology_Matrix.xlsx</span>
                                        <span class="excel-badge-autosave"><i class="autosave-dot"></i> AutoSave On</span>
                                        <span class="excel-badge-ai"><span class="ai-sparkle">✦</span> Alice (Camber AI): Ready</span>
                                    </div>
                                    <div class="excel-title-center">
                                        <span class="excel-header-subcopy">AI Agent Recommends Methodologies & Sections · Author Can Freely Change & Override Rules</span>
                                    </div>
                                    <div class="excel-title-right">
                                        <button type="button" class="excel-window-btn" id="btn-excel-toggle-collapse" title="Collapse / Expand Spreadsheet">−</button>
                                    </div>
                                </div>

                                <!-- Excel Ribbon Bar -->
                                <div class="excel-ribbon-bar">
                                    <div class="excel-ribbon-tabs">
                                        <button type="button" class="excel-ribbon-tab is-active" data-excel-tab="methodology">Methodology & Rules</button>
                                        <button type="button" class="excel-ribbon-tab" data-excel-tab="ai">AI Agent (Alice)</button>
                                        <button type="button" class="excel-ribbon-tab" data-excel-tab="author">Author Rules</button>
                                        <button type="button" class="excel-ribbon-tab" data-excel-tab="export">Export & Sync</button>
                                    </div>
                                    <div class="excel-ribbon-actions">
                                        <button type="button" class="excel-action-btn excel-action-primary" id="btn-excel-ai-recommend" title="Alice AI analyzes the study and recommends optimal section methodologies and rigor rules">
                                            <span class="btn-icon">🤖</span>
                                            <span class="btn-text">
                                                <strong>Ask Alice (AI Recommend)</strong>
                                                <small>Auto-suggest methodology & rules</small>
                                            </span>
                                        </button>
                                        <button type="button" class="excel-action-btn excel-action-accent" id="btn-excel-apply-canvas" title="Apply the Excel sections, methodologies, and author rules directly to the active paper canvas">
                                            <span class="btn-icon">⚡</span>
                                            <span class="btn-text">
                                                <strong>Apply to Manuscript</strong>
                                                <small>Sync sections & author rules</small>
                                            </span>
                                        </button>
                                        <button type="button" class="excel-action-btn" id="btn-excel-insert-table" title="Insert publication-ready Methodology Matrix Table into paper">
                                            <span class="btn-icon">📋</span>
                                            <span class="btn-text">
                                                <strong>Insert Table in Paper</strong>
                                                <small>Embed academic Table 1</small>
                                            </span>
                                        </button>
                                        <div class="excel-action-separator"></div>
                                        <button type="button" class="excel-tool-icon-btn" id="btn-excel-add-row" title="Insert new section row below">+ Row</button>
                                        <button type="button" class="excel-tool-icon-btn" id="btn-excel-delete-row" title="Delete selected row">− Row</button>
                                        <button type="button" class="excel-tool-icon-btn" id="btn-excel-reset" title="Reset to standard methodology outline">↺ Reset</button>
                                        <button type="button" class="excel-tool-icon-btn" id="btn-excel-export" title="Export spreadsheet as CSV">📥 CSV</button>
                                    </div>
                                </div>

                                <!-- Excel Formula Bar -->
                                <div class="excel-formula-bar">
                                    <div class="excel-name-box" id="excel-name-box" title="Active Cell Address">D1</div>
                                    <div class="excel-formula-divider"></div>
                                    <div class="excel-fx-label" title="Formula & Rule Editor">fx</div>
                                    <input type="text" class="excel-formula-input" id="excel-formula-input" placeholder="Click any cell or edit author rule here..." autocomplete="off" spellcheck="false">
                                    <span class="excel-formula-hint">Author Rule Override (Click to edit)</span>
                                </div>

                                <!-- Excel Grid Scroll Viewport -->
                                <div class="excel-grid-viewport">
                                    <table class="excel-table" id="excel-matrix-table">
                                        <thead>
                                            <tr>
                                                <th class="excel-th-corner" title="Select all cells"></th>
                                                <th class="excel-th-col" data-col="A" style="min-width: 190px;">A<span class="col-name">Section / Chapter</span></th>
                                                <th class="excel-th-col" data-col="B" style="min-width: 210px;">B<span class="col-name">Methodology & Paradigm</span></th>
                                                <th class="excel-th-col" data-col="C" style="min-width: 250px;">C<span class="col-name">AI Recommendation (Alice)</span></th>
                                                <th class="excel-th-col excel-th-author" data-col="D" style="min-width: 280px;">D<span class="col-name">Author Rules & Overrides (You Change)</span></th>
                                                <th class="excel-th-col" data-col="E" style="min-width: 100px;">E<span class="col-name">Words</span></th>
                                                <th class="excel-th-col" data-col="F" style="min-width: 85px;">F<span class="col-name">Cites</span></th>
                                                <th class="excel-th-col" data-col="G" style="min-width: 130px;">G<span class="col-name">Status</span></th>
                                                <th class="excel-th-col" data-col="H" style="min-width: 75px;">H<span class="col-name">Action</span></th>
                                            </tr>
                                        </thead>
                                        <tbody id="excel-tbody">
                                            <!-- Dynamic Rows populated by workspace.js -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Excel Sheet Tabs & Status Bar -->
                                <div class="excel-bottom-bar">
                                    <div class="excel-sheet-tabs">
                                        <button type="button" class="excel-sheet-tab is-active" data-sheet="1">
                                            <span class="sheet-icon">📑</span> Sheet1: Methodology & Rules
                                        </button>
                                        <button type="button" class="excel-sheet-tab" data-sheet="2">
                                            <span class="sheet-icon">📑</span> Sheet2: Evaluation Metrics
                                        </button>
                                        <button type="button" class="excel-new-sheet-btn" id="btn-excel-new-sheet" title="Add Sheet">+</button>
                                    </div>
                                    <div class="excel-status-bar" id="excel-status-bar">
                                        <span class="status-indicator-ready">READY</span>
                                        <span class="status-sep">|</span>
                                        <span id="excel-stat-sections">6 Sections</span>
                                        <span class="status-sep">|</span>
                                        <span id="excel-stat-words">Sum Words: 10,700</span>
                                        <span class="status-sep">|</span>
                                        <span id="excel-stat-rules">Rules: 6 Active (Click any cell in Column D to change)</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Authentic Paper Sheet Container -->
                        <div class="paper-sheet-viewport">
                            <div class="paper-sheet" id="paper-sheet">
                                <!-- Running Paper Header -->
                                <div class="paper-running-head">
                                    <span class="running-title">{{ $project->title }} <span class="running-methodology" id="running-methodology">· Methodology pending</span></span>
                                    <span class="running-page">Page 1</span>
                                </div>

                                <!-- Editable Manuscript Canvas -->
                                <div class="editor-canvas" id="editor-canvas" contenteditable="true" role="textbox" aria-label="Thesis manuscript editor" spellcheck="true">
                                    <h2>1. Introduction & Research Problem</h2>
                                    <p>What happens when thesis writers and supervisors share one living research space? Graduate and scientific research requires rigorous documentation, rapid feedback loops, and empirical validation.</p>
                                    <p>Use this canvas to shape your argument, capture evidence, cite in context <span class="cite-ref" contenteditable="false" data-cite-key="hevner2004">[1]</span>, and leave a verifiable trail for peer reviewers and supervisors.</p>

                                    <h2>2. Theoretical Framework & Methodology</h2>
                                    <p>Ground your inquiry in a clearly articulated paradigm <span class="cite-ref" contenteditable="false" data-cite-key="creswell2018">[2]</span>. State whether your claims stem from inductive qualitative themes, deductive statistical hypotheses, or iterative artifact construction.</p>

                                    <figure class="paper-figure" contenteditable="false">
                                        <img src="https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1000&q=80" alt="Collaborative Research Framework">
                                        <figcaption><strong class="fig-num">Figure 1:</strong> Collaborative Research Framework illustrating triangulation between qualitative feedback, quantitative milestones, and automated document synthesis in distributed teams.</figcaption>
                                    </figure>

                                    <h2>3. Working Notes & Empirical Evidence</h2>
                                    <p>Document observations, system benchmarks, participant transcripts, and open questions here as the project develops.</p>
                                </div>

                                <!-- Paper Running Footer with Live Metrics -->
                                <div class="paper-running-foot">
                                    <div class="paper-meta-stats" id="paper-stats" title="Real-time academic manuscript metrics">
                                        <span id="stat-words">0 words</span>
                                        <span class="stat-sep">·</span>
                                        <span id="stat-chars">0 characters</span>
                                        <span class="stat-sep">·</span>
                                        <span id="stat-read-time">~1 min read</span>
                                    </div>
                                    <div class="paper-foot-tag">
                                        <span id="paper-principle">Research integrity · traceable evidence · reflective practice</span>
                                        <span class="stat-sep">·</span>
                                        <span>Confidential Working Draft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Multi-Tab Sidebar: AI Lab, Directory & Core, Team, Citations, Figures, Tasks, Feedback, History -->
                    <span id="stage-review" class="lifecycle-anchor" aria-hidden="true"></span>
                    <aside class="workspace-sidebar" id="workspace-sidebar">
                        <!-- Sidebar Navigation Tabs -->
                        <div class="sidebar-tab-row" role="tablist">
                            <button type="button" class="sidebar-tab sidebar-tab-ai is-active" data-sidebar-tab="ai" title="AI Research Copilot">✦ AI Lab</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="directory" title="Active Directory & Architecture">📁 Directory</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="team" title="Collaborators & Live Presence">👥 Team</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="citations" title="Citations & Bibliography">📚 Citations</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="figures" title="Figures & Media Gallery">🖼 Figures</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="tasks" title="Task Board & Tasks">✓ Tasks</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="reviews" title="Supervisor Feedback Desk">💬 Feedback</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="history" title="Version Snapshots">⏱ History</button>
                        </div>

                        <!-- TAB 1: AI Lab: persistent context-grounded research assistance -->
                        <div class="sidebar-panel ai-lab-panel is-active" id="tab-panel-ai">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Context-Grounded AI</p>
                                    <h2>Research Copilot</h2>
                                </div>
                                <span class="ai-live-dot"><i></i>Ready</span>
                            </div>
                            <p class="panel-subcopy">Grounded in project question, method, tasks, feedback, papers, and verified bibliography with strict zero-hallucination guards.</p>
                            <div class="ai-provider-card">
                                <div class="ai-personal-logo ai-logo-orchestrator" aria-hidden="true">✦</div>
                                <div class="ai-provider-copy"><strong id="ai-active-identity">Google Gemini 2.5 Flash</strong><small id="ai-lab-model">1,000,000+ token context · Free tier</small></div>
                                <span class="ai-collaboration-state"><i></i>Encrypted</span>
                            </div>
                            <div class="ai-collaboration-card">
                                <div class="ai-collaboration-heading"><strong>AI Studio Team</strong><span>Multi-agent context</span></div>
                                <p>Configured models operate with zero cross-talk. All prompts and outputs are TLS 1.3 encrypted in transit and AES-256 encrypted at rest.</p>
                                <div class="ai-agent-roster" aria-label="Configured AI collaborators">
                                    <div class="ai-agent is-active" data-ai-agent="alice">
                                        <span class="ai-personal-logo" style="background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff; font-weight: 700;">A</span>
                                        <span><strong>Alice (Camber)</strong><small>Thesis Co-Pilot & Critic</small></span>
                                        <i></i>
                                    </div>
                                    <div class="ai-agent" data-ai-agent="gemini">
                                        <span class="ai-personal-logo ai-logo-gemini">G</span>
                                        <span><strong>Google Gemini</strong><small>Deep reasoning & 1M context</small></span>
                                        <i></i>
                                    </div>
                                    <div class="ai-agent" data-ai-agent="llama">
                                        <span class="ai-personal-logo ai-logo-llama">L</span>
                                        <span><strong>Llama 3.3 (Groq)</strong><small>Fast generation</small></span>
                                        <i></i>
                                    </div>
                                    <div class="ai-agent" data-ai-agent="deepseek">
                                        <span class="ai-personal-logo ai-logo-deepseek">D</span>
                                        <span><strong>DeepSeek-R1</strong><small>Reasoning & review</small></span>
                                        <i></i>
                                    </div>
                                    <div class="ai-agent" data-ai-agent="qwen">
                                        <span class="ai-personal-logo ai-logo-qwen">Q</span>
                                        <span><strong>Qwen 2.5</strong><small>Code & benchmarks</small></span>
                                        <i></i>
                                    </div>
                                </div>
                            </div>
                            <label class="ai-prompt-label" for="ai-lab-prompt">What should we work on?</label>
                            <textarea id="ai-lab-prompt" class="ai-prompt" rows="3" placeholder="Ask for a literature gap, methodology check, next step, or evidence review…"></textarea>
                            <div class="ai-quick-actions">
                                <button type="button" class="ai-quick-action" data-ai-instruction="Summarize the current project state and recommend the next three research actions.">Summarize project</button>
                                <button type="button" class="ai-quick-action" data-ai-instruction="Identify unresolved risks, feedback, or evidence gaps in this research project.">Find research gaps</button>
                                <button type="button" class="ai-quick-action" data-ai-instruction="Suggest a rigorous structure for the next manuscript section based on the current methodology.">Plan next section</button>
                            </div>
                            <button class="button button-ai ai-lab-submit" id="ai-lab-submit" type="button">Run research copilot <span>⌘↵</span></button>
                            <div class="ai-lab-output" id="ai-lab-output" role="status"><span class="ai-output-placeholder">Your grounded response will appear here.</span></div>
                        </div>

                        <!-- TAB 2: Active Local Directory & Core Architecture Defense -->
                        <div class="sidebar-panel" id="tab-panel-directory">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Local Environment</p>
                                    <h2>Active Directory</h2>
                                </div>
                                <button class="button button-quiet button-sm" type="button" id="btn-run-arch-audit">⚡ Audit Now</button>
                            </div>
                            <p class="panel-subcopy">System infrastructure, active project files, and cryptographic data structures protecting the core architecture.</p>

                            <div class="directory-card">
                                <div class="directory-item">
                                    <span>Active Local Directory</span>
                                    <code id="env-active-directory">{{ base_path() }}</code>
                                </div>
                                <div class="directory-item">
                                    <span>Public Storage Link</span>
                                    <code id="env-storage-path">{{ public_path('storage') }}</code>
                                </div>
                                <div class="directory-item">
                                    <span>Database Connection</span>
                                    <code id="env-db-driver">{{ config('database.default') }} (Relational)</code>
                                </div>
                                <div class="directory-item">
                                    <span>PHP Runtime</span>
                                    <code>{{ PHP_VERSION }} (Strict Types)</code>
                                </div>
                                <div class="directory-item">
                                    <span>WebSockets Engine</span>
                                    <code>Laravel Reverb (:8080)</code>
                                </div>
                            </div>

                            <div class="side-heading" style="margin-top: 14px;">
                                <div>
                                    <p class="eyebrow">AI API Protection</p>
                                    <h2>Encryption & Access</h2>
                                </div>
                                <span class="arch-badge arch-pass">FREE ACCESS</span>
                            </div>
                            <div class="directory-card">
                                <div class="directory-item">
                                    <span>AI Provider</span>
                                    <code id="env-ai-provider">Google Gemini / Groq</code>
                                </div>
                                <div class="directory-item">
                                    <span>Active Model</span>
                                    <code id="env-ai-model">gemini-2.5-flash</code>
                                </div>
                                <div class="directory-item">
                                    <span>Transport Encryption</span>
                                    <code>TLS 1.3 (HTTPS Enforced)</code>
                                </div>
                                <div class="directory-item">
                                    <span>At-Rest Encryption</span>
                                    <code>AES-256-CBC Crypt</code>
                                </div>
                                <div class="directory-item">
                                    <span>User Entitlement</span>
                                    <code style="color: var(--teal);">Free for all authenticated users</code>
                                </div>
                                <div class="directory-item" style="flex-direction: column; align-items: flex-start; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                                        <span>AI API Availability</span>
                                        <button type="button" class="button button-quiet button-sm" id="btn-refresh-ai-status" style="padding: 2px 6px; font-size: 0.7rem;">↻ Check</button>
                                    </div>
                                    <div id="ai-providers-quick-status" style="width: 100%; display: flex; flex-direction: column; gap: 5px; font-size: 0.75rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(124, 58, 237, 0.08); padding: 5px 8px; border-radius: 4px; border-left: 3px solid #7c3aed;">
                                            <span><strong>★ Alice (Camber Cloud)</strong></span>
                                            <span id="status-alice" class="arch-badge arch-pass" style="font-size: 0.65rem;">ONLINE</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.03); padding: 5px 8px; border-radius: 4px;">
                                            <span>Google Gemini 2.5 Flash</span>
                                            <span id="status-gemini" class="arch-badge arch-warn" style="font-size: 0.65rem;">REACHABLE (Key Req)</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.03); padding: 5px 8px; border-radius: 4px;">
                                            <span>Groq Cloud (Llama 3.3)</span>
                                            <span id="status-groq" class="arch-badge arch-warn" style="font-size: 0.65rem;">REACHABLE (Key Req)</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.03); padding: 5px 8px; border-radius: 4px;">
                                            <span>Ollama (Local Private)</span>
                                            <span id="status-ollama" class="arch-badge" style="font-size: 0.65rem; background: var(--line);">OFFLINE</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="side-heading" style="margin-top: 14px;">
                                <div>
                                    <p class="eyebrow">Data Structures Defense</p>
                                    <h2>Core Architecture</h2>
                                </div>
                                <span class="arch-badge arch-pass" id="arch-global-status">PASS</span>
                            </div>
                            <div class="arch-audit-grid" id="arch-audit-list">
                                <div class="arch-audit-item">
                                    <div><strong>Binary Tree</strong><small>Merkle Root Hash Indexing</small></div>
                                    <span class="arch-badge arch-pass">Verified</span>
                                </div>
                                <div class="arch-audit-item">
                                    <div><strong>Recursion</strong><small>Outline & Hierarchy Depth Guard</small></div>
                                    <span class="arch-badge arch-pass">Verified</span>
                                </div>
                                <div class="arch-audit-item">
                                    <div><strong>Graph Mapping</strong><small>Tree-to-DAG & Cycle Defense</small></div>
                                    <span class="arch-badge arch-pass">Verified</span>
                                </div>
                                <div class="arch-audit-item">
                                    <div><strong>Linked List</strong><small>Tamper-Evident Version Ledger</small></div>
                                    <span class="arch-badge arch-pass">Verified</span>
                                </div>
                                <div class="arch-audit-item">
                                    <div><strong>Linear Search</strong><small>Sequential Audit Stream Scan</small></div>
                                    <span class="arch-badge arch-pass">Verified</span>
                                </div>
                                <div class="arch-audit-item">
                                    <div><strong>Cryptographic Hash</strong><small>SHA-256 HMAC State Seal</small></div>
                                    <span class="arch-badge arch-pass">Verified</span>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: Team & Collaboration (Individual & 2/3 Group Seamless Work) -->
                        <div class="sidebar-panel" id="tab-panel-team">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Project Team</p>
                                    <h2>Collaborators</h2>
                                </div>
                                <button class="button button-quiet button-sm" type="button" id="btn-invite-member" title="Invite a co-author or supervisor">＋ Invite</button>
                            </div>
                            <div class="working-directory-banner">
                                <span class="dir-icon">📁</span>
                                <div>
                                    <strong>Active Working Directory</strong>
                                    <small id="workspace-sync-status">Real-time sync active · 3 members</small>
                                </div>
                            </div>
                            <div id="member-list" class="member-list">
                                <div class="loading-line">Loading team…</div>
                            </div>
                            <div class="team-hint-box">
                                <p><strong>Seamless multi-user collaboration:</strong> Co-authors and supervisors can view drafts, post inline feedback, and save version snapshots without overwriting each other's work.</p>
                            </div>
                        </div>

                        <!-- TAB 4: In-Context Citations & References -->
                        <div class="sidebar-panel" id="tab-panel-citations">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Bibliography</p>
                                    <h2>Citations Library</h2>
                                </div>
                                <button class="button button-quiet button-sm" type="button" id="btn-add-citation">＋ Add citation</button>
                            </div>
                            <p class="panel-subcopy">Insert citations at your cursor position or generate the bibliography list.</p>
                            <div class="citation-actions-row">
                                <button type="button" class="button button-primary" id="btn-generate-references-action" style="flex: 1;">📚 Append References</button>
                                <a href="/api/v1/projects/{{ $project->id }}/citations/export?download=1" class="button button-quiet" id="btn-export-bibtex" download title="Download BibTeX (.bib) file for LaTeX / Overleaf">💾 Export .bib</a>
                            </div>
                            <div class="overleaf-guide">
                                <strong>Overleaf handoff</strong>
                                <p>Export the <code>.bib</code> file, upload it to Overleaf, and cite with the same keys used in this manuscript.</p>
                                <ol><li>Download <code>references.bib</code>.</li><li>Upload it beside your <code>.tex</code> file.</li><li>Choose a matching APA, IEEE, or Chicago bibliography style.</li></ol>
                                <span>Source keys stay traceable across Co-Auth and Overleaf.</span>
                            </div>
                            <div class="citation-filter-wrap" style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                <input type="search" class="search-input" id="citation-search" placeholder="🔍 Filter citations..." style="width: 100%;">
                            </div>
                            <div id="citation-list" class="citation-list">
                                <div class="loading-line">Loading citations…</div>
                            </div>
                        </div>

                        <!-- TAB 5: Figures & Media Gallery -->
                        <div class="sidebar-panel" id="tab-panel-figures">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Visual Evidence</p>
                                    <h2>Figures & Media</h2>
                                </div>
                                <button class="button button-quiet button-sm" type="button" id="btn-upload-figure-tab">＋ Add figure</button>
                            </div>
                            <p class="panel-subcopy">Embed diagrams, charts, and plots directly into your manuscript.</p>
                            <div id="media-list" class="media-gallery">
                                <div class="loading-line">Loading media gallery…</div>
                            </div>
                        </div>

                        <!-- TAB 6: Shared Work / Task Board -->
                        <div class="sidebar-panel" id="tab-panel-tasks">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Shared Work</p>
                                    <h2>Task Board</h2>
                                </div>
                                <span id="task-count" class="review-count">0</span>
                            </div>
                            <form id="task-form" class="inline-form">
                                <input name="title" required placeholder="Add a research milestone or task…">
                                <button class="icon-button" type="submit" title="Add task">＋</button>
                            </form>
                            <div id="task-list" class="task-list">
                                <div class="loading-line">Loading tasks…</div>
                            </div>
                        </div>

                        <!-- TAB 7: Supervisor Desk / Feedback Loop -->
                        <div class="sidebar-panel" id="tab-panel-reviews">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Supervisor Desk</p>
                                    <h2>Feedback Loop</h2>
                                </div>
                                <span id="feedback-count" class="review-count">0</span>
                            </div>
                            <div id="feedback-list" class="feedback-list">
                                <div class="loading-line">Loading feedback…</div>
                            </div>
                            <form id="feedback-form" class="feedback-form">
                                <textarea name="body" required rows="2" placeholder="Leave review feedback or supervisor note…"></textarea>
                                <button class="button button-primary" type="submit">Post feedback</button>
                            </form>
                        </div>

                        <!-- TAB 8: Document Trail / Version History -->
                        <div class="sidebar-panel" id="tab-panel-history">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Document Trail</p>
                                    <h2>Version History</h2>
                                </div>
                            </div>
                            <p class="panel-subcopy">Every save captures an immutable linked-list snapshot. You can restore previous drafts anytime.</p>
                            <div id="version-history" class="version-history">
                                <div class="loading-line">No versions yet</div>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>

        <!-- ================= MODALS & DIALOGS ================= -->

        <!-- 0. Full Project Instructions, Administrative Biography & Operations Manual Modal -->
        <dialog class="app-dialog instructions-dialog" id="instructions-dialog">
            <div class="dialog-content-large" style="padding: 24px;">
                <button class="dialog-close" type="button" data-close-dialog="instructions-dialog" aria-label="Close">×</button>
                <p class="eyebrow">System Manual & Specification Blueprint</p>
                <h2>Co-Auth Platform Instructions & Administrative Biography</h2>
                <p class="dialog-copy">Complete structural reference, operational workflows, epistemological definitions, and architectural security parameters for graduate thesis teams.</p>

                <nav class="guide-tabs-nav" role="tablist">
                    <button type="button" class="guide-tab-btn is-active" data-guide-target="tab-guide-bio">Biography & Mandate</button>
                    <button type="button" class="guide-tab-btn" data-guide-target="tab-guide-methodologies">Formatted Methodologies</button>
                    <button type="button" class="guide-tab-btn" data-guide-target="tab-guide-architecture">Architecture & CS Defense</button>
                    <button type="button" class="guide-tab-btn" data-guide-target="tab-guide-ai">AI API & Encryption</button>
                    <button type="button" class="guide-tab-btn" data-guide-target="tab-guide-workflows">Workflows & Export</button>
                </nav>

                <!-- GUIDE TAB 1: Biography & Mandate -->
                <div class="guide-tab-pane is-active" id="tab-guide-bio">
                    <div class="guide-card">
                        <h3>Administrative Metadata & System Provenance</h3>
                        <p><strong>Co-Auth</strong> is an enterprise-grade collaborative research studio engineered specifically for graduate researchers, doctoral candidates, co-authors, and university thesis advisors. It resolves the "Semantic Vacuum" of contemporary academic drafting by bridging physical paper sheet typesetting with context-aware AI engines using the Model Context Protocol (MCP).</p>
                        <div class="guide-meta-grid">
                            <div class="guide-meta-item">
                                <strong>Target Degrees</strong>
                                <span>Master's Thesis, PhD Dissertations, Capstone Projects</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>Core Architecture</strong>
                                <span>Laravel 13.x · PHP 8.4+ · Reverb WebSockets (:8080)</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>Active Local Directory</strong>
                                <code>{{ base_path() }}</code>
                            </div>
                            <div class="guide-meta-item">
                                <strong>Relational Database</strong>
                                <span>{{ config('database.default') }} (Strict Foreign Keys & Audits)</span>
                            </div>
                        </div>
                    </div>
                    <div class="guide-card">
                        <h3>Role Responsibilities in 2-3 Person Research Teams</h3>
                        <p><strong>Member 1 (Lead Architecture & AI Protocol):</strong> Implements the Model Context Protocol (<code>ResearchServer</code>, <code>ProjectContextTool</code>), manages backend gateways, enforces tenant security fences, and validates ORCID identifiers.</p>
                        <p><strong>Member 2 (Frontend & Real-Time Engine):</strong> Authors the authentic paper sheet canvas, line-spacing and margin geometry, Reverb presence heartbeats, and responsive reviewer views.</p>
                        <p><strong>Member 3 (Database, Citations & QA):</strong> Manages relational migrations, in-context citation indexing, BibTeX export serializers, and executes the 65-test automated verification suite.</p>
                    </div>
                </div>

                <!-- GUIDE TAB 2: Formatted Methodologies -->
                <div class="guide-tab-pane" id="tab-guide-methodologies">
                    <div class="guide-card">
                        <h3>Epistemological Frameworks & Theoretical Foundations</h3>
                        <p>Academic rigor begins with ontological alignment. Each methodology represents a distinct premise regarding the nature of reality, truth, and evidence:</p>
                    </div>
                    
                    <div class="guide-card">
                        <h3>1. Qualitative Research (Constructivism / Interpretivism)</h3>
                        <p><strong>Seminal Theorists:</strong> Egon Guba & Yvonna Lincoln (<em>Naturalistic Inquiry</em>, 1985), John W. Creswell, Kathy Charmaz.</p>
                        <p><strong>Epistemological Stance:</strong> Reality is socially constructed through lived experience, cultural context, and subjective meaning. Truth is emergent, contextual, and pluralistic.</p>
                        <p><strong>Core Question Formula:</strong> <em>"How or why does [phenomenon] occur within [social/organizational context]?"</em></p>
                        <p><strong>Primary Evidence:</strong> In-depth semi-structured interviews, focus group transcripts, field observation, grounded theory open/axial coding.</p>
                        <p><strong>Rigor Criteria:</strong> Lincoln & Guba's Trustworthiness Framework: Credibility (member-checking), Transferability (thick description), Dependability (audit trail), Confirmability (reflexivity).</p>
                    </div>

                    <div class="guide-card">
                        <h3>2. Quantitative Research (Positivism / Post-Positivism & Empiricism)</h3>
                        <p><strong>Seminal Theorists:</strong> Karl Popper (<em>Logic of Scientific Discovery</em>, 1959), Auguste Comte, Donald Campbell & Julian Stanley (1963).</p>
                        <p><strong>Epistemological Stance:</strong> An objective reality exists independently of human observation, governed by natural laws and measurable causal relationships.</p>
                        <p><strong>Core Question Formula:</strong> <em>"What is the causal effect or significant relationship between Variable X and Variable Y, controlling for Z?"</em></p>
                        <p><strong>Primary Evidence:</strong> Randomized controlled trials (RCT), closed-ended Likert surveys, system telemetry logs, ANOVA, multiple regression.</p>
                        <p><strong>Rigor Criteria:</strong> Internal validity, external validity (generalizability), statistical significance (p &lt; 0.05), construct reliability (Cronbach's alpha &gt; 0.70).</p>
                    </div>

                    <div class="guide-card">
                        <h3>3. Mixed Methods Research (Pragmatism & Triangulation)</h3>
                        <p><strong>Seminal Theorists:</strong> John Dewey (<em>Logic: The Theory of Inquiry</em>, 1938), C.S. Peirce, Abbas Tashakkori & Charles Teddlie, John Creswell.</p>
                        <p><strong>Epistemological Stance:</strong> Rejects dogmatic either/or paradigms. Truth is judged by practical utility and what works in solving problems.</p>
                        <p><strong>Core Question Formula:</strong> <em>"To what extent and in what ways does qualitative contextual depth explain quantitative statistical outcomes?"</em></p>
                        <p><strong>Primary Evidence:</strong> Convergent parallel datasets, sequential explanatory designs (Quant $\to$ Qual), integrated joint displays.</p>
                        <p><strong>Rigor Criteria:</strong> Design fidelity, interpretive consistency, cross-methodological triangulation, dialectical integration.</p>
                    </div>

                    <div class="guide-card">
                        <h3>4. Design Science Research (Sciences of the Artificial / Artifact & Utility)</h3>
                        <p><strong>Seminal Theorists:</strong> Herbert A. Simon (<em>The Sciences of the Artificial</em>, 1969), Alan R. Hevner (2004), Ken Peffers (2007).</p>
                        <p><strong>Epistemological Stance:</strong> Knowledge is produced through creating, demonstrating, and empirically evaluating innovative technological artifacts solving practical problems.</p>
                        <p><strong>Core Question Formula:</strong> <em>"How can an innovative [Artifact / Architecture] be engineered to solve [Class of Problems] with superior utility?"</em></p>
                        <p><strong>Primary Evidence:</strong> Functional software prototypes, algorithmic benchmark logs, latency/throughput metrics, usability evaluations.</p>
                        <p><strong>Rigor Criteria:</strong> Hevner's 7 Guidelines: Problem Relevance, Design as an Artifact, Design Evaluation (Utility Theory), Research Contributions, Research Rigor.</p>
                    </div>

                    <div class="guide-card">
                        <h3>5. Systematic Literature Review (PRISMA Framework / Critical Realism)</h3>
                        <p><strong>Seminal Theorists:</strong> David Moher et al. (PRISMA 2009 / 2020), Kitchenham & Charters (<em>Guidelines for SLRs in Software Engineering</em>, 2007).</p>
                        <p><strong>Epistemological Stance:</strong> Cumulative scientific knowledge represents our closest approximation of reality. Systematic synthesis uncovers the consensus and boundary frontiers of knowledge.</p>
                        <p><strong>Core Question Formula:</strong> <em>"What is the synthesized state of scientific evidence regarding [Topic], what methodologies have been applied, and what critical research gaps remain?"</em></p>
                        <p><strong>Primary Evidence:</strong> Reproducible search query logs across IEEE Xplore, ACM DL, Scopus, PubMed, and Web of Science; formal inclusion/exclusion audit.</p>
                        <p><strong>Rigor Criteria:</strong> 27-item PRISMA checklist, transparent boolean query strings, dual-reviewer screening inter-rater reliability (Cohen's Kappa), risk-of-bias assessment.</p>
                    </div>

                    <div class="guide-card">
                        <h3>6. Action Research (Critical Social Theory & Cyclical Intervention)</h3>
                        <p><strong>Seminal Theorists:</strong> Kurt Lewin (<em>Action Research and Minority Problems</em>, 1946), Stephen Kemmis & Robin McTaggart (1988), Peter Checkland.</p>
                        <p><strong>Epistemological Stance:</strong> Theory and practice must be united. Action is the source of knowledge; social and organizational systems can only be truly understood through iterative attempts to improve them.</p>
                        <p><strong>Core Question Formula:</strong> <em>"How can practitioners and researchers collaboratively intervene to transform and enhance [Organizational / Educational / Technical System]?"</em></p>
                        <p><strong>Primary Evidence:</strong> Intervention sprint logs, stakeholder debriefs, iterative process metrics, participant reflection diaries.</p>
                        <p><strong>Rigor Criteria:</strong> Catalytic validity (empowerment of participants), cyclical recoverability of the inquiry process, democratic validity, observable systemic transformation.</p>
                    </div>
                </div>

                <!-- GUIDE TAB 3: Architecture & CS Defense -->
                <div class="guide-tab-pane" id="tab-guide-architecture">
                    <div class="guide-card">
                        <h3>Core Architecture Defense: 6 Computer Science Structures</h3>
                        <p>Co-Auth implements six foundational data structures and algorithms in <code>App\Core\Architecture</code> to protect the platform's core architecture against data tampering, circular deadlocks, and index corruption:</p>
                        <div class="guide-meta-grid">
                            <div class="guide-meta-item">
                                <strong>1. Binary Tree (Merkle Root)</strong>
                                <span>Balanced binary search tree providing $O(\log n)$ citation and section lookup while computing a cryptographic Merkle root hash.</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>2. Recursion</strong>
                                <span>Recursively audits deep outline trees, enforces strict recursion-depth limits (max 20 levels), and prevents duplicate node injection.</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>3. Graph Mapping (DAG)</strong>
                                <span>Projects hierarchical outlines into a directed graph, executing 3-color DFS cycle detection to prevent dependency deadlocks.</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>4. Linked List</strong>
                                <span>Implements a tamper-evident revision ledger where each node holds a cryptographic <code>prevHash</code> pointer to the prior snapshot.</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>5. Linear Search</strong>
                                <span>Sequential $O(n)$ scanning engine across unindexed audit events, verifying time-series continuity as a fallback.</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>6. Cryptographic Hash</strong>
                                <span>Computes deterministic SHA-256 and HMAC-SHA256 signatures sealing project states against unauthorized modification.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GUIDE TAB 4: AI API & Encryption -->
                <div class="guide-tab-pane" id="tab-guide-ai">
                    <div class="guide-card">
                        <h3>AI API Architecture, Encryption & Free Access</h3>
                        <p><strong>Recommended Provider:</strong> <strong>Google Gemini API</strong> (via <code>gemini-2.5-flash</code>). Google AI Studio provides an extensive <strong>free tier</strong> (15 RPM / 1M TPM / 1,500 RPD) with an unprecedented <strong>1,000,000+ token context window</strong>, allowing entire 50-page manuscripts, full citation databases, and literature reviews to be evaluated in a single prompt without chunking errors.</p>
                        <div class="guide-meta-grid">
                            <div class="guide-meta-item">
                                <strong>Transport Encryption</strong>
                                <span>Strict HTTPS / TLS 1.3 enforced for all external AI endpoints via <code>AiGateway::validateTransportSecurity()</code>.</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>At-Rest Encryption</strong>
                                <span>Sensitive research prompts and AI outputs encrypted via Laravel's AES-256-CBC cryptographic vault (<code>Crypt</code>).</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>Zero Hallucination Protocol</strong>
                                <span>Zero-shot system constraints ground all summaries strictly in relational project records extracted via MCP.</span>
                            </div>
                            <div class="guide-meta-item">
                                <strong>Free Access Policy</strong>
                                <span><code>AI_FREE_FOR_ALL=true</code> guarantees all authenticated researchers have unrestricted, free AI assistance.</span>
                            </div>
                        </div>

                        <h4 style="margin-top: 1.25rem; margin-bottom: 0.5rem;">API Provider Hierarchy & Activation</h4>
                        <div class="theory-comparison-table-wrap">
                            <table class="theory-table" style="font-size: 0.8rem;">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Recommended Model</th>
                                        <th>Hierarchy Role</th>
                                        <th>Encryption & Protocol</th>
                                        <th>Free Tier Capacity</th>
                                        <th>Activation (.env)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>★ Google Gemini</strong></td>
                                        <td><code>gemini-2.5-flash</code></td>
                                        <td>Primary Academic Engine (1M token context window)</td>
                                        <td>HTTPS / TLS 1.3</td>
                                        <td>15 RPM / 1M TPM / 1,500 RPD (Free)</td>
                                        <td><code>GEMINI_API_KEY=AIzaSy...</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Groq Cloud</strong></td>
                                        <td><code>llama-3.3-70b-versatile</code></td>
                                        <td>Sub-second low latency inference (LPU hardware)</td>
                                        <td>HTTPS / TLS 1.3</td>
                                        <td>30 RPM / 14,400 RPD (Free)</td>
                                        <td><code>GROQ_API_KEY=gsk_...</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ollama Local</strong></td>
                                        <td><code>llama3.2</code></td>
                                        <td>100% Air-gapped / Offline confidential research</td>
                                        <td>Localhost loopback</td>
                                        <td>Unlimited local GPU/CPU compute</td>
                                        <td>Run <code>ollama serve</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- GUIDE TAB 5: Workflows & Export -->
                <div class="guide-tab-pane" id="tab-guide-workflows">
                    <div class="guide-card">
                        <h3>Export Workflows: Markdown, BibTeX & Print PDF</h3>
                        <p><strong>Markdown (.md):</strong> Click <em>"📥 Export .md"</em> to generate clean GitHub-flavored markdown with embedded image paths and citation chips formatted as traceable brackets.</p>
                        <p><strong>BibTeX (.bib):</strong> Click <em>"💾 Export .bib"</em> in the Citations tab to download an Overleaf-compatible bibliography file. Source keys match the in-text citation markers perfectly.</p>
                        <p><strong>Camera-Ready PDF:</strong> Click <em>"🖨 Print / PDF"</em> (or <code>Ctrl+P</code>) to trigger the browser's print engine. Custom <code>@media print</code> rules automatically strip all UI chrome, sidebars, and buttons, rendering an authentic publication-grade document formatted to standard A4 or US Letter dimensions.</p>
                    </div>
                </div>

                <div class="dialog-actions">
                    <button class="button button-primary" type="button" data-close-dialog="instructions-dialog">Close Manual</button>
                </div>
            </div>
        </dialog>

        <!-- 1. Methodology Theory Differentiation Modal -->
        <dialog class="app-dialog theory-dialog" id="theory-dialog">
            <div class="dialog-content-large" style="padding: 24px;">
                <button class="dialog-close" type="button" data-close-dialog="theory-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Epistemological & Ontological Guide</p>
                <h2>Differentiate Theories of Research Methodologies</h2>
                <p class="dialog-copy">Different methodologies make fundamentally different assumptions about reality, evidence, and truth. Choose the methodology whose theoretical stance matches your research question.</p>

                <div class="theory-comparison-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Methodology</th>
                                <th>Theoretical Foundation & Seminal Authors</th>
                                <th>Epistemology (Truth & Reality)</th>
                                <th>Core Research Question</th>
                                <th>Primary Evidence</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Qualitative</strong></td>
                                <td><span class="theory-pill">Constructivism / Interpretivism</span><br><small style="color: var(--muted);">Guba & Lincoln (1985), Creswell</small></td>
                                <td>Reality is socially constructed through lived experience, cultural context, and subjective meaning.</td>
                                <td><em>"How or why does a phenomenon occur within its natural context?"</em></td>
                                <td>Semi-structured interviews, observations, thematic codes.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Qualitative">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Quantitative</strong></td>
                                <td><span class="theory-pill">Positivism / Post-Positivism</span><br><small style="color: var(--muted);">Karl Popper (1959), Campbell</small></td>
                                <td>An objective reality exists independently of human observation; tested via deduction.</td>
                                <td><em>"What is the causal effect or relationship between variables?"</em></td>
                                <td>Randomized trials, surveys, regression, p-values.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Quantitative">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Mixed Methods</strong></td>
                                <td><span class="theory-pill">Pragmatism</span><br><small style="color: var(--muted);">John Dewey (1938), Peirce, Tashakkori</small></td>
                                <td>Truth is found in practical utility and pluralistic inquiry; triangulates numbers and narratives.</td>
                                <td><em>"What happened (quant), and why did participants react this way (qual)?"</em></td>
                                <td>Sequential explanatory design, convergent parallel datasets.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Mixed methods">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Design Science</strong></td>
                                <td><span class="theory-pill">Sciences of the Artificial</span><br><small style="color: var(--muted);">Herbert Simon (1969), Alan Hevner (2004)</small></td>
                                <td>Knowledge is produced through the creation, demonstration, and evaluation of innovative artifacts.</td>
                                <td><em>"How can we engineer a novel artifact to solve an unsolved problem?"</em></td>
                                <td>Software prototypes, algorithmic benchmarks, utility audits.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Design science">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Literature Review</strong></td>
                                <td><span class="theory-pill">PRISMA Framework</span><br><small style="color: var(--muted);">David Moher et al. (2009/2020), Kitchenham</small></td>
                                <td>Synthesizes cumulative scientific evidence without selection bias to uncover consensus and frontiers.</td>
                                <td><em>"What is the synthesized state of knowledge, and where are the gaps?"</em></td>
                                <td>Database query logs (IEEE/ACM/PubMed), inclusion audits.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Literature review">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Action Research</strong></td>
                                <td><span class="theory-pill">Critical Theory / Participatory</span><br><small style="color: var(--muted);">Kurt Lewin (1946), Kemmis & McTaggart</small></td>
                                <td>Theory and practice advance simultaneously through cyclical intervention: Plan → Act → Observe → Reflect.</td>
                                <td><em>"How can researchers and practitioners collaboratively improve a system?"</em></td>
                                <td>Organizational field logs, change interventions, reflective debriefs.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Action research">Apply</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="theory-dialog">Close guide</button>
                </div>
            </div>
        </dialog>

        <!-- 2. Research Paper Templates Modal -->
        <dialog class="app-dialog template-dialog" id="template-dialog">
            <div class="dialog-content-large" style="padding: 24px;">
                <button class="dialog-close" type="button" data-close-dialog="template-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Readymade Academic Scaffolding</p>
                <h2>Start from a Research Paper Template</h2>
                <p class="dialog-copy">Jumpstart your thesis without guessing section structures or reviewer expectations. Each template comes pre-filled with outline headings, guidance prompts, and checklist notes.</p>

                <div class="template-card-grid">
                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Scientific Standard</span>
                            <h3>IMRaD Research Manuscript</h3>
                        </div>
                        <p class="template-desc">The universal scientific framework: Introduction, Methodology, Results, and Discussion. Ideal for empirical science and biomedical research.</p>
                        <div class="template-sections">
                            <span>Abstract</span><span>1. Intro</span><span>2. Methods</span><span>3. Results</span><span>4. Discussion</span><span>5. References</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="imrad">Use IMRaD Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Engineering & CS</span>
                            <h3>IEEE / ACM Computer Science Paper</h3>
                        </div>
                        <p class="template-desc">Designed for software engineering, algorithms, AI, and systems architecture. Includes Related Work, System Design, and Empirical Benchmarks.</p>
                        <div class="template-sections">
                            <span>Abstract</span><span>I. Intro</span><span>II. Related Work</span><span>III. Architecture</span><span>IV. Evaluation</span><span>V. Threats</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="ieee_cs">Use IEEE / ACM Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Social Sciences</span>
                            <h3>Qualitative Case Study & Thesis</h3>
                        </div>
                        <p class="template-desc">Focused on inductive inquiry, lived experience, interview narratives, and thematic qualitative coding with ethical transparency.</p>
                        <div class="template-sections">
                            <span>1. Problem</span><span>2. Theoretical Lens</span><span>3. Case Selection</span><span>4. Thematic Findings</span><span>5. Ethics</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="qualitative_thesis">Use Qualitative Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Evidence Synthesis</span>
                            <h3>Systematic Literature Review (PRISMA)</h3>
                        </div>
                        <p class="template-desc">Follows PRISMA guidelines for comprehensive literature queries, quality appraisal, bibliometric synthesis, and research gap mapping.</p>
                        <div class="template-sections">
                            <span>1. Review Qs</span><span>2. Search Strategy</span><span>3. Inclusion Criteria</span><span>4. Bibliometrics</span><span>5. Gaps</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="prisma_review">Use PRISMA Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Systems & Innovation</span>
                            <h3>Design Science Research (DSR)</h3>
                        </div>
                        <p class="template-desc">Rigorous framework for constructing and evaluating novel technological artifacts, frameworks, and software systems solving practical problems.</p>
                        <div class="template-sections">
                            <span>1. Problem</span><span>2. Objectives</span><span>3. Artifact Design</span><span>4. Demonstration</span><span>5. Evaluation</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="dsr_framework">Use DSR Template</button>
                    </div>
                </div>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="template-dialog">Cancel</button>
                </div>
            </div>
        </dialog>

        <!-- 3. Insert Figure / Media Modal -->
        <dialog class="app-dialog" id="media-dialog">
            <form id="media-form" class="dialog-form" enctype="multipart/form-data" style="padding: 24px;">
                <button class="dialog-close" type="button" data-close-dialog="media-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Visual Evidence</p>
                <h2>Insert Figure or Media</h2>
                <p class="dialog-copy">Add architecture diagrams, experimental charts, or conceptual figures with standard academic captions and figure numbers.</p>

                <div class="media-input-tabs">
                    <label class="radio-label">
                        <input type="radio" name="media_source_type" value="url" checked> Image URL / Unsplash
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="media_source_type" value="file"> Upload image file
                    </label>
                </div>

                <div id="media-url-field">
                    <label>
                        Image URL
                        <input type="url" name="image_url" placeholder="https://... (or leave default demo sample)" value="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1000&q=80">
                    </label>
                </div>

                <div id="media-file-field" style="display: none;">
                    <label>
                        Select Image File
                        <input type="file" name="image_file" accept="image/*">
                    </label>
                </div>

                <label>
                    Figure Title / Header
                    <input type="text" name="title" required placeholder="e.g. Figure 2: Experimental Performance Comparison" value="Figure: System Architecture">
                </label>

                <label>
                    Academic Caption & Attribution
                    <textarea name="caption" rows="2" placeholder="Describe the figure and cite the source or experimental setup…">Comparative latency and throughput benchmarks under varying concurrent loads across distributed nodes.</textarea>
                </label>

                <div class="form-row-2">
                    <label>
                        Alignment
                        <select name="alignment" id="media-alignment">
                            <option value="center">Center (Standard Academic)</option>
                            <option value="full">Full width</option>
                            <option value="left">Left aligned</option>
                            <option value="right">Right aligned</option>
                        </select>
                    </label>
                </div>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="media-dialog">Cancel</button>
                    <button class="button button-primary" type="submit">Insert into Manuscript</button>
                </div>
                <p class="form-error" id="media-form-error" role="alert"></p>
            </form>
        </dialog>

        <!-- 4. In-Context Citation Modal -->
        <dialog class="app-dialog" id="citation-dialog">
            <div class="dialog-form" style="padding: 24px;">
                <button class="dialog-close" type="button" data-close-dialog="citation-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Academic Sourcing</p>
                <h2>Insert Citation in Context</h2>
                <p class="dialog-copy">Pick an existing reference from your project bibliography or add a new scholarly source to cite at the cursor.</p>

                <div class="citation-mode-toggle">
                    <button type="button" class="tab-chip is-active" id="btn-cite-mode-existing">Select existing citation</button>
                    <button type="button" class="tab-chip" id="btn-cite-mode-new">＋ Add new source</button>
                </div>

                <!-- Existing Citations List for 1-Click In-Text Insertion -->
                <div id="existing-citations-container">
                    <input type="search" class="search-input" id="modal-citation-search" placeholder="🔍 Search by author or title..." style="width: 100%; margin-bottom: 0.75rem;">
                    <div id="quick-citation-picker" class="quick-citation-picker">
                        <div class="loading-line">Loading citations…</div>
                    </div>
                </div>

                <!-- New Citation Form -->
                <form id="citation-form" style="display: none;">
                    <label>
                        Paper or Book Title
                        <input type="text" name="title" required placeholder="e.g. Deep Residual Learning for Image Recognition">
                    </label>
                    <label>
                        Authors
                        <input type="text" name="authors" required placeholder="e.g. He, K., Zhang, X., Ren, S., & Sun, J.">
                    </label>
                    <div class="form-row-2">
                        <label>
                            Publication Year
                            <input type="text" name="year" placeholder="e.g. 2016" value="2024">
                        </label>
                        <label>
                            Citation Key (optional)
                            <input type="text" name="key" placeholder="e.g. he2016deep">
                        </label>
                    </div>
                    <label>
                        Journal, Conference, or Publisher
                        <input type="text" name="source" placeholder="e.g. IEEE Conference on Computer Vision and Pattern Recognition (CVPR)">
                    </label>
                    <div class="form-row-2">
                        <label>
                            DOI
                            <input type="text" name="doi" placeholder="e.g. 10.1109/CVPR.2016.90">
                        </label>
                        <label>
                            URL
                            <input type="url" name="url" placeholder="https://doi.org/...">
                        </label>
                    </div>
                    <div class="dialog-actions">
                        <button class="button button-quiet" type="button" data-close-dialog="citation-dialog">Cancel</button>
                        <button class="button button-primary" type="submit">Save & Insert Citation</button>
                    </div>
                    <p class="form-error" id="citation-form-error" role="alert"></p>
                </form>
            </div>
        </dialog>

        <!-- 5. Invite Collaborator Modal (Team of 2/3 Multi-User Workflow) -->
        <dialog class="app-dialog" id="member-dialog">
            <form id="member-form" class="dialog-form" style="padding: 24px;">
                <button class="dialog-close" type="button" data-close-dialog="member-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Multi-User Collaboration</p>
                <h2>Invite Project Collaborator</h2>
                <p class="dialog-copy">Add co-authors, thesis advisors, or peer reviewers. A group of 2-3 can draft and review together seamlessly in real-time.</p>

                <label>
                    Collaborator Email
                    <input type="email" name="email" required placeholder="e.g. coauthor@university.edu or supervisor@institution.org">
                </label>

                <label>
                    Role in Project
                    <select name="role" required>
                        <option value="co-author">Co-Author (Full write & version access)</option>
                        <option value="supervisor">Supervisor (Reviewer & feedback author)</option>
                        <option value="reviewer">Peer Reviewer (Read & feedback)</option>
                        <option value="student">Student Researcher</option>
                    </select>
                </label>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="member-dialog">Cancel</button>
                    <button class="button button-primary" type="submit">Add Collaborator</button>
                </div>
                <p class="form-error" id="member-form-error" role="alert"></p>
            </form>
        </dialog>
    </body>
</html>
