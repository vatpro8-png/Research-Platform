const projectId = document.body.dataset.projectId;
const editor = document.querySelector('#editor-canvas');
const memberList = document.querySelector('#member-list');
const versionHistory = document.querySelector('#version-history');
const versionLabel = document.querySelector('#version-label');
const saveState = document.querySelector('#save-state');
const taskList = document.querySelector('#task-list');
const feedbackList = document.querySelector('#feedback-list');
const citationList = document.querySelector('#citation-list');
const mediaList = document.querySelector('#media-list');
const aiSummaryButton = document.querySelector('#ai-summary');
const aiResult = document.querySelector('#ai-result');
const setupForm = document.querySelector('#setup-form');
const setupState = document.querySelector('#setup-state');
const activeEditorBanner = document.querySelector('#active-editor-banner');
const methodologySelect = document.querySelector('#methodology-select');
const runningMethodology = document.querySelector('#running-methodology');
const paperPrinciple = document.querySelector('#paper-principle');
const aiModelBadge = document.querySelector('#ai-model-badge');
const aiLabPrompt = document.querySelector('#ai-lab-prompt');
const aiLabSubmit = document.querySelector('#ai-lab-submit');
const aiLabOutput = document.querySelector('#ai-lab-output');
const aiLabModel = document.querySelector('#ai-lab-model');
const aiActiveIdentity = document.querySelector('#ai-active-identity');
const aiAgents = document.querySelectorAll('[data-ai-agent]');

const updateAiIdentity = (model = '') => {
    const normalizedModel = model.toLowerCase();
    let identity = { key: 'alice', name: 'Alice · Thesis Co-Pilot (Camber)' };

    if (normalizedModel.includes('alice') || normalizedModel.includes('camber')) {
        identity = { key: 'alice', name: 'Alice · Thesis Co-Pilot (Camber)' };
    } else if (normalizedModel.includes('deepseek')) {
        identity = { key: 'deepseek', name: 'DeepSeek Reviewer' };
    } else if (normalizedModel.includes('qwen')) {
        identity = { key: 'qwen', name: 'Qwen Builder' };
    } else if (normalizedModel.includes('llama') || normalizedModel.includes('groq')) {
        identity = { key: 'llama', name: 'Llama 3.3 (Groq)' };
    } else if (normalizedModel.includes('gemini')) {
        identity = { key: 'gemini', name: 'Google Gemini 2.5 Flash' };
    }

    if (aiActiveIdentity) aiActiveIdentity.textContent = identity.name;
    aiAgents.forEach((agent) => agent.classList.toggle('is-active', agent.dataset.aiAgent === identity.key));
};

aiAgents.forEach((agent) => {
    agent.addEventListener('click', () => {
        const key = agent.dataset.aiAgent;
        aiAgents.forEach((a) => a.classList.remove('is-active'));
        agent.classList.add('is-active');
        if (key === 'alice') {
            if (aiActiveIdentity) aiActiveIdentity.textContent = 'Alice · Thesis Co-Pilot (Camber)';
            if (aiLabModel) aiLabModel.textContent = 'Camber Cloud Custom Agent';
        } else if (key === 'gemini') {
            if (aiActiveIdentity) aiActiveIdentity.textContent = 'Google Gemini 2.5 Flash';
            if (aiLabModel) aiLabModel.textContent = '1,000,000+ token context · Free tier';
        } else if (key === 'llama') {
            if (aiActiveIdentity) aiActiveIdentity.textContent = 'Llama 3.3 (Groq)';
            if (aiLabModel) aiLabModel.textContent = 'Ultra-low latency LPU';
        } else if (key === 'deepseek') {
            if (aiActiveIdentity) aiActiveIdentity.textContent = 'DeepSeek-R1';
            if (aiLabModel) aiLabModel.textContent = 'Deep academic reasoning';
        } else if (key === 'qwen') {
            if (aiActiveIdentity) aiActiveIdentity.textContent = 'Qwen 2.5';
            if (aiLabModel) aiLabModel.textContent = 'Code & benchmarks';
        }
    });
});

updateAiIdentity();

// Cached project data
let currentProject = null;
let currentCitations = [];
let currentMedia = [];
let latestKnownVersion = 0;

// ================= 1. COMPREHENSIVE THEORY OF METHODOLOGY DEFINITIONS =================
const METHODOLOGY_THEORIES = {
    Qualitative: {
        paradigm: 'Interpretivism & Social Constructivism',
        theorists: 'Egon Guba & Yvonna Lincoln (1985), John W. Creswell, Kathy Charmaz',
        desc: 'Assumes reality is socially constructed through lived experiences, cultural contexts, and human interactions. Aims for deep inductive discovery rather than statistical reductionism.',
        ontology: 'Relativist: Multiple subjective realities; researcher and participants are epistemologically intertwined.',
        question: '"How or why does [phenomenon] occur within [social / organizational context]?"',
        data: 'Semi-structured in-depth interviews, focus groups, field observation, grounded theory coding transcripts',
        validation: 'Lincoln & Guba criteria: Credibility (member checking), Transferability (thick description), Dependability (audit trail), Confirmability (reflexivity)',
        pitfalls: 'Treating qualitative themes as statistical frequencies; omission of reflexive researcher positionality statement.',
        summary: 'Inductive, exploratory inquiry capturing nuanced participant perspectives without premature statistical constraints.',
    },
    Quantitative: {
        paradigm: 'Positivism & Post-Positivism (Empiricism)',
        theorists: 'Karl Popper (1959), Auguste Comte, Donald Campbell & Julian Stanley (1963)',
        desc: 'Assumes an objective, measurable reality exists independently of human observation. Employs deductive hypothesis testing, operationalized variables, and inferential statistics to test causality.',
        ontology: 'Realist / Critical Realist: Objective reality governed by natural laws and probabilistic relationships.',
        question: '"What is the causal effect or significant relationship between Variable X and Variable Y, controlling for Z?"',
        data: 'Randomized controlled trials, structured surveys (Likert), automated telemetry, regression analysis, ANOVA',
        validation: 'Internal & External Validity, Statistical Significance (p < 0.05), Effect Size (Cohen\'s d), Construct Reliability (Cronbach\'s alpha > 0.70)',
        pitfalls: 'Confusing correlation with causation; p-hacking or omitting statistical power analysis calculations.',
        summary: 'Deductive, hypothesis-driven empirical study testing theoretical relationships through operationalized variables and statistical significance.',
    },
    'Mixed methods': {
        paradigm: 'Pragmatism (Pluralistic Epistemology)',
        theorists: 'John Dewey (1938), Charles Sanders Peirce, Abbas Tashakkori & Charles Teddlie, John W. Creswell',
        desc: 'Rejects methodological dogmatism: "Truth is what works in practice." Integrates quantitative breadth with qualitative contextual depth using triangulation and sequential designs.',
        ontology: 'Pragmatic: Reality is both objective and socially experienced; value of truth lies in practical problem-solving.',
        question: '"To what extent and in what ways does qualitative depth explain quantitative outcomes?"',
        data: 'Convergent parallel datasets (e.g., broad quantitative survey + follow-up qualitative explanatory interviews)',
        validation: 'Design fidelity, interpretive consistency, cross-methodological triangulation, dialectical integration',
        pitfalls: 'Treating mixed methods as two unintegrated parallel studies without explicit synthesis.',
        summary: 'Pluralistic research capitalizing on the complementary strengths of statistical generalizability and experiential depth.',
    },
    'Design science': {
        paradigm: 'Sciences of the Artificial & Utility Theory',
        theorists: 'Herbert A. Simon (1969), Alan R. Hevner (2004), Ken Peffers (2007)',
        desc: 'Generates prescriptive scientific knowledge through the design, demonstration, and empirical evaluation of innovative technological artifacts (software, architectures, algorithms) solving practical problems.',
        ontology: 'Constructive Artificial: Reality includes human-made artifacts whose merit is judged by utility and efficacy.',
        question: '"How can an innovative [Artifact / Architecture] be engineered to solve [Problem X] with superior utility?"',
        data: 'Functional prototypes, architectural blueprints, computational latency benchmarks, usability evaluations',
        validation: 'Hevner\'s Guidelines: Utility theory, artifact novelty, structural robustness, empirical benchmark superiority',
        pitfalls: 'Building a system without rigorous formal evaluation or theoretical contribution.',
        summary: 'Engineering research producing reusable prescriptive design principles and demonstrating measurable utility over baseline systems.',
    },
    'Literature review': {
        paradigm: 'Critical Realism & PRISMA Evidence Protocol',
        theorists: 'David Moher et al. (PRISMA 2009/2020), Kitchenham & Charters (2007)',
        desc: 'Synthesizes cumulative scientific evidence without selection bias. Audits existing literature to map consensus, establish foundational baselines, and uncover unaddressed research frontiers.',
        ontology: 'Cumulative Realist: Scientific knowledge is cumulative; systematic synthesis reveals the consensus and frontier of truth.',
        question: '"What is the synthesized state of scientific evidence regarding [Topic], and where are the research gaps?"',
        data: 'Comprehensive database queries across IEEE Xplore, ACM DL, Scopus, PubMed, and Web of Science',
        validation: 'PRISMA 27-item checklist, reproducible boolean query strings, explicit inclusion/exclusion audit',
        pitfalls: 'Selective cherry-picking of articles; missing search strings or lack of quality appraisal scoring.',
        summary: 'Exhaustive, unbiased, reproducible synthesis of peer-reviewed scientific literature establishing foundational benchmarks.',
    },
    'Action research': {
        paradigm: 'Critical Social Theory & Participatory Inquiry',
        theorists: 'Kurt Lewin (1946), Stephen Kemmis & Robin McTaggart (1988), Peter Checkland',
        desc: 'Simultaneously solves real-world organizational challenges while advancing scholarly theory through collaborative, iterative cycles: Plan → Act → Observe → Reflect.',
        ontology: 'Participatory / Constructivist: Social systems can only be understood through deliberate attempts to transform them.',
        question: '"How can practitioners and researchers collaboratively intervene to transform [System X]?"',
        data: 'Intervention sprint logs, stakeholder debriefs, iterative process metrics, reflective journals',
        validation: 'Recoverability of inquiry, catalytic validity (stakeholder empowerment), cyclical reflection fidelity',
        pitfalls: 'Devolving into standard corporate consulting without advancing generalizable scholarly theory.',
        summary: 'Participatory, collaborative inquiry generating actionable practitioner solutions through recurring spirals of reflection.',
    },
};

