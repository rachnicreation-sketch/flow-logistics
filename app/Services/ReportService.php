<?php

declare(strict_types=1);

namespace App\Services;

final class ReportService
{
    public function outputStructuredPdf(
        string $title,
        string $subtitle,
        array $columns,
        array $rows,
        array $summary,
        string $filename
    ): void {
        $pageWidth = 612.0;
        $pageHeight = 792.0;
        $margin = 36.0;
        $usableWidth = $pageWidth - ($margin * 2);

        $stream = [];
        $y = $pageHeight - $margin;

        $stream[] = '0.12 0.20 0.32 rg';
        $stream[] = sprintf('%.2f %.2f %.2f %.2f re f', $margin, $pageHeight - 90, $usableWidth, 54.0);

        $stream[] = '1 1 1 rg';
        $stream[] = $this->textCmd($margin + 16, $pageHeight - 58, 18, $title, 'B');
        $stream[] = $this->textCmd($margin + 16, $pageHeight - 78, 10, $subtitle, 'R');

        $y = $pageHeight - 112;

        $summaryLabel = [];
        foreach ($summary as $key => $value) {
            $summaryLabel[] = $this->normalize((string) $key) . ': ' . $this->normalize((string) $value);
        }
        $summaryText = implode('  |  ', $summaryLabel);

        $stream[] = '0.05 0.10 0.20 rg';
        $stream[] = $this->textCmd($margin, $y, 10, 'Genere le: ' . date('d/m/Y H:i'), 'R');
        $stream[] = $this->textCmd($margin + 200, $y, 10, $summaryText, 'R');

        $y -= 18;

        $weights = [];
        $headers = [];
        foreach ($columns as $col) {
            $headers[] = (string) ($col['label'] ?? '');
            $weights[] = max(1.0, (float) ($col['weight'] ?? 1.0));
        }
        $weightTotal = array_sum($weights);
        $widths = array_map(static fn (float $w): float => ($w / $weightTotal) * $usableWidth, $weights);

        $headerHeight = 20.0;
        $rowHeight = 16.0;

        $stream[] = '0.90 0.94 0.98 rg';
        $stream[] = sprintf('%.2f %.2f %.2f %.2f re f', $margin, $y - $headerHeight + 4, $usableWidth, $headerHeight);

        $cursorX = $margin + 4;
        foreach ($headers as $idx => $header) {
            $stream[] = '0.08 0.14 0.24 rg';
            $stream[] = $this->textCmd($cursorX, $y - 10, 9, $header, 'B');
            $cursorX += $widths[$idx];
        }

        $y -= $headerHeight;

        $rowIndex = 0;
        foreach ($rows as $row) {
            if ($y < ($margin + 38)) {
                break;
            }

            if ($rowIndex % 2 === 0) {
                $stream[] = '0.97 0.98 0.99 rg';
                $stream[] = sprintf('%.2f %.2f %.2f %.2f re f', $margin, $y - $rowHeight + 3, $usableWidth, $rowHeight);
            }

            $cursorX = $margin + 4;
            foreach ($headers as $colIdx => $header) {
                $value = (string) ($row[$header] ?? '-');
                $stream[] = '0.08 0.14 0.24 rg';
                $maxChars = max(8, (int) floor(($widths[$colIdx] - 8) / 4));
                $stream[] = $this->textCmd($cursorX, $y - 9, 8.5, $value, 'R', $maxChars);
                $cursorX += $widths[$colIdx];
            }

            $y -= $rowHeight;
            $rowIndex++;
        }

        $stream[] = '0.7 0.75 0.82 RG';
        $stream[] = sprintf('%.2f w', 0.6);
        $stream[] = sprintf('%.2f %.2f m %.2f %.2f l S', $margin, $y + $rowHeight - 2, $margin + $usableWidth, $y + $rowHeight - 2);

        $stream[] = '0.38 0.44 0.52 rg';
        $stream[] = $this->textCmd($margin, $margin - 4, 8, 'Flow Logistics - Rapport interne', 'R');

        $content = "q\n" . implode("\n", $stream) . "\nQ";
        $pdf = $this->buildPdf($content);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    public function outputSimplePdf(string $title, array $lines, string $filename): void
    {
        $rows = [];
        foreach ($lines as $line) {
            $rows[] = ['Ligne' => $line];
        }

        $this->outputStructuredPdf(
            $title,
            'Version detaillee',
            [['label' => 'Ligne', 'weight' => 1]],
            $rows,
            ['Total lignes' => count($rows)],
            $filename
        );
    }

    private function textCmd(float $x, float $y, float $size, string $text, string $font = 'R', int $maxChars = 120): string
    {
        $fontName = $font === 'B' ? 'F2' : 'F1';
        $safe = $this->escapePdfText($this->truncate($this->normalize($text), $maxChars));

        return sprintf('BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET', $fontName, $size, $x, $y, $safe);
    }

    private function buildPdf(string $contentStream): string
    {
        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >> endobj';
        $objects[] = '4 0 obj << /Length ' . strlen($contentStream) . " >> stream\n{$contentStream}\nendstream endobj";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objects[] = '6 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj . "\n";
        }

        $xrefStart = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= "startxref\n";
        $pdf .= $xrefStart . "\n";
        $pdf .= "%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        return $text;
    }

    private function normalize(string $text): string
    {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted === false) {
            return $text;
        }

        return preg_replace('/[^\x20-\x7E]/', '', $converted) ?? $converted;
    }

    private function truncate(string $text, int $maxChars): string
    {
        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return substr($text, 0, max(1, $maxChars - 1)) . '...';
    }
}
