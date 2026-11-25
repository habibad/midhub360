<?php
namespace Midhub\Pdf;

/**
 * Minimal dependency-free PDF 1.4 writer for one-page receipts (Helvetica, lines, boxes).
 * Coordinates are in points from the TOP-left corner (converted internally). ASCII text
 * only: non-ASCII characters are replaced, which is fine for receipts in English.
 */
final class SimplePdf
{
    /** @var float */ private $w;
    /** @var float */ private $h;
    /** @var string[] */ private $ops = array();

    public function __construct(float $width = 420.0, float $height = 595.0) // A5 portrait
    {
        $this->w = $width;
        $this->h = $height;
    }

    public function text(float $x, float $y, string $s, float $size = 10, bool $bold = false, string $align = 'left'): self
    {
        $s = self::ascii($s);
        if ($align !== 'left') {
            $width = self::width($s, $size, $bold);
            $x = $align === 'right' ? $x - $width : $x - $width / 2;
        }
        $this->ops[] = sprintf('BT /%s %.2F Tf %.2F %.2F Td (%s) Tj ET', $bold ? 'F2' : 'F1', $size, $x, $this->h - $y, self::esc($s));
        return $this;
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.6, float $gray = 0.75): self
    {
        $this->ops[] = sprintf('%.2F G %.2F w %.2F %.2F m %.2F %.2F l S', $gray, $width, $x1, $this->h - $y1, $x2, $this->h - $y2);
        return $this;
    }

    public function box(float $x, float $y, float $w, float $h, float $fillGray = 0.95): self
    {
        $this->ops[] = sprintf('%.2F g %.2F %.2F %.2F %.2F re f 0 g', $fillGray, $x, $this->h - $y - $h, $w, $h);
        return $this;
    }

    public function output(): string
    {
        $content = implode("\n", $this->ops);
        $objs = array(
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            sprintf('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>', $this->w, $this->h),
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream",
        );
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = array();
        foreach ($objs as $i => $o) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $o . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }
        $pdf .= "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
        return $pdf;
    }

    private static function esc(string $s): string
    {
        return str_replace(array('\\', '(', ')', "\r", "\n"), array('\\\\', '\\(', '\\)', ' ', ' '), $s);
    }

    private static function ascii(string $s): string
    {
        $out = preg_replace('/[^\x20-\x7E]/', '?', $s);
        return $out === null ? '' : $out;
    }

    /** Approximate Helvetica text width (average glyph widths), good enough for alignment. */
    private static function width(string $s, float $size, bool $bold): float
    {
        $w = 0.0;
        for ($i = 0, $n = strlen($s); $i < $n; $i++) {
            $c = $s[$i];
            $w += ctype_digit($c) ? 556 : (ctype_upper($c) ? 690 : ($c === ' ' ? 278 : ($c === '.' || $c === ',' || $c === ':' ? 278 : 500)));
        }
        return $w * $size / 1000 * ($bold ? 1.04 : 1.0);
    }
}