const updateTheoryCard = (methodology) => {
    const card = document.querySelector('#theory-card');
    if (!card) return;

    const theory = METHODOLOGY_THEORIES[methodology];
    if (theory) {
        card.innerHTML = `
            <div class="theory-card-header">
                <span class="theory-card-badge">${theory.paradigm}</span>
                <span class="theory-card-badge" style="color: var(--teal);">${methodology}</span>
            </div>
            <p class="theory-theorists"><strong>Seminal Theorists:</strong> ${theory.theorists}</p>
            <p class="theory-card-desc">${theory.desc}</p>
            <div class="theory-grid-layout">
                <div class="theory-grid-cell">
                    <strong>Epistemological Stance & Ontology</strong>
                    <span>${theory.ontology}</span>
                </div>
                <div class="theory-grid-cell">
                    <strong>Core Question Format</strong>
                    <span><em>${theory.question}</em></span>
                </div>
                <div class="theory-grid-cell">
                    <strong>Data Collection & Evidence</strong>
                    <span>${theory.data}</span>
                </div>
                <div class="theory-grid-cell">
                    <strong>Rigor & Validation Criteria</strong>
                    <span>${theory.validation}</span>
                </div>
            </div>
            <div class="theory-formula-box">
                <strong>Key Abstract Formula:</strong> ${theory.summary}
            </div>
        `;
        card.classList.add('has-selection');
    } else {
        card.innerHTML = `
            <div class="theory-card-header">
                <span class="theory-card-badge">Select a methodology</span>
            </div>
            <p class="theory-card-desc">Selecting a methodology will display its theoretical premise, seminal theorists, epistemological ground, and rigor criteria.</p>
        `;
        card.classList.remove('has-selection');
    }
};

const updateResearchFrame = (methodology) => {
    const principles = {
        Qualitative: 'credibility · reflexivity · participant voice',
        Quantitative: 'validity · reliability · reproducibility',
        'Mixed methods': 'triangulation · integration · pragmatic fit',
        'Design science': 'utility · rigor · artifact evaluation',
        'Literature review': 'transparency · evidence synthesis · PRISMA',
        'Action research': 'participation · reflection · change',
    };

    if (runningMethodology) {
        runningMethodology.textContent = methodology ? `· ${methodology}` : '· Methodology pending';
    }
    if (paperPrinciple) {
        paperPrinciple.textContent = principles[methodology] || 'Research integrity · traceable evidence · reflective practice';
    }
};

methodologySelect?.addEventListener('change', (e) => {
    updateTheoryCard(e.target.value);
    updateResearchFrame(e.target.value);
});

// Setup panel collapse toggle
const setupPanel = document.querySelector('#stage-initialization');
const toggleSetupBtn = document.querySelector('#toggle-setup-panel');
toggleSetupBtn?.addEventListener('click', () => {
    if (!setupPanel) return;
    const isCollapsed = setupPanel.classList.toggle('is-collapsed');
    toggleSetupBtn.textContent = isCollapsed ? 'Expand setup ↓' : 'Collapse setup ↑';
});

const architectureDetails = {
    question: 'Start with a bounded question: define the problem, who it serves, and what a meaningful outcome looks like.',
    method: 'Make the paradigm visible. Link the research question to an appropriate method, theoretical stance, and ethics decision.',
    evidence: 'Treat every claim as traceable: connect source keys, figures, datasets, and observations to the section where they are used.',
    review: 'Use feedback and versions as part of the method. Record limitations, supervisor decisions, and reflections instead of losing them in email.',
    contribution: 'Close the loop with a defensible contribution: findings, artifact utility, implications, and a concrete next research step.',
};

document.querySelectorAll('[data-architecture-node]').forEach((node) => {
    node.addEventListener('click', () => {
        document.querySelectorAll('[data-architecture-node]').forEach((item) => item.classList.remove('is-selected'));
        node.classList.add('is-selected');
        const detail = document.querySelector('#architecture-detail');
        if (detail) detail.textContent = architectureDetails[node.dataset.architectureNode];
    });
});

document.querySelector('#toggle-architecture')?.addEventListener('click', (event) => {
    const tree = document.querySelector('#architecture-tree');
    if (!tree) return;
    const isCollapsed = tree.classList.toggle('is-collapsed');
    event.currentTarget.setAttribute('aria-expanded', String(!isCollapsed));
    event.currentTarget.textContent = isCollapsed ? 'Expand tree ↓' : 'Collapse tree ↑';
});

