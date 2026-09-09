<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Renders donation receipts (and other documents that may contain Bengali
 * text alongside English/numbers/currency symbols) to PDF using mPDF.
 *
 * dompdf (this project's other PDF generator, still used for the tabular
 * admin reports) has no complex-script shaping engine: it draws every
 * Unicode codepoint in raw storage order, so Bengali vowel signs and
 * conjuncts land in the wrong visual position no matter which Bengali font
 * is embedded, on top of the bundled default font (DejaVu Sans) not
 * containing Bengali glyphs at all (which is what previously produced the
 * literal "????" in downloaded receipts). This was verified by rendering
 * both engines to an image and comparing the output.
 *
 * mPDF ships its own OpenType-shaping ("OTL") engine and, with
 * autoScriptToLang/autoLangToFont enabled, automatically switches font
 * whenever it encounters Bengali codepoints, while keeping Latin text in
 * the normal default font — both scripts render correctly in the same
 * line. mPDF's own bundled Bengali-capable font (FreeSerif, GNU FreeFont)
 * is what autoLangToFont selects by default, but its Bold face ships with
 * zero Bengali glyphs (verified: bold Bengali text fell back to tofu
 * boxes), so `resources/fonts/SolaimanLipi(.ttf|_Bold.ttf)` is registered
 * below in its place — a Unicode font with full Bengali, Latin, digit and
 * Bengali-Taka-sign (৳) coverage in both weights, still shaped by mPDF's
 * own OTL engine (`useOTL`).
 */
class ReceiptPdfService
{
    public function download(string $view, array $data, string $filename): StreamedResponse
    {
        $mpdf = $this->makeMpdf();

        $mpdf->WriteHTML(view($view, $data)->render());

        return response()->streamDownload(
            fn () => print($mpdf->Output($filename, Destination::STRING_RETURN)),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    protected function makeMpdf(): Mpdf
    {
        $fontDirs = (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'];
        $fontDirs[] = resource_path('fonts');

        // mPDF's own bundled "freeserif" is what autoLangToFont switches to
        // for Bengali script text, and its OTL shaping engine renders it
        // correctly — except GNU FreeSerif's Bold face has zero Bengali
        // glyphs (verified: any bold Bengali text falls back to tofu boxes).
        // SolaimanLipi (+ its real Bold face) has full Bengali, Latin,
        // digit and Bengali-Taka-sign (৳) coverage, and shapes correctly
        // through the same OTL engine, so it replaces both faces here.
        $fontData['freeserif']['R'] = 'SolaimanLipi.ttf';
        $fontData['freeserif']['B'] = 'SolaimanLipi_Bold.ttf';
        $fontData['freeserif']['useOTL'] = 0xFF;

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'fontDir' => $fontDirs,
            'fontdata' => $fontData,
            'tempDir' => storage_path('app/mpdf-temp'),
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);
    }
}
