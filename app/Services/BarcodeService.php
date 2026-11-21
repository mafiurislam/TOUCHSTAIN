<?php

namespace App\Services;

class BarcodeService
{
    /**
     * Code 128 pattern dictionary (ISO/IEC 15417).
     * Alternating bar and space widths (total 11 modules each, except stop which is 13).
     */
    protected static array $patterns = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', // 0-9
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', // 10-19
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', // 20-29
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', // 30-39
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', // 40-49
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', // 50-59
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', // 60-69
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', // 70-79
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', // 80-89
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', // 90-99
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112', // 100-106
    ];

    protected const START_A = 103;
    protected const START_B = 104;
    protected const START_C = 105;
    protected const STOP = 106;

    /**
     * Generate standard Code 128 (Subset B) SVG barcode.
     * Compliant with ISO/IEC 15417 standards readable by any laser or camera scanner.
     *
     * @param string $text Barcode content (e.g. AWB number or Order ID)
     * @param int $height Bar height in px (standard: 50-60px for shipping labels)
     * @param float $moduleWidth Single narrow bar width in px (standard: 2.0 - 2.2px)
     * @param bool $showText Whether to render human-readable text below
     * @return string Fully qualified SVG markup
     */
    public static function getBarcodeSvg(string $text, int $height = 55, float $moduleWidth = 2.0, bool $showText = true): string
    {
        $raw = trim($text);
        if ($raw === '') {
            $raw = 'AWB' . time();
        }

        // Clean string to printable ASCII range 32-126
        $clean = '';
        for ($i = 0; $i < strlen($raw); $i++) {
            $ord = ord($raw[$i]);
            if ($ord >= 32 && $ord <= 126) {
                $clean .= $raw[$i];
            }
        }
        if ($clean === '') {
            $clean = 'UNKNOWN';
        }

        // Encode using Code 128 Subset B (supports letters, numbers, hyphens, and symbols)
        $codes = [];
        $codes[] = self::START_B;
        $checksumSum = self::START_B;

        for ($i = 0; $i < strlen($clean); $i++) {
            $val = ord($clean[$i]) - 32;
            $codes[] = $val;
            $checksumSum += ($val * ($i + 1));
        }

        $checksum = $checksumSum % 103;
        $codes[] = $checksum;
        $codes[] = self::STOP;

        // Quiet Zone: Standard requires >= 10 modules width on left and right margins
        $quietZoneModules = 14;

        // Calculate total module count
        $totalModules = $quietZoneModules * 2;
        foreach ($codes as $code) {
            $pattern = self::$patterns[$code] ?? self::$patterns[0];
            $totalModules += array_sum(str_split($pattern));
        }

        $totalWidth = round($totalModules * $moduleWidth, 2);
        $textHeight = $showText ? 18 : 0;
        $totalHeight = $height + $textHeight + 6;

        // Build SVG rectangles for black bars
        $x = $quietZoneModules * $moduleWidth;
        $bars = '';

        foreach ($codes as $code) {
            $pattern = self::$patterns[$code] ?? self::$patterns[0];
            $isBar = true;
            for ($p = 0; $p < strlen($pattern); $p++) {
                $w = (int)$pattern[$p] * $moduleWidth;
                if ($isBar) {
                    $bars .= sprintf('<rect x="%.2f" y="3" width="%.2f" height="%d" fill="#000000" shape-rendering="crispEdges"/>', $x, $w, $height);
                }
                $x += $w;
                $isBar = !$isBar;
            }
        }

        $textSvg = '';
        if ($showText) {
            $textY = $height + 17;
            $escaped = htmlspecialchars($clean, ENT_QUOTES | ENT_XML1);
            $textSvg = sprintf(
                '<text x="50%%" y="%d" text-anchor="middle" font-family="-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, monospace" font-size="13" font-weight="800" fill="#000000" letter-spacing="2">%s</text>',
                $textY,
                $escaped
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %.2f %d" width="%.2f" height="%d" style="background:#ffffff;display:block;margin:0 auto;max-width:100%%;height:auto;"><rect width="100%%" height="100%%" fill="#ffffff"/>%s%s</svg>',
            $totalWidth,
            $totalHeight,
            $totalWidth,
            $totalHeight,
            $bars,
            $textSvg
        );
    }

    /**
     * Get QR Code image URL for mobile camera scans
     */
    public static function getQrCodeUrl(string $data, int $size = 140): string
    {
        $encoded = urlencode(trim($data));
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&margin=2&data={$encoded}";
    }

    public static function getCode128Svg(string $text, int $height = 45, float|int $moduleWidth = 1.8, bool $showText = true, int $quietZone = 12): string
    {
        return \App\Services\Delivery\BarcodeService::getCode128Svg($text, $height, $moduleWidth, $showText, $quietZone);
    }
}