const runAiSummary = async (instruction, sourceButton = null) => {
    const buttons = [aiSummaryButton, aiLabSubmit, ...document.querySelectorAll('[data-ai-instruction]')].filter(Boolean);
    buttons.forEach((button) => { button.disabled = true; });
    if (sourceButton) sourceButton.textContent = 'Thinking…';
    if (aiSummaryButton) aiSummaryButton.textContent = 'Analyzing thesis context…';
    if (aiLabOutput) aiLabOutput.innerHTML = '<span class="ai-output-loading">Synthesizing project context…</span>';
    if (aiResult) aiResult.textContent = '';

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/ai/summary`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ instruction: instruction || 'Summarize the current project state and recommend the next three research actions.' }),
        });
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'The research assistant is unavailable right now.');
        }

        const content = payload.data.content || 'No response was returned.';
        if (aiResult) aiResult.textContent = content;
        if (aiLabOutput) aiLabOutput.textContent = content;
        if (aiModelBadge) aiModelBadge.textContent = `${payload.data.provider || 'Google Gemini'} · ${payload.data.model || 'gemini-2.5-flash'} (Encrypted)`;
        if (aiLabModel) aiLabModel.textContent = `${payload.data.provider || 'Google Gemini'} · ${payload.data.model || 'gemini-2.5-flash'}`;
        updateAiIdentity(payload.data.model || '');
    } catch (error) {
        if (aiResult) aiResult.textContent = error.message;
        if (aiLabOutput) aiLabOutput.textContent = error.message;
    } finally {
        buttons.forEach((button) => { button.disabled = false; });
        if (aiSummaryButton) aiSummaryButton.textContent = '✦ Ask research assistant';
        if (sourceButton) sourceButton.textContent = 'Run research copilot';
    }
};

document.querySelectorAll('[data-ai-instruction]').forEach((button) => {
    button.addEventListener('click', () => {
        if (aiLabPrompt) aiLabPrompt.value = button.dataset.aiInstruction;
        runAiSummary(button.dataset.aiInstruction, button.classList.contains('ai-quick-action') ? aiLabSubmit : button);
    });
});

aiLabSubmit?.addEventListener('click', () => runAiSummary(aiLabPrompt?.value, aiLabSubmit));
aiLabPrompt?.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        event.preventDefault();
        runAiSummary(aiLabPrompt.value, aiLabSubmit);
    }
});

// ================= 2. RESEARCH PAPER TEMPLATES =================
const PAPER_TEMPLATES = {
    imrad: `
        <h2>Abstract</h2>
        <p><em>[Provide a concise 150-250 word summary covering the research problem, methodology, key findings, and practical implications.]</em></p>
        
        <h2>1. Introduction</h2>
        <p>Establish the academic and real-world significance of the research topic. What gap in knowledge exists, and why must it be addressed now?</p>
        <p><strong>Research Objectives:</strong> Clearly enumerate the primary questions or hypotheses this paper investigates.</p>

        <h2>2. Materials & Methods</h2>
        <p>Describe the research design, participants, materials, and procedures with sufficient detail for external replication. Ground your methodological choices in established literature.</p>
        <p><em>[Tip: Cite validation instruments and statistical or qualitative analysis procedures used.]</em></p>

        <h2>3. Results & Empirical Findings</h2>
        <p>Present the findings objectively using text, tables, and figures. Highlight significant patterns, statistical trends, or thematic categories without interpretive commentary.</p>

        <h2>4. Discussion</h2>
        <p>Interpret the results in the context of the initial research questions. How do your findings compare with prior work? What are the theoretical and practical implications?</p>
        <p><strong>Limitations:</strong> Candidly acknowledge any methodological boundaries, sample constraints, or confounding variables.</p>

        <h2>5. Conclusion & Future Work</h2>
        <p>Summarize the core contributions and outline concrete recommendations for subsequent research.</p>
    `,

    ieee_cs: `
        <h2>Abstract</h2>
        <p><em>[State the computational or engineering problem, the proposed architecture/algorithm, implementation highlights, and benchmark speedup or accuracy improvements.]</em></p>
        <p><strong>Index Terms:</strong> Software Architecture, Distributed Systems, Empirical Evaluation, Benchmarking.</p>

        <h2>I. Introduction</h2>
        <p>Modern computing environments face critical bottlenecks. This paper introduces a novel system architecture designed to solve scalability and latency constraints.</p>
        <p><strong>Our Contributions:</strong></p>
        <ul>
            <li>We formulate the distributed coordination challenge under high concurrency.</li>
            <li>We design and implement an end-to-end framework.</li>
            <li>We empirically evaluate our solution against baseline state-of-the-art benchmarks.</li>
        </ul>

        <h2>II. Related Work</h2>
        <p>Review prior systems and highlight where current literature fails to address the targeted performance parameters.</p>

        <h2>III. System Architecture & Design</h2>
        <p>Provide a comprehensive technical description of the proposed framework, data flows, and algorithmic mechanisms.</p>

        <h2>IV. Experimental Evaluation</h2>
        <p>Detail the experimental testbed, hardware/software specifications, workloads, and metrics (latency, throughput, resource consumption).</p>

        <h2>V. Threats to Validity</h2>
        <p>Discuss construct, internal, and external validity threats and how they were mitigated.</p>

        <h2>VI. Conclusion & Future Work</h2>
        <p>Summarize key findings and prospective future engineering iterations.</p>
    `,

    qualitative_thesis: `
        <h2>Abstract</h2>
        <p><em>[Summarize the lived experiences, social context, qualitative inquiry approach, core emergent themes, and theoretical contribution.]</em></p>

        <h2>1. Research Context & Problem Statement</h2>
        <p>Situate the inquiry within the social, organizational, or cultural context. Why is qualitative depth essential for understanding this phenomenon?</p>

        <h2>2. Theoretical Framework & Epistemology</h2>
        <p>Articulate the interpretivist/constructivist theoretical lens through which the study was conceptualized and analyzed.</p>

        <h2>3. Methodology & Participant Selection</h2>
        <p>Detail the purposive or theoretical sampling strategy, participant profiles, interview protocols, and ethical safeguards.</p>

        <h2>4. Qualitative Analysis & Emergent Themes</h2>
        <p><strong>Theme 1: Navigating Structural Ambiguity</strong> — Integrate verbatim participant quotes with rich analytical synthesis.</p>
        <p><strong>Theme 2: Collaborative Sensemaking</strong> — Illustrate how participants negotiated shared understanding.</p>

        <h2>5. Discussion & Implications</h2>
        <p>Connect emergent themes back to existing academic literature and organizational practice.</p>

        <h2>6. Ethical Considerations & Reflexivity</h2>
        <p>Reflect on researcher positioning, potential biases, and member-checking validation steps.</p>
    `,

    prisma_review: `
        <h2>Abstract</h2>
        <p><em>[Structured summary: Background, Objectives, Data Sources, Eligibility Criteria, Synthesis Methods, Key Findings, and Conclusions.]</em></p>

        <h2>1. Background & Review Questions</h2>
        <p>Formulate explicit research questions using PICO/SPIDER frameworks. Define the scope and justification for this systematic literature review.</p>

        <h2>2. Search Protocol & PRISMA Strategy</h2>
        <p>Document the electronic databases searched (e.g., IEEE Xplore, ACM DL, Scopus), exact boolean query strings, and date limits.</p>

        <h2>3. Study Selection & Quality Appraisal</h2>
        <p>Detail the multi-stage screening process (title/abstract screening followed by full-text evaluation) with explicit inclusion/exclusion criteria.</p>

        <h2>4. Bibliometric & Thematic Synthesis</h2>
        <p>Categorize accepted studies by publication chronology, geography, methodologies used, and technological frameworks.</p>

        <h2>5. Identified Research Gaps & Future Roadmap</h2>
        <p>Synthesize major unresolved frontiers and offer actionable recommendations for the academic community.</p>
    `,

    dsr_framework: `
        <h2>Abstract</h2>
        <p><em>[Summarize the business/engineering problem, the designed artifact, the evaluation environment, and the utility delivered.]</em></p>

        <h2>1. Problem Identification & Motivation</h2>
        <p>Define the specific problem in practice and demonstrate its academic and organizational significance.</p>

        <h2>2. Solution Objectives & Requirements</h2>
        <p>Derive functional and non-functional requirements that an effective artifact must satisfy.</p>

        <h2>3. Artifact Design & Architecture</h2>
        <p>Present the structure, modules, and internal mechanisms of the designed system, model, or algorithm.</p>

        <h2>4. Demonstration</h2>
        <p>Demonstrate the artifact solving an instance of the problem in an institutional or simulated case environment.</p>

        <h2>5. Empirical Evaluation</h2>
        <p>Measure the artifact's utility, efficacy, and performance using qualitative or quantitative criteria.</p>

        <h2>6. Theoretical & Practical Contributions</h2>
        <p>Articulate design principles and reusable prescriptive knowledge derived from the creation process.</p>
    `,
};

// ================= 3. CORE ARCHITECTURE AUDIT & ENVIRONMENT LOADER =================
const loadArchitectureAudit = async () => {
    try {
        const response = await fetch(`/api/v1/projects/${projectId}/architecture/audit`, {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) return;

        const { data } = await response.json();
        const globalStatus = document.querySelector('#arch-global-status');
        if (globalStatus) {
            globalStatus.textContent = data.architectural_integrity_passed ? 'PASS' : 'FAIL';
            globalStatus.className = `arch-badge ${data.architectural_integrity_passed ? 'arch-pass' : 'arch-fail'}`;
        }

        const auditList = document.querySelector('#arch-audit-list');
        if (auditList && data.structures) {
            const s = data.structures;
            auditList.innerHTML = `
                <div class="arch-audit-item">
                    <div><strong>Binary Tree</strong><small>Merkle Root (${s.binary_tree?.indexed_citations || 0} citations)</small></div>
                    <span class="arch-badge arch-pass">Root: ${(s.binary_tree?.merkle_root || 'Calculated').slice(0, 8)}…</span>
                </div>
                <div class="arch-audit-item">
                    <div><strong>Recursion</strong><small>Depth Guard (${s.recursion?.total_nodes || 0} nodes / depth ${s.recursion?.max_observed_depth || 1})</small></div>
                    <span class="arch-badge ${s.recursion?.outline_valid ? 'arch-pass' : 'arch-fail'}">${s.recursion?.outline_valid ? 'Verified' : 'Invalid'}</span>
                </div>
                <div class="arch-audit-item">
                    <div><strong>Graph Mapping</strong><small>DAG Structure (${s.graph_mapping?.nodes || 0} nodes)</small></div>
                    <span class="arch-badge ${!s.graph_mapping?.has_cycles ? 'arch-pass' : 'arch-fail'}">${!s.graph_mapping?.has_cycles ? 'Cycle-Free' : 'Cycle Detected'}</span>
                </div>
                <div class="arch-audit-item">
                    <div><strong>Linked List</strong><small>Tamper-Evident Ledger (${s.linked_list?.node_count || 0} versions)</small></div>
                    <span class="arch-badge ${s.linked_list?.chain_valid ? 'arch-pass' : 'arch-fail'}">${s.linked_list?.chain_valid ? 'Verified' : 'Broken'}</span>
                </div>
                <div class="arch-audit-item">
                    <div><strong>Linear Search</strong><small>Sequential Audit Scan (${s.linear_search?.urgent_unresolved_tasks_count || 0} urgent items)</small></div>
                    <span class="arch-badge arch-pass">Scanned</span>
                </div>
                <div class="arch-audit-item">
                    <div><strong>Cryptographic Hash</strong><small>SHA-256 HMAC State Seal</small></div>
                    <span class="arch-badge arch-pass">Sealed: ${(s.hash_integrity?.hash || '').slice(0, 8)}…</span>
                </div>
            `;
        }
    } catch (e) {
        console.error('Architecture audit load error:', e);
    }
};

const loadEnvironmentHealth = async () => {
    try {
        const response = await fetch('/api/v1/integrations/health', {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) return;

        const { data } = await response.json();
        const env = data.environment || {};

        const activeDirEl = document.querySelector('#env-active-directory');
        const storageEl = document.querySelector('#env-storage-path');
        const dbDriverEl = document.querySelector('#env-db-driver');
        const aiProviderEl = document.querySelector('#env-ai-provider');
        const aiModelEl = document.querySelector('#env-ai-model');

        if (activeDirEl && env.active_directory) activeDirEl.textContent = env.active_directory;
        if (storageEl && env.public_storage_path) storageEl.textContent = `${env.public_storage_path} (${env.storage_linked ? 'Linked' : 'Not Linked'})`;
        if (dbDriverEl && env.database_driver) dbDriverEl.textContent = `${env.database_driver} (Relational)`;
        if (aiProviderEl && env.ai_provider) aiProviderEl.textContent = env.ai_provider;
        if (aiModelEl && env.ai_model) aiModelEl.textContent = env.ai_model;
    } catch (e) {
        console.error('Environment health load error:', e);
    }
};

const loadAiAvailability = async () => {
    const btn = document.querySelector('#btn-refresh-ai-status');
    if (btn) btn.textContent = '…';
    try {
        const response = await fetch('/api/v1/ai/status', {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) return;

        const { data } = await response.json();
        const providers = data.providers || {};

        const updateStatusBadge = (id, provider) => {
            const el = document.querySelector(id);
            if (!el || !provider) return;

            if (provider.status === 'ready') {
                el.className = 'arch-badge arch-pass';
                el.textContent = 'ONLINE';
                el.title = `${provider.name} ready (${provider.latency_ms || 0}ms)`;
            } else if (provider.status === 'key_required') {
                el.className = 'arch-badge arch-warn';
                el.textContent = 'REACHABLE (Key Req)';
                el.title = provider.message;
            } else if (provider.status === 'invalid_key') {
                el.className = 'arch-badge arch-fail';
                el.textContent = 'KEY INVALID';
                el.title = provider.message;
            } else {
                el.className = 'arch-badge';
                el.style.background = 'var(--line)';
                el.textContent = 'OFFLINE';
                el.title = provider.message;
            }
        };

        updateStatusBadge('#status-alice', providers.alice);
        updateStatusBadge('#status-gemini', providers.gemini);
        updateStatusBadge('#status-groq', providers.groq);
        updateStatusBadge('#status-ollama', providers.ollama);

        const aiModelBadge = document.querySelector('#ai-model-badge');
        if (aiModelBadge && data.active_provider && data.active_model) {
            aiModelBadge.textContent = `${data.active_provider} · ${data.active_model} (Free Tier)`;
        }
    } catch (e) {
        console.error('AI availability check error:', e);
    } finally {
        if (btn) btn.textContent = '↻ Check';
    }
};

// ================= 4. WORKSPACE CORE LOADER =================
const loadWorkspace = async () => {
    try {
        const [projectResponse, taskResponse, feedbackResponse, citationsResponse, mediaResponse] = await Promise.all([
            fetch(`/api/v1/projects/${projectId}`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/tasks`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/feedback`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/citations`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/media`, { headers: { Accept: 'application/json' } }),
        ]);

        const { data: project } = await projectResponse.json();
        const { data: tasks } = await taskResponse.json();
        const { data: feedback } = await feedbackResponse.json();
        const { data: citations } = await citationsResponse.json();
        const { data: media } = await mediaResponse.json();

        currentProject = project;
        currentCitations = citations || [];
        currentMedia = media || [];

        renderMembers(project.members);
        renderFormatting(project);
        renderActivePresence(project.active_editor);
        renderCitations(currentCitations);
        renderMedia(currentMedia);

        // Version state
        if (project.latest_version) {
            latestKnownVersion = project.latest_version.version_number;
            versionLabel.textContent = `Version ${project.latest_version.version_number}`;
            versionHistory.innerHTML = `
                <div class="version-row">
                    <div>
                        <strong>Version ${project.latest_version.version_number}</strong>
                        <small>Saved by ${project.latest_version.content.author || 'collaborator'}</small>
                    </div>
                    <button type="button" class="button button-quiet button-sm" data-restore-version="${project.latest_version.id}">Restore</button>
                </div>
            `;
        }

        // Tasks
        taskList.innerHTML = tasks.length
            ? tasks.map((task) => `
                <div class="task-row">
                    <button class="task-toggle ${task.status === 'done' ? 'is-done' : ''}" data-task-id="${task.id}" data-task-status="${task.status}" type="button">
                        ${task.status === 'done' ? '✓' : '○'}
                    </button>
                    <span>${task.title}</span>
                </div>
            `).join('')
            : '<div class="loading-line">No tasks yet. Add a milestone above.</div>';
        document.querySelector('#task-count').textContent = tasks.filter((t) => t.status !== 'done').length;

        // Feedback
        feedbackList.innerHTML = feedback.length
            ? feedback.map((item) => `
                <div class="feedback-item ${item.status === 'resolved' ? 'is-resolved' : ''}">
                    <div class="feedback-item-header">
                        <strong>${item.author.name}</strong>
                        <span class="feedback-status-badge">${item.status}</span>
                    </div>
                    <p>${item.body}</p>
                    <button class="text-button" data-feedback-id="${item.id}" type="button">
                        ${item.status === 'resolved' ? 'Resolved ✓' : 'Mark resolved'}
                    </button>
                </div>
            `).join('')
            : '<div class="loading-line">No supervisor notes yet.</div>';
        document.querySelector('#feedback-count').textContent = feedback.filter((i) => i.status === 'open').length;

        // Setup form populate
        if (setupForm) {
            setupForm.elements.research_question.value = project.research_question || '';
            setupForm.elements.methodology.value = project.methodology || '';
            setupForm.elements.expected_outcome.value = project.expected_outcome || '';
            setupForm.elements.ethics_status.value = project.ethics_status || 'not_assessed';
            updateTheoryCard(project.methodology);
            updateResearchFrame(project.methodology);
        }

        updateManuscriptStats();
        loadArchitectureAudit();
        loadEnvironmentHealth();
        loadAiAvailability();
    } catch (err) {
        console.error('Workspace load error:', err);
    }
};

// ================= 5. COLLABORATION (INDIVIDUAL & 2/3 GROUP) =================
const renderMembers = (members) => {
    if (!memberList) return;
    const roleColors = {
        supervisor: 'avatar-coral',
        'co-author': 'avatar-amber',
        student: 'avatar-teal',
        reviewer: 'avatar-purple',
    };

    memberList.innerHTML = members.map((member) => {
        const initials = member.name.split(' ').map((p) => p[0]).join('').slice(0, 2);
        const roleClass = roleColors[member.role] || 'avatar-teal';
        return `
            <div class="member-row">
                <span class="avatar ${roleClass}">${initials}</span>
                <div class="member-info">
                    <strong>${member.name}</strong>
                    <small class="role-badge role-${member.role}">${member.role.replace('-', ' ')}</small>
                </div>
                <span class="member-online" title="Online in workspace">●</span>
            </div>
        `;
    }).join('');

    const count = members.length;
    const banner = document.querySelector('#workspace-sync-status');
    if (banner) {
        banner.textContent = `Real-time sync active · ${count} member${count > 1 ? 's' : ''}`;
    }
};

const renderActivePresence = (activeEditor) => {
    if (!activeEditorBanner) return;
    if (activeEditor) {
        activeEditorBanner.innerHTML = `<strong>${activeEditor.name}</strong> is editing: <em>${activeEditor.section}</em> (${activeEditor.active_at})`;
    } else {
        activeEditorBanner.textContent = 'All team members in sync';
    }
};

// Periodic Presence Heartbeat for Multi-User Working Directory
setInterval(async () => {
    try {
        const sectionHeading = editor?.querySelector('h2:hover, h2') ? editor.querySelector('h2').textContent.slice(0, 40) : 'Manuscript';
        await fetch(`/api/v1/projects/${projectId}/presence`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ section: sectionHeading }),
        });
    } catch (_) {}
}, 15000);

// ================= 6. PAPER MARGINS, LAYOUT & TYPOGRAPHY =================
const applyFormattingClasses = (formatting) => {
    const body = document.body;

    // Margins
    ['margin-normal', 'margin-narrow', 'margin-moderate', 'margin-wide'].forEach((cls) => body.classList.remove(cls));
    body.classList.add(`margin-${formatting.paper_margin || 'normal'}`);

    // Columns
    ['layout-single-col', 'layout-two-col'].forEach((cls) => body.classList.remove(cls));
    body.classList.add(formatting.paper_layout === 'two_column' ? 'layout-two-col' : 'layout-single-col');

    // Font
    ['font-serif', 'font-sans', 'font-mono'].forEach((cls) => body.classList.remove(cls));
    body.classList.add(`font-${formatting.font_family || 'serif'}`);

    // Line Spacing
    ['spacing-1-0', 'spacing-1-15', 'spacing-1-5', 'spacing-2-0'].forEach((cls) => body.classList.remove(cls));
    const spacingKey = (formatting.line_spacing || '1.5').replace('.', '-');
    body.classList.add(`spacing-${spacingKey}`);

    // Theme
    ['theme-clean-white', 'theme-warm-sepia', 'theme-academic-dark'].forEach((cls) => body.classList.remove(cls));
    const themeCls = formatting.active_theme === 'warm_sepia' ? 'theme-warm-sepia' : (formatting.active_theme === 'academic_dark' ? 'theme-academic-dark' : 'theme-clean-white');
    body.classList.add(themeCls);

    // Sync button active states
    document.querySelectorAll('[data-margin]').forEach((b) => b.classList.toggle('is-active', b.dataset.margin === formatting.paper_margin));
    document.querySelectorAll('[data-column]').forEach((b) => b.classList.toggle('is-active', b.dataset.column === formatting.paper_layout));
    document.querySelectorAll('[data-font]').forEach((b) => b.classList.toggle('is-active', b.dataset.font === formatting.font_family));
    document.querySelectorAll('[data-spacing]').forEach((b) => b.classList.toggle('is-active', b.dataset.spacing === formatting.line_spacing));
    document.querySelectorAll('[data-theme-mode]').forEach((b) => b.classList.toggle('is-active', b.dataset.themeMode === formatting.active_theme));
};

const renderFormatting = (project) => {
    applyFormattingClasses({
        paper_margin: project.paper_margin || 'normal',
        paper_layout: project.paper_layout || 'single_column',
        font_family: project.font_family || 'serif',
        line_spacing: project.line_spacing || '1.5',
        active_theme: project.active_theme || 'clean_white',
    });
};

const saveFormatting = async (partial) => {
    const merged = {
        paper_margin: document.querySelector('#margin-buttons .is-active')?.dataset.margin || 'normal',
        paper_layout: document.querySelector('#column-buttons .is-active')?.dataset.column || 'single_column',
        font_family: document.querySelector('#font-buttons .is-active')?.dataset.font || 'serif',
        line_spacing: document.querySelector('#spacing-buttons .is-active')?.dataset.spacing || '1.5',
        active_theme: document.querySelector('.theme-btn.is-active')?.dataset.themeMode || 'clean_white',
        ...partial,
    };

    applyFormattingClasses(merged);

    try {
        await fetch(`/api/v1/projects/${projectId}/formatting`, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(merged),
        });
    } catch (e) {
        console.error('Failed saving layout:', e);
    }
};

// ================= 7. CITATIONS & BIBLIOGRAPHY =================
const renderCitations = (citations) => {
    if (!citationList) return;
    citationList.innerHTML = citations.length
        ? citations.map((citation, idx) => `
            <div class="citation-card citation-item">
                <div class="citation-top">
                    <span class="citation-tag">${citation.in_text_tag || '[' + (idx + 1) + ']'}</span>
                    <button type="button" class="button button-quiet button-sm" data-insert-citation-tag="${citation.in_text_tag || '[' + (idx + 1) + ']'}" data-cite-key="${citation.key}" title="Insert into editor">Insert in text</button>
                </div>
                <strong>${citation.title}</strong>
                <p class="citation-authors">${citation.authors} (${citation.year || 'n.d.'})</p>
                <small class="citation-source">${citation.source || ''} ${citation.doi ? '· DOI: ' + citation.doi : ''}</small>
            </div>
        `).join('')
        : '<div class="loading-line">No citations added yet. Use "＋ Add citation" above.</div>';

    // Also populate quick picker in dialog
    const quickPicker = document.querySelector('#quick-citation-picker');
    if (quickPicker) {
        quickPicker.innerHTML = citations.length
            ? citations.map((citation, idx) => `
                <div class="picker-row quick-citation-option">
                    <div>
                        <span class="citation-tag">${citation.in_text_tag || '[' + (idx + 1) + ']'}</span>
                        <strong>${citation.title}</strong>
                        <small>${citation.authors} (${citation.year || ''})</small>
                    </div>
                    <button type="button" class="button button-primary button-sm" data-insert-citation-tag="${citation.in_text_tag || '[' + (idx + 1) + ']'}" data-cite-key="${citation.key}">Insert</button>
                </div>
            `).join('')
            : '<div class="loading-line">No citations in library. Switch to "Add new source" tab.</div>';
    }
};

const insertInTextCitation = (tag, key) => {
    editor.focus();
    const citationHtml = ` <span class="cite-ref" contenteditable="false" data-cite-key="${key}">${tag}</span> `;
    document.execCommand('insertHTML', false, citationHtml);
    document.querySelector('#citation-dialog')?.close();
};

const appendBibliographyToPaper = () => {
    if (!currentCitations.length) {
        alert('Please add citations to the project before generating references.');
        return;
    }

    const existingRefs = editor.querySelector('.paper-references');
    if (existingRefs) {
        existingRefs.remove();
    }

    const referencesHtml = `
        <section class="paper-references" contenteditable="false">
            <h2>References</h2>
            <ol class="references-list">
                ${currentCitations.map((c) => `
                    <li>
                        <span class="ref-authors">${c.authors}</span> (${c.year || 'n.d.'}). 
                        <strong class="ref-title">${c.title}</strong>. 
                        <em class="ref-source">${c.source || ''}</em>. 
                        ${c.doi ? '<span class="ref-doi">https://doi.org/' + c.doi + '</span>' : (c.url ? '<a href="' + c.url + '" target="_blank">' + c.url + '</a>' : '')}
                    </li>
                `).join('')}
            </ol>
        </section>
    `;

    editor.insertAdjacentHTML('beforeend', referencesHtml);
    saveState.textContent = 'Bibliography appended to paper';
};

// ================= 8. MEDIA & FIGURES =================
const renderMedia = (media) => {
    if (!mediaList) return;
    mediaList.innerHTML = media.length
        ? media.map((item) => `
            <div class="media-card">
                <div class="media-thumb-wrap">
                    <img src="${item.file_path}" alt="${item.title}" loading="lazy">
                </div>
                <div class="media-info">
                    <strong>Figure ${item.figure_number}: ${item.title}</strong>
                    <p>${item.caption || 'No caption provided.'}</p>
                    <button type="button" class="button button-quiet button-sm" data-insert-figure-id="${item.id}">Insert into paper</button>
                </div>
            </div>
        `).join('')
        : '<div class="loading-line">No figures yet. Click "＋ Add figure" to embed diagrams.</div>';
};

const insertFigureIntoEditor = (mediaItem) => {
    editor.focus();
    const figureHtml = `
        <figure class="paper-figure" contenteditable="false" data-media-id="${mediaItem.id}">
            <img src="${mediaItem.file_path}" alt="${mediaItem.title}" style="max-width: 100%;">
            <figcaption><strong class="fig-num">Figure ${mediaItem.figure_number}:</strong> ${mediaItem.title}. ${mediaItem.caption || ''}</figcaption>
        </figure>
    `;
    document.execCommand('insertHTML', false, figureHtml);
    document.querySelector('#media-dialog')?.close();
};

// ================= 9. EVENT LISTENERS & CONTROLS =================

// Rich Text Formatting Buttons
document.querySelectorAll('[data-command]').forEach((button) => {
    button.addEventListener('click', () => {
        document.execCommand(button.dataset.command, false, null);
        editor.focus();
    });
});

document.querySelectorAll('[data-format-block]').forEach((button) => {
    button.addEventListener('click', () => {
        document.execCommand('formatBlock', false, `<${button.dataset.formatBlock}>`);
        editor.focus();
    });
});

// Layout Switcher (Studio, Focus, Review)
document.querySelectorAll('[data-layout-mode]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-layout-mode]').forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');

        document.body.classList.remove('layout-full', 'layout-focus', 'layout-review');
        document.body.classList.add(`layout-${btn.dataset.layoutMode}`);
    });
});

// Color Palette Theme Switcher (White, Sepia, Dark)
document.querySelectorAll('[data-theme-mode]').forEach((btn) => {
    btn.addEventListener('click', () => {
        saveFormatting({ active_theme: btn.dataset.themeMode });
    });
});

// Paper Format Strip Buttons
document.querySelector('#margin-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-margin]');
    if (btn) saveFormatting({ paper_margin: btn.dataset.margin });
});

document.querySelector('#column-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-column]');
    if (btn) saveFormatting({ paper_layout: btn.dataset.column });
});

document.querySelector('#font-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-font]');
    if (btn) saveFormatting({ font_family: btn.dataset.font });
});

document.querySelector('#spacing-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-spacing]');
    if (btn) saveFormatting({ line_spacing: btn.dataset.spacing });
});

// Sidebar Tab switching
document.querySelectorAll('[data-sidebar-tab]').forEach((tabBtn) => {
    tabBtn.addEventListener('click', () => {
        document.querySelectorAll('[data-sidebar-tab]').forEach((b) => b.classList.remove('is-active'));
        document.querySelectorAll('.sidebar-panel').forEach((p) => p.classList.remove('is-active'));

        tabBtn.classList.add('is-active');
        const targetId = `tab-panel-${tabBtn.dataset.sidebarTab}`;
        document.getElementById(targetId)?.classList.add('is-active');
    });
});

// Guide Modal Tab Switching
document.querySelectorAll('.guide-tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.guide-tab-btn').forEach((b) => b.classList.remove('is-active'));
        document.querySelectorAll('.guide-tab-pane').forEach((p) => p.classList.remove('is-active'));

        btn.classList.add('is-active');
        const targetId = btn.dataset.guideTarget;
        document.getElementById(targetId)?.classList.add('is-active');
    });
});

// Modals Trigger Handlers
document.querySelector('#open-instructions-guide')?.addEventListener('click', () => document.querySelector('#instructions-dialog')?.showModal());
document.querySelector('#trigger-theory-modal')?.addEventListener('click', () => document.querySelector('#theory-dialog')?.showModal());
document.querySelector('#open-theory-guide')?.addEventListener('click', () => document.querySelector('#theory-dialog')?.showModal());
document.querySelector('#btn-open-templates')?.addEventListener('click', () => document.querySelector('#template-dialog')?.showModal());
document.querySelector('#btn-open-media')?.addEventListener('click', () => document.querySelector('#media-dialog')?.showModal());
document.querySelector('#btn-upload-figure-tab')?.addEventListener('click', () => document.querySelector('#media-dialog')?.showModal());
document.querySelector('#btn-open-citations')?.addEventListener('click', () => document.querySelector('#citation-dialog')?.showModal());
document.querySelector('#btn-add-citation')?.addEventListener('click', () => {
    document.querySelector('#citation-dialog')?.showModal();
    document.querySelector('#btn-cite-mode-new')?.click();
});
document.querySelector('#btn-invite-member')?.addEventListener('click', () => document.querySelector('#member-dialog')?.showModal());
document.querySelector('#btn-run-arch-audit')?.addEventListener('click', () => loadArchitectureAudit());
document.querySelector('#trigger-arch-audit')?.addEventListener('click', () => {
    document.querySelector('[data-sidebar-tab="directory"]')?.click();
    loadArchitectureAudit();
});

// Close Dialogs
document.querySelectorAll('[data-close-dialog]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelector(`#${btn.dataset.closeDialog}`)?.close();
    });
});

