<?php

namespace App\Http\Controllers;

use App\Models\ResearchProject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ManuscriptExportController extends Controller
{
    /**
     * Export manuscript as a complete, compilable LaTeX source file (.tex).
     */
    public function latex(Request $request, ResearchProject $project): Response
    {
        $project->load(['citations', 'documentVersions' => fn ($q) => $q->latest()->limit(1)]);

        $latestVersion = $project->documentVersions->first();
        $htmlContent = data_get($latestVersion, 'content.html', '');

        $title = $project->title ?: 'Research Manuscript';
        $author = $project->owner?->name ?: 'Research Team';
        $abstract = $project->description ?: 'This study presents an investigation into collaborative research systems.';

        $latexBody = $this->convertHtmlToLatex($htmlContent);

        $texLines = [];
        $texLines[] = '\documentclass[11pt,a4paper]{article}';
        $texLines[] = '\usepackage[utf8]{inputenc}';
        $texLines[] = '\usepackage{amsmath,amsfonts,amssymb}';
        $texLines[] = '\usepackage{graphicx}';
        $texLines[] = '\usepackage{cite}';
        $texLines[] = '\usepackage{hyperref}';
        $texLines[] = '\usepackage[margin=1.0in]{geometry}';
        $texLines[] = '';
        $texLines[] = '\title{'.$this->sanitizeLatex($title).'}';
        $texLines[] = '\author{'.$this->sanitizeLatex($author).'}';
        $texLines[] = '\date{\today}';
        $texLines[] = '';
        $texLines[] = '\begin{document}';
        $texLines[] = '\maketitle';
        $texLines[] = '';
        $texLines[] = '\begin{abstract}';
        $texLines[] = $this->sanitizeLatex($abstract);
        $texLines[] = '\end{abstract}';
        $texLines[] = '';
        $texLines[] = $latexBody ?: "\\section{Introduction}\nDraft your research inquiry here.\n";
        $texLines[] = '';

        if ($project->citations->isNotEmpty()) {
            $texLines[] = '\begin{thebibliography}{99}';
            foreach ($project->citations as $index => $citation) {
                $num = $index + 1;
                $key = $citation->key ?: 'ref'.$num;
                $auth = $this->sanitizeLatex($citation->authors ?: 'Unknown');
                $titl = $this->sanitizeLatex($citation->title ?: 'Untitled');
                $jour = $this->sanitizeLatex($citation->source ?: 'Publication');
                $yr = $citation->year ?: '';
                $texLines[] = '\\bibitem{'.$key.'} '.$auth.'. \\textit{'.$titl.'}. '.$jour.', '.$yr.'.';
            }
            $texLines[] = '\end{thebibliography}';
        }

        $texLines[] = '\end{document}';

        $output = implode("\n", $texLines);
        $filename = Str::slug($title).'-manuscript.tex';

        return response($output, 200, [
            'Content-Type' => 'application/x-latex; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export manuscript as a formatted Microsoft Word document (.doc).
     */
    public function word(Request $request, ResearchProject $project): Response
    {
        $project->load(['citations', 'documentVersions' => fn ($q) => $q->latest()->limit(1)]);

        $latestVersion = $project->documentVersions->first();
        $htmlContent = data_get($latestVersion, 'content.html', '');

        $title = htmlspecialchars($project->title ?: 'Research Manuscript');
        $author = htmlspecialchars($project->owner?->name ?: 'Research Author');
        $discipline = htmlspecialchars($project->discipline ?: 'Multidisciplinary');
        $methodology = htmlspecialchars($project->methodology ?: 'Design Science Research');

        $wordHtml = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        $wordHtml .= '<head><meta charset="utf-8"><title>'.$title.'</title>';
        $wordHtml .= '<style>';
        $wordHtml .= 'body { font-family: "Times New Roman", serif; font-size: 12pt; line-height: 1.5; margin: 1in; color: #172c35; }';
        $wordHtml .= 'h1 { font-size: 22pt; font-family: "Arial", sans-serif; text-align: center; margin-bottom: 8pt; font-weight: bold; }';
        $wordHtml .= 'h2 { font-size: 15pt; font-family: "Arial", sans-serif; margin-top: 18pt; margin-bottom: 6pt; border-bottom: 1pt solid #ccc; font-weight: bold; }';
        $wordHtml .= 'h3 { font-size: 13pt; font-family: "Arial", sans-serif; margin-top: 12pt; margin-bottom: 4pt; }';
        $wordHtml .= 'p { margin-bottom: 10pt; text-align: justify; }';
        $wordHtml .= '.author-block { text-align: center; font-size: 12pt; margin-bottom: 18pt; color: #555; }';
        $wordHtml .= '.meta-pill { font-size: 10pt; font-family: monospace; color: #007f7a; text-align: center; margin-bottom: 24pt; }';
        $wordHtml .= 'table { width: 100%; border-collapse: collapse; margin: 16pt 0; }';
        $wordHtml .= 'th, td { border: 1pt solid #bbb; padding: 6pt 8pt; text-align: left; }';
        $wordHtml .= 'th { background: #f2f2f2; font-weight: bold; }';
        $wordHtml .= '</style></head><body>';
        $wordHtml .= '<h1>'.$title.'</h1>';
        $wordHtml .= '<div class="author-block"><strong>'.$author.'</strong><br>'.$discipline.'</div>';
        $wordHtml .= '<div class="meta-pill">Methodology: '.$methodology.' &middot; Co-Auth Research Platform Studio</div>';
        $wordHtml .= $htmlContent ?: '<p>Start your research manuscript drafting here.</p>';
        $wordHtml .= '</body></html>';

        $filename = Str::slug($project->title).'-manuscript.doc';

        return response($wordHtml, 200, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Convert manuscript HTML into standard LaTeX.
     */
    protected function convertHtmlToLatex(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $text = $html;

        // Strip script/style
        $text = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $text);

        // Convert headings
        $text = preg_replace_callback('/<h2>(.*?)<\/h2>/is', fn ($m): string => '\\section{'.$this->sanitizeLatex(strip_tags($m[1]))."}\n", $text);
        $text = preg_replace_callback('/<h3>(.*?)<\/h3>/is', fn ($m): string => '\\subsection{'.$this->sanitizeLatex(strip_tags($m[1]))."}\n", $text);
        $text = preg_replace_callback('/<h4>(.*?)<\/h4>/is', fn ($m): string => '\\subsubsection{'.$this->sanitizeLatex(strip_tags($m[1]))."}\n", $text);

        // Convert citations
        $text = preg_replace('/<span class="cite-ref"[^>]*data-cite-key="([^"]+)"[^>]*>.*?<\/span>/i', '\\cite{$1}', $text);

        // Convert bold & italic
        $text = preg_replace('/<(strong|b)>(.*?)<\/\1>/is', '\\textbf{$2}', $text);
        $text = preg_replace('/<(em|i)>(.*?)<\/\1>/is', '\\textit{$2}', $text);

        // Convert paragraphs
        $text = preg_replace('/<p>(.*?)<\/p>/is', "$1\n\n", $text);

        // Strip remaining HTML tags
        $text = strip_tags($text);

        return trim($text);
    }

    /**
     * Escape special LaTeX characters.
     */
    protected function sanitizeLatex(string $text): string
    {
        $search = ['\\', '{', '}', '$', '&', '#', '^', '_', '%', '~'];
        $replace = ['\textbackslash{}', '\{', '\}', '\$', '\&', '\#', '\textasciicircum{}', '\_', '\%', '\textasciitilde{}'];

        return str_replace($search, $replace, $text);
    }
}
