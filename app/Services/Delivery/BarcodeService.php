<?php

namespace App\Services\Delivery;

class BarcodeService
{
    /**
     * Standard ISO/IEC 15417 Code 128 pattern table (0 - 106).
     * Each pattern is a sequence of alternating widths for bars and spaces.
     */
    protected static array $patterns = [
        '212222','222122','222221','121223','121322','131222','122213','122312','132212','221213',
        '221312','231212','112232','122132','122231','113222','123122','123221','223211','221132',
        '221231','213212','223112','312131','311222','321122','321221','312212','322112','322211',
        '212123','212321','232121','111323','131123','131321','112313','132113','132311','211313',
        '231113','231311','112133','112331','132131','113123','113321','133121','313121','211331',
        '231131','213113','213311','213131','311123','311321','331121','312113','312311','332111',
        '314111','221411','431111','111224','111422','121124','121421','141122','141221','112214',
        '112412','122114','122411','142112','142211','241211','221114','413111','241112','134111',
        '111242','121142','121241','114212','124112','124211','411212','421112','421211','212141',
        '214121','412121','111143','111341','131141','114113','114311','411113','411311','113141',
        '114131','311141','411131',
        '211412', // 103: Start A
        '211214', // 104: Start B
        '211232', // 105: Start C
        '2331112' // 106: Stop (13 modules)
    ];

    /**
     * Generate 100% compliant, vector SVG Code 128 barcode
     * Optimized for Delhivery / Shiprocket handheld laser & optical scanners
     *
     * @param string $text Barcode data (AWB number, order ID, etc.)
     * @param int $height Total SVG height in pixels
     * @param int $moduleWidth Width of single narrowest bar in pixels (2px recommended for 203 DPI thermal)
     * @param bool $showText Whether to render human readable text below barcode
     * @param int $quietZone White margin modules on left & right (standard >= 10)
     * @return string Standalone SVG element
     */
    public static function getCode128Svg(
        string $text,
        int $height = 45,
        float|int $moduleWidth = 1.8,
        bool $showText = true,
        int $quietZone = 12
    ): string {
        $cleanText = trim($text);
        if ($cleanText === '') {
            $cleanText = 'AWB' . rand(10000000, 99999999);
        }

        // Check if input is purely numeric and even length -> Code Set C is much more compact and high-density
        $isPureDigits = ctype_digit($cleanText);
        $useCodeC = $isPureDigits && (strlen($cleanText) % 2 === 0) && strlen($cleanText) >= 4;

        $values = [];
        if ($useCodeC) {
            $values[] = 105; // Start C
            $pairs = str_split($cleanText, 2);
            foreach ($pairs as $pair) {
                $values[] = (int)$pair;
            }
        } else {
            $values[] = 104; // Start B
            $chars = str_split($cleanText);
            foreach ($chars as $ch) {
                $ascii = ord($ch);
                $code = $ascii - 32;
                if ($code < 0 || $code > 95) {
                    $code = 0; // fallback to space
                }
                $values[] = $code;
            }
        }

        // Compute mandatory Code 128 checksum
        $checkSum = $values[0];
        for ($i = 1; $i < count($values); $i++) {
            $checkSum += $i * $values[$i];
        }
        $values[] = $checkSum % 103;
        $values[] = 106; // Stop pattern

        // Build 1D binary module stream (1 = black bar, 0 = white space)
        $modules = [];
        foreach ($values as $val) {
            $pattern = self::$patterns[$val];
            $isBar = true;
            for ($k = 0; $k < strlen($pattern); $k++) {
                $w = (int)$pattern[$k];
                for ($m = 0; $m < $w; $m++) {
                    $modules[] = $isBar ? 1 : 0;
                }
                $isBar = !$isBar;
            }
        }

        $contentModulesCount = count($modules);
        $totalModulesCount = $contentModulesCount + ($quietZone * 2);
        $totalWidthPx = round($totalModulesCount * $moduleWidth, 1);

        $textHeight = $showText ? 15 : 0;
        $barHeight = max(18, $height - $textHeight);

        // Build continuous black rects for minimum SVG element count & crisp edges
        $rects = [];
        $inBar = false;
        $barStart = 0;

        for ($i = 0; $i < $contentModulesCount; $i++) {
            if ($modules[$i] === 1) {
                if (!$inBar) {
                    $inBar = true;
                    $barStart = $i;
                }
            } else {
                if ($inBar) {
                    $barWidth = round(($i - $barStart) * $moduleWidth, 1);
                    $x = round(($quietZone + $barStart) * $moduleWidth, 1);
                    $rects[] = "<rect x=\"{$x}\" y=\"0\" width=\"{$barWidth}\" height=\"{$barHeight}\" fill=\"#000000\"/>";
                    $inBar = false;
                }
            }
        }
        if ($inBar) {
            $barWidth = round(($contentModulesCount - $barStart) * $moduleWidth, 1);
            $x = round(($quietZone + $barStart) * $moduleWidth, 1);
            $rects[] = "<rect x=\"{$x}\" y=\"0\" width=\"{$barWidth}\" height=\"{$barHeight}\" fill=\"#000000\"/>";
        }

        $textElement = '';
        if ($showText) {
            $textY = $barHeight + 12;
            $escapedText = htmlspecialchars($cleanText);
            $textElement = "<text x=\"50%\" y=\"{$textY}\" font-family=\"'Courier New', Courier, monospace, monospace\" font-size=\"12\" font-weight=\"bold\" text-anchor=\"middle\" fill=\"#000000\" letter-spacing=\"1.5\">{$escapedText}</text>";
        }

        $rectsHtml = implode("\n", $rects);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$totalWidthPx} {$height}" width="{$totalWidthPx}" height="{$height}" style="display:block;margin:0 auto;background:#ffffff;max-width:100%;height:{$height}px;shape-rendering:crispEdges;">
<rect width="100%" height="100%" fill="#ffffff"/>
{$rectsHtml}
{$textElement}
</svg>
SVG;
    }
}