// Format panel toggle
document.querySelector('#open-format-panel')?.addEventListener('click', () => {
    const strip = document.querySelector('#paper-layout-strip');
    if (strip) strip.classList.toggle('is-hidden');
});

// Print / PDF Button
document.querySelector('#print-paper')?.addEventListener('click', () => {
    window.print();
});

// Apply Template
document.querySelectorAll('[data-template-id]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const templateHtml = PAPER_TEMPLATES[btn.dataset.templateId];
        if (templateHtml) {
            if (confirm('Apply this research template? This will set up the outline structure in your manuscript.')) {
                editor.innerHTML = templateHtml;
                document.querySelector('#template-dialog')?.close();
                saveState.textContent = 'Template outline applied';
                updateManuscriptStats();
            }
        }
    });
});

// Apply Methodology from Modal
document.querySelectorAll('[data-apply-method]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const method = btn.dataset.applyMethod;
        if (methodologySelect) {
            methodologySelect.value = method;
            updateTheoryCard(method);
            updateResearchFrame(method);
            document.querySelector('#theory-dialog')?.close();
            document.querySelector('#instructions-dialog')?.close();
            saveState.textContent = `Applied ${method} methodology stance`;
        }
    });
});

// Citation Dialog Modes
const btnCiteModeExisting = document.querySelector('#btn-cite-mode-existing');
const btnCiteModeNew = document.querySelector('#btn-cite-mode-new');
const existingCitationsContainer = document.querySelector('#existing-citations-container');
const citationForm = document.querySelector('#citation-form');

btnCiteModeExisting?.addEventListener('click', () => {
    btnCiteModeExisting.classList.add('is-active');
    btnCiteModeNew.classList.remove('is-active');
    existingCitationsContainer.style.display = 'block';
    citationForm.style.display = 'none';
});

btnCiteModeNew?.addEventListener('click', () => {
    btnCiteModeNew.classList.add('is-active');
    btnCiteModeExisting.classList.remove('is-active');
    existingCitationsContainer.style.display = 'none';
    citationForm.style.display = 'block';
});

// Insert citation tag listener
document.addEventListener('click', (e) => {
    const insertCitationBtn = e.target.closest('[data-insert-citation-tag]');
    if (insertCitationBtn) {
        insertInTextCitation(insertCitationBtn.dataset.insertCitationTag, insertCitationBtn.dataset.citeKey);
    }

    const insertFigureBtn = e.target.closest('[data-insert-figure-id]');
    if (insertFigureBtn) {
        const mediaItem = currentMedia.find((m) => m.id == insertFigureBtn.dataset.insertFigureId);
        if (mediaItem) insertFigureIntoEditor(mediaItem);
    }

    const restoreVersionBtn = e.target.closest('[data-restore-version]');
    if (restoreVersionBtn) {
        if (confirm('Restore this document version? Current unsaved edits will be replaced.')) {
            fetch(`/api/v1/projects/${projectId}`)
                .then((r) => r.json())
                .then(({ data }) => {
                    if (data.latest_version && data.latest_version.content.html) {
                        editor.innerHTML = data.latest_version.content.html;
                        saveState.textContent = `Restored version ${data.latest_version.version_number}`;
                        updateManuscriptStats();
                    }
                });
        }
    }
});

// Generate References
document.querySelector('#btn-append-bibliography')?.addEventListener('click', appendBibliographyToPaper);
document.querySelector('#btn-generate-references-action')?.addEventListener('click', appendBibliographyToPaper);

// Media Form Source Type Toggle (URL vs File)
document.querySelectorAll('input[name="media_source_type"]').forEach((radio) => {
    radio.addEventListener('change', (e) => {
        const isFile = e.target.value === 'file';
        document.querySelector('#media-file-field').style.display = isFile ? 'block' : 'none';
        document.querySelector('#media-url-field').style.display = isFile ? 'none' : 'block';
    });
});

// Media Form Submit
document.querySelector('#media-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const errorEl = document.querySelector('#media-form-error');
    errorEl.textContent = '';

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/media`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        });

        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Failed to upload figure');
        }

        form.reset();
        document.querySelector('#media-dialog')?.close();
        await loadWorkspace();
        insertFigureIntoEditor(payload.data);
    } catch (err) {
        errorEl.textContent = err.message;
    }
});

// Citation Form Submit
document.querySelector('#citation-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const errorEl = document.querySelector('#citation-form-error');
    errorEl.textContent = '';

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/citations`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(Object.fromEntries(formData)),
        });

        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Failed to save citation');
        }

        form.reset();
        document.querySelector('#citation-dialog')?.close();
        await loadWorkspace();
        insertInTextCitation(payload.data.in_text_tag, payload.data.key);
    } catch (err) {
        errorEl.textContent = err.message;
    }
});

// Invite Member Form Submit
document.querySelector('#member-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const errorEl = document.querySelector('#member-form-error');
    errorEl.textContent = '';

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/members`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(Object.fromEntries(formData)),
        });

        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Failed to add collaborator');
        }

        form.reset();
        document.querySelector('#member-dialog')?.close();
        await loadWorkspace();
    } catch (err) {
        errorEl.textContent = err.message;
    }
});

// Task Form Submit
document.querySelector('#task-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const titleInput = form.elements.title;
    if (!titleInput.value.trim()) return;

    await fetch(`/api/v1/projects/${projectId}/tasks`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ title: titleInput.value.trim(), priority: 'normal' }),
    });

    titleInput.value = '';
    await loadWorkspace();
});

// Feedback Form Submit
document.querySelector('#feedback-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const bodyInput = form.elements.body;
    if (!bodyInput.value.trim()) return;

    await fetch(`/api/v1/projects/${projectId}/feedback`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ body: bodyInput.value.trim() }),
    });

    bodyInput.value = '';
    await loadWorkspace();
});

// Save Version Button
document.querySelector('#save-version')?.addEventListener('click', async () => {
    saveState.textContent = 'Saving version…';
    const content = {
        html: editor.innerHTML,
        text: editor.innerText,
        words: (editor.innerText || '').trim().split(/\s+/).filter(Boolean).length,
    };

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/versions`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ content }),
        });

        if (response.ok) {
            saveState.textContent = 'All changes saved';
            await loadWorkspace();
        } else {
            saveState.textContent = 'Error saving version';
        }
    } catch (err) {
        saveState.textContent = 'Failed to save version';
    }
});

// Setup Form Submit
setupForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    setupState.textContent = 'Saving…';
    const response = await fetch(`/api/v1/projects/${projectId}/setup`, {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(Object.fromEntries(new FormData(setupForm))),
    });
    setupState.textContent = response.ok ? 'Saved' : 'Could not save';
});

// Task & Feedback Toggles
document.addEventListener('click', async (event) => {
    const taskToggle = event.target.closest('[data-task-id]');
    const feedbackToggle = event.target.closest('[data-feedback-id]');
    if (taskToggle) {
        const status = taskToggle.dataset.taskStatus === 'done' ? 'todo' : 'done';
        await fetch(`/api/v1/tasks/${taskToggle.dataset.taskId}`, {
            method: 'PATCH',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ status }),
        });
        await loadWorkspace();
    }
    if (feedbackToggle && !feedbackToggle.parentElement.classList.contains('is-resolved')) {
        await fetch(`/api/v1/feedback/${feedbackToggle.dataset.feedbackId}`, {
            method: 'PATCH',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'resolved' }),
        });
        await loadWorkspace();
    }
});

// AI Assistant button
aiSummaryButton?.addEventListener('click', () => runAiSummary());

// Live Manuscript Statistics (Word count, character count, reading time)
const updateManuscriptStats = () => {
    if (!editor) return;
    const text = editor.innerText || '';
    const words = text.trim() ? text.trim().split(/\s+/).filter(Boolean).length : 0;
    const chars = text.length;
    const readMinutes = Math.max(1, Math.ceil(words / 220));

    const wordEl = document.querySelector('#stat-words');
    const charEl = document.querySelector('#stat-chars');
    const timeEl = document.querySelector('#stat-read-time');

    if (wordEl) wordEl.textContent = `${words.toLocaleString()} words`;
    if (charEl) charEl.textContent = `${chars.toLocaleString()} characters`;
    if (timeEl) timeEl.textContent = `~${readMinutes} min read`;
};

editor?.addEventListener('input', updateManuscriptStats);

// Markdown Manuscript Exporter
const convertHtmlToMarkdown = (html) => {
    const temp = document.createElement('div');
    temp.innerHTML = html;

    temp.querySelectorAll('figure').forEach((fig) => {
        const img = fig.querySelector('img');
        const caption = fig.querySelector('figcaption');
        const src = img ? img.getAttribute('src') : '';
        const capText = caption ? caption.innerText.trim() : '';
        fig.outerHTML = `\n\n![${capText}](${src})\n*${capText}*\n\n`;
    });

    temp.querySelectorAll('h1').forEach((h) => { h.outerHTML = `\n\n# ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('h2').forEach((h) => { h.outerHTML = `\n\n## ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('h3').forEach((h) => { h.outerHTML = `\n\n### ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('h4').forEach((h) => { h.outerHTML = `\n\n#### ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('blockquote').forEach((bq) => { bq.outerHTML = `\n\n> ${bq.innerText.trim()}\n\n`; });
    temp.querySelectorAll('ul li').forEach((li) => { li.outerHTML = `- ${li.innerText.trim()}\n`; });
    temp.querySelectorAll('ol li').forEach((li, idx) => { li.outerHTML = `${idx + 1}. ${li.innerText.trim()}\n`; });
    temp.querySelectorAll('.cite-ref').forEach((ref) => { ref.outerHTML = `[${ref.innerText.trim()}]`; });
    temp.querySelectorAll('p').forEach((p) => { p.outerHTML = `\n\n${p.innerText.trim()}\n\n`; });

    return temp.innerText.replace(/\n{3,}/g, '\n\n').trim();
};

document.querySelector('#btn-export-markdown')?.addEventListener('click', () => {
    const title = currentProject?.title || 'manuscript';
    const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    const mdContent = `# ${title}\n\n` + convertHtmlToMarkdown(editor.innerHTML);

    const blob = new Blob([mdContent], { type: 'text/markdown;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${slug}.md`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    saveState.textContent = 'Exported manuscript to Markdown (.md)';
});

// Citation Search Filters
const filterCitations = (query) => {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.citation-item').forEach((item) => {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
    document.querySelectorAll('.quick-citation-option').forEach((item) => {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
};

document.querySelector('#citation-search')?.addEventListener('input', (e) => filterCitations(e.target.value));
document.querySelector('#modal-citation-search')?.addEventListener('input', (e) => filterCitations(e.target.value));
document.querySelector('#btn-refresh-ai-status')?.addEventListener('click', () => loadAiAvailability());

// =========================================================================
// MICROSOFT EXCEL RESEARCH METHODOLOGY & SECTION GOVERNANCE MATRIX
// =========================================================================

let excelMatrixData = [
    {
        row_id: 1,
        section: '1. Introduction & Problem Space',
        methodology: 'Problem Formulation & Value Proposition',
        ai_recommendation: 'Articulate SMART research question. State real-world impact. Ground in >= 8 peer-reviewed foundational papers.',
        author_rule: 'Focus on real-time co-authoring friction and supervisory review overhead.',
        target_words: '1,200',
        min_citations: 8,
        status: 'AI Recommended'
    },
    {
        row_id: 2,
        section: '2. Theoretical Grounding & Literature Gap',
        methodology: 'Knowledge Base Foundation (Hevner Guideline 2)',
        ai_recommendation: 'Ground in peer-reviewed seminal works; map state-of-the-art tools and articulate verifiable research gap.',
        author_rule: 'Cite seminal DSR guidelines (Hevner 2004, Gregor 2006) and collaborative editing systems.',
        target_words: '2,000',
        min_citations: 14,
        status: 'AI Recommended'
    },
    {
        row_id: 3,
        section: '3. Artifact Architecture & Methodological Cycles',
        methodology: 'Design as a Search Process (Hevner Guideline 6)',
        ai_recommendation: 'Detail iterative build cycles, architectural blueprints, data models, and functional specifications.',
        author_rule: 'Document multi-agent orchestration, WebSocket sync pipelines, and SQLite persistence engine.',
        target_words: '2,500',
        min_citations: 9,
        status: 'AI Recommended'
    },
    {
        row_id: 4,
        section: '4. Implementation & Rigorous Artifact Evaluation',
        methodology: 'Design Evaluation (Hevner Guideline 3)',
        ai_recommendation: 'Evaluate artifact utility, throughput, and reliability through empirical benchmarks or user studies.',
        author_rule: 'Conduct concurrent editing stress test (50 agents) and report latency percentiles.',
        target_words: '2,200',
        min_citations: 6,
        status: 'AI Recommended'
    },
    {
        row_id: 5,
        section: '5. Discussion & Research Contributions',
        methodology: 'Research Contributions (Hevner Guideline 4)',
        ai_recommendation: 'Contrast artifact capabilities against existing literature; explain design principles and generalizable patterns.',
        author_rule: 'Articulate clear architectural design principles for future multi-agent research tools.',
        target_words: '1,800',
        min_citations: 8,
        status: 'AI Recommended'
    },
    {
        row_id: 6,
        section: '6. Limitations, Rigor Audit & Future Horizons',
        methodology: 'Research Rigor & Communication (Hevner Guidelines 5 & 7)',
        ai_recommendation: 'Address technical constraints, scalability bottlenecks, deployment limitations, and next research vectors.',
        author_rule: 'Outline future work on distributed cloud federation and automated peer-review consensus.',
        target_words: '1,000',
        min_citations: 4,
        status: 'AI Recommended'
    }
];

let activeExcelCell = null;
let activeExcelCellAddress = 'D1';

const excelTbody = document.querySelector('#excel-tbody');
const excelNameBox = document.querySelector('#excel-name-box');
const excelFormulaInput = document.querySelector('#excel-formula-input');
const excelStatSections = document.querySelector('#excel-stat-sections');
const excelStatWords = document.querySelector('#excel-stat-words');
const excelStatRules = document.querySelector('#excel-stat-rules');
const excelStatusBar = document.querySelector('#excel-status-bar');
const excelMatrixWrapper = document.querySelector('#excel-matrix-wrapper');
const paperSheetViewport = document.querySelector('.paper-sheet-viewport');

const renderExcelMatrixTable = () => {
    if (!excelTbody) return;
    excelTbody.innerHTML = '';

    let totalWords = 0;
    let customizedCount = 0;

    excelMatrixData.forEach((row, index) => {
        const rowNum = index + 1;
        const wordsNum = parseInt(String(row.target_words).replace(/[^0-9]/g, '')) || 0;
        totalWords += wordsNum;
        if (row.status?.includes('Customized') || row.status?.includes('Author')) {
            customizedCount++;
        }

        const tr = document.createElement('tr');
        tr.className = 'excel-tr';
        tr.dataset.rowId = row.row_id || rowNum;
        tr.dataset.rowIndex = index;

        const isAuthorCustom = (row.status || '').includes('Customized') || (row.status || '').includes('Author');
        const statusClass = isAuthorCustom ? 'status-custom' : ((row.status || '').includes('Applied') ? 'status-applied' : 'status-ai');

        tr.innerHTML = `
            <td class="excel-td-row-num">${rowNum}</td>
            <td class="excel-td-cell" contenteditable="true" spellcheck="false" data-col="A" data-field="section">${escapeHtml(row.section || '')}</td>
            <td class="excel-td-cell" contenteditable="true" spellcheck="false" data-col="B" data-field="methodology">${escapeHtml(row.methodology || '')}</td>
            <td class="excel-td-cell" contenteditable="true" spellcheck="false" data-col="C" data-field="ai_recommendation">${escapeHtml(row.ai_recommendation || '')}</td>
            <td class="excel-td-cell excel-td-author-rule ${isAuthorCustom ? 'is-customized' : ''}" contenteditable="true" spellcheck="false" data-col="D" data-field="author_rule">${escapeHtml(row.author_rule || '')}</td>
            <td class="excel-td-cell" contenteditable="true" spellcheck="false" data-col="E" data-field="target_words" style="text-align: right;">${escapeHtml(String(row.target_words || ''))}</td>
            <td class="excel-td-cell" contenteditable="true" spellcheck="false" data-col="F" data-field="min_citations" style="text-align: right;">${escapeHtml(String(row.min_citations || ''))}</td>
            <td class="excel-td-cell" data-col="G" style="text-align: center;"><span class="cell-status-badge ${statusClass}">${escapeHtml(row.status || 'AI Recommended')}</span></td>
            <td class="excel-td-cell" data-col="H" style="text-align: center;"><button type="button" class="excel-cell-del-btn" title="Delete section row">✕</button></td>
        `;

        // Wire cell click / focus / input listeners
        tr.querySelectorAll('.excel-td-cell[contenteditable]').forEach((cell) => {
            cell.addEventListener('focus', () => selectExcelCell(cell, index));
            cell.addEventListener('click', () => selectExcelCell(cell, index));

            cell.addEventListener('input', () => {
                const field = cell.dataset.field;
                const value = cell.innerText.trim();
                row[field] = value;

                // When user edits Author Rule in Column D, mark as Customized by Author
                if (field === 'author_rule') {
                    row.status = 'Customized by Author';
                    cell.classList.add('is-customized');
                    const badge = tr.querySelector('.cell-status-badge');
                    if (badge) {
                        badge.className = 'cell-status-badge status-custom';
                        badge.textContent = 'Customized by Author';
                    }
                }

                if (excelFormulaInput && activeExcelCell === cell) {
                    excelFormulaInput.value = cell.innerText;
                }
                updateExcelStats();
            });
        });

        // Delete Row Button
        tr.querySelector('.excel-cell-del-btn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (confirm(`Remove section row "${row.section}"?`)) {
                excelMatrixData.splice(index, 1);
                renderExcelMatrixTable();
            }
        });

        excelTbody.appendChild(tr);
    });

    updateExcelStats();
};

const selectExcelCell = (cell, rowIndex) => {
    if (activeExcelCell) {
        activeExcelCell.classList.remove('is-selected');
    }
    activeExcelCell = cell;
    cell.classList.add('is-selected');

    const colLetter = cell.dataset.col || 'A';
    const rowNum = rowIndex + 1;
    activeExcelCellAddress = `${colLetter}${rowNum}`;

    if (excelNameBox) {
        excelNameBox.textContent = activeExcelCellAddress;
    }
    if (excelFormulaInput) {
        excelFormulaInput.value = cell.innerText;
    }
};

const updateExcelStats = () => {
    let totalWords = 0;
    let customRules = 0;
    excelMatrixData.forEach((r) => {
        totalWords += parseInt(String(r.target_words).replace(/[^0-9]/g, '')) || 0;
        if ((r.status || '').includes('Customized') || (r.status || '').includes('Author')) {
            customRules++;
        }
    });

    if (excelStatSections) excelStatSections.textContent = `${excelMatrixData.length} Sections`;
    if (excelStatWords) excelStatWords.textContent = `Sum Words: ${totalWords.toLocaleString()}`;
    if (excelStatRules) {
        excelStatRules.textContent = customRules > 0
            ? `Rules: ${customRules} Custom / ${excelMatrixData.length} Total (Author Customized)`
            : `Rules: ${excelMatrixData.length} Active (Click any cell in Column D to change)`;
    }
};

// Two-way synchronization from Formula Input to Active Cell
excelFormulaInput?.addEventListener('input', () => {
    if (!activeExcelCell) return;
    const val = excelFormulaInput.value;
    activeExcelCell.innerText = val;

    const tr = activeExcelCell.closest('tr');
    const rowIndex = parseInt(tr?.dataset.rowIndex || '0');
    const field = activeExcelCell.dataset.field;
    if (excelMatrixData[rowIndex] && field) {
        excelMatrixData[rowIndex][field] = val;
        if (field === 'author_rule') {
            excelMatrixData[rowIndex].status = 'Customized by Author';
            activeExcelCell.classList.add('is-customized');
            const badge = tr.querySelector('.cell-status-badge');
            if (badge) {
                badge.className = 'cell-status-badge status-custom';
                badge.textContent = 'Customized by Author';
            }
        }
    }
    updateExcelStats();
});

// View Switcher (Split, Excel Matrix Only, Canvas Only)
document.querySelectorAll('.view-switch-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.view-switch-btn').forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');

        const mode = btn.dataset.viewMode;
        if (mode === 'excel') {
            if (excelMatrixWrapper) excelMatrixWrapper.style.display = 'block';
            if (paperSheetViewport) paperSheetViewport.style.display = 'none';
        } else if (mode === 'canvas') {
            if (excelMatrixWrapper) excelMatrixWrapper.style.display = 'none';
            if (paperSheetViewport) paperSheetViewport.style.display = 'block';
        } else {
            // Split View
            if (excelMatrixWrapper) excelMatrixWrapper.style.display = 'block';
            if (paperSheetViewport) paperSheetViewport.style.display = 'block';
        }
    });
});

// Toolbar jump to Excel
document.querySelector('#btn-open-excel-matrix')?.addEventListener('click', () => {
    if (excelMatrixWrapper) {
        excelMatrixWrapper.style.display = 'block';
        excelMatrixWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Set split view active
        document.querySelectorAll('.view-switch-btn').forEach((b) => {
            b.classList.toggle('is-active', b.dataset.viewMode === 'split');
        });
    }
});

// Collapse / Minimize Excel Matrix
document.querySelector('#btn-excel-toggle-collapse')?.addEventListener('click', (e) => {
    excelMatrixWrapper?.classList.toggle('is-collapsed');
    e.target.textContent = excelMatrixWrapper?.classList.contains('is-collapsed') ? '+' : '−';
});

// Ask Alice (AI Agent Recommendation)
document.querySelector('#btn-excel-ai-recommend')?.addEventListener('click', async () => {
    const btn = document.querySelector('#btn-excel-ai-recommend');
    if (!btn) return;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="btn-icon">⏳</span><span class="btn-text"><strong>Alice is analyzing...</strong><small>Generating methodology matrix</small></span>`;

    if (excelStatusBar) {
        excelStatusBar.innerHTML = `<span style="color: #0284c7; font-weight: 700;">🤖 Alice AI Copilot is synthesizing academic methodology and section rules...</span>`;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/api/v1/projects/${projectId}/ai/methodology-matrix`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify({
                methodology_preference: methodologySelect?.value || '',
            }),
        });

        const data = await response.json();
        if (response.ok && data?.data?.matrix) {
            excelMatrixData = data.data.matrix.map((r, i) => ({
                row_id: r.row_id || i + 1,
                section: r.section || `Section ${i + 1}`,
                methodology: r.methodology || 'Scientific Inquiry',
                ai_recommendation: r.ai_recommendation || 'Grounded in peer-reviewed methodology.',
                author_rule: r.author_rule || 'Author custom constraint.',
                target_words: r.target_words || '1,500',
                min_citations: r.min_citations || 8,
                status: 'AI Recommended',
            }));

            renderExcelMatrixTable();

            if (runningMethodology && data.data.overall_methodology) {
                runningMethodology.textContent = `· ${data.data.overall_methodology}`;
            }

            if (saveState) {
                saveState.textContent = 'Alice AI methodology recommendations applied to Excel matrix';
            }
        } else {
            alert('Could not retrieve AI methodology recommendation. Using standard framework.');
        }
    } catch (err) {
        console.error('Error fetching AI methodology matrix:', err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
        updateExcelStats();
    }
});

// Apply Matrix to Manuscript Canvas
document.querySelector('#btn-excel-apply-canvas')?.addEventListener('click', () => {
    if (!editor) return;

    if (!confirm('Apply these sections and author rules to your manuscript canvas? This will structure your outline.')) {
        return;
    }

    let manuscriptHtml = '';
    excelMatrixData.forEach((row) => {
        manuscriptHtml += `<h2>${escapeHtml(row.section)}</h2>\n`;
        manuscriptHtml += `<div class="manuscript-section-rule" contenteditable="false">\n`;
        manuscriptHtml += `    <span class="rule-badge-author">Author Rule</span> <strong>${escapeHtml(row.author_rule || 'Follow research plan')}</strong>\n`;
        manuscriptHtml += `    <br><span class="rule-badge-ai">Alice AI Guideline</span> <small>${escapeHtml(row.ai_recommendation || '')}</small>\n`;
        manuscriptHtml += `    <div style="margin-top: 5px; font-family: monospace; font-size: 10px; color: #166534;">\n`;
        manuscriptHtml += `        Methodology: ${escapeHtml(row.methodology)} · Target: ${escapeHtml(String(row.target_words))} words · Min Citations: ${escapeHtml(String(row.min_citations))}\n`;
        manuscriptHtml += `    </div>\n`;
        manuscriptHtml += `</div>\n`;
        manuscriptHtml += `<p>Document your inquiry and empirical evidence for ${escapeHtml(row.section)} here...</p>\n\n`;

        row.status = 'Applied to Paper';
    });

    editor.innerHTML = manuscriptHtml;
    renderExcelMatrixTable();
    updateManuscriptStats();

    // Ensure paper viewport is visible
    if (paperSheetViewport) {
        paperSheetViewport.style.display = 'block';
    }

    if (saveState) {
        saveState.textContent = `Applied ${excelMatrixData.length} sections & customized author rules to manuscript`;
    }
});

// Insert Table into Paper
document.querySelector('#btn-excel-insert-table')?.addEventListener('click', () => {
    if (!editor) return;

    let tableHtml = `\n<table class="paper-methodology-table" contenteditable="false">\n`;
    tableHtml += `    <caption><strong>Table 1:</strong> Research Methodology and Section Governance Matrix (Author-Defined Rules & AI Recommendations)</caption>\n`;
    tableHtml += `    <thead>\n`;
    tableHtml += `        <tr>\n`;
    tableHtml += `            <th>Section / Chapter</th>\n`;
    tableHtml += `            <th>Methodology Paradigm</th>\n`;
    tableHtml += `            <th>Author Governance Rule</th>\n`;
    tableHtml += `            <th>AI Rigor Guideline (Alice)</th>\n`;
    tableHtml += `            <th style="text-align: right;">Target Words</th>\n`;
    tableHtml += `            <th style="text-align: right;">Cites</th>\n`;
    tableHtml += `        </tr>\n`;
    tableHtml += `    </thead>\n`;
    tableHtml += `    <tbody>\n`;

    excelMatrixData.forEach((row) => {
        tableHtml += `        <tr>\n`;
        tableHtml += `            <td><strong>${escapeHtml(row.section)}</strong></td>\n`;
        tableHtml += `            <td>${escapeHtml(row.methodology)}</td>\n`;
        tableHtml += `            <td>${escapeHtml(row.author_rule)}</td>\n`;
        tableHtml += `            <td><small>${escapeHtml(row.ai_recommendation)}</small></td>\n`;
        tableHtml += `            <td style="text-align: right;">${escapeHtml(String(row.target_words))}</td>\n`;
        tableHtml += `            <td style="text-align: right;">${escapeHtml(String(row.min_citations))}</td>\n`;
        tableHtml += `        </tr>\n`;
    });

    tableHtml += `    </tbody>\n`;
    tableHtml += `    <tfoot>\n`;
    tableHtml += `        <tr>\n`;
    tableHtml += `            <td colspan="6"><em>Note: Methodologies recommended by Alice AI Copilot; governance rules and empirical thresholds verified and customized by study author.</em></td>\n`;
    tableHtml += `        </tr>\n`;
    tableHtml += `    </tfoot>\n`;
    tableHtml += `</table>\n<p></p>\n`;

    editor.innerHTML += tableHtml;
    updateManuscriptStats();

    if (saveState) {
        saveState.textContent = 'Embedded Table 1 (Methodology Matrix) into manuscript canvas';
    }
});

// Add Section Row
document.querySelector('#btn-excel-add-row')?.addEventListener('click', () => {
    const nextId = excelMatrixData.length + 1;
    excelMatrixData.push({
        row_id: nextId,
        section: `${nextId}. New Custom Section`,
        methodology: 'Empirical Verification',
        ai_recommendation: 'Specify clear hypotheses, measurement variables, and validity controls.',
        author_rule: 'Author defined constraint for this section.',
        target_words: '1,500',
        min_citations: 6,
        status: 'Customized by Author',
    });
    renderExcelMatrixTable();
});

// Delete Selected Row
document.querySelector('#btn-excel-delete-row')?.addEventListener('click', () => {
    if (!activeExcelCell) {
        alert('Please click on a row to select it first.');
        return;
    }
    const tr = activeExcelCell.closest('tr');
    const rowIndex = parseInt(tr?.dataset.rowIndex || '0');
    if (confirm(`Delete row ${rowIndex + 1}?`)) {
        excelMatrixData.splice(rowIndex, 1);
        activeExcelCell = null;
        renderExcelMatrixTable();
    }
});

// Reset Standard Outline
document.querySelector('#btn-excel-reset')?.addEventListener('click', () => {
    if (confirm('Reset matrix to standard Design Science / IMRaD research outline?')) {
        location.reload();
    }
});

// Export CSV
document.querySelector('#btn-excel-export')?.addEventListener('click', () => {
    let csv = 'Section,Methodology,AI_Recommendation,Author_Rule,Target_Words,Citations,Status\n';
    excelMatrixData.forEach((row) => {
        const clean = (str) => `"${String(str || '').replace(/"/g, '""')}"`;
        csv += `${clean(row.section)},${clean(row.methodology)},${clean(row.ai_recommendation)},${clean(row.author_rule)},${clean(row.target_words)},${clean(row.min_citations)},${clean(row.status)}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Co-Auth_Methodology_Matrix.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});

// Initialize Excel Matrix on page load
renderExcelMatrixTable();

// Initial Load
loadWorkspace();
