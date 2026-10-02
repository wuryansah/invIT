<?php

namespace App\Core;

use ZipArchive;

/**
 * Minimal spreadsheet writer producing valid .xlsx files (single sheet)
 * without external dependencies. Uses PHP's built-in ZipArchive.
 */
class Excel
{
    public const MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /**
     * @param string   $filename  e.g. "Inventory.xlsx"
     * @param string[] $headers
     * @param array[]  $rows
     */
    public static function download(string $filename, array $headers, array $rows, string $sheetTitle = 'Sheet1'): never
    {
        $xml = self::build($headers, $rows, $sheetTitle);
        app_response()->download($xml, $filename, self::MIME);
    }

    public static function build(array $headers, array $rows, string $sheetTitle = 'Sheet1'): string
    {
        $headers = array_map('strval', $headers);
        $widths = self::columnWidths($headers, $rows);

        $colMeta = '';
        foreach ($widths as $i => $w) {
            $colMeta .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
        }

        $sheetRows = '<row r="1">';
        foreach ($headers as $i => $h) {
            $col = $i + 1;
            $sheetRows .= '<c r="' . colName($col) . '1" t="inlineStr" s="1">'
                . '<is><t>' . self::escape($h) . '</t></is></c>';
        }
        $sheetRows .= '</row>';

        $r = 2;
        foreach ($rows as $row) {
            $sheetRows .= '<row r="' . $r . '">';
            $c = 1;
            foreach ($row as $value) {
                if (is_numeric($value) && !is_bool($value)) {
                    $cell = '<c r="' . colName($c) . $r . '" t="n" s="0"><v>' . (is_float($value) ? rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') : $value) . '</v></c>';
                } else {
                    $cell = '<c r="' . colName($c) . $r . '" t="inlineStr" s="0"><is><t>' . self::escape((string)$value) . '</t></is></c>';
                }
                $sheetRows .= $cell;
                $c++;
            }
            $sheetRows .= '</row>';
            $r++;
        }

        $sheetTitleXml = self::escape(substr($sheetTitle, 0, 31));

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>' . $colMeta . '</cols>'
            . '<sheetData>' . $sheetRows . '</sheetData>'
            . '<autoFilter ref="A1:' . colName(count($headers)) . max(1, count($rows)) . '"/>'
            . '</worksheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $sheetTitleXml . '" sheetId="1" r:id="rId1"/></sheets></workbook>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="0"/>'
            . '<fonts count="2">'
            . '<font><sz val="10"/><name val="Calibri"/></font>'
            . '<font><b/><color rgb="FFFFFFFF"/><sz val="10"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="2">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1F4E79"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border>'
            . '<left style="thin"><color rgb="FFD9D9D9"/></left>'
            . '<right style="thin"><color rgb="FFD9D9D9"/></right>'
            . '<top style="thin"><color rgb="FFD9D9D9"/></top>'
            . '<bottom style="thin"><color rgb="FFD9D9D9"/></bottom>'
            . '<diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="1" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        $zip = new ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            app_response()->abort(500, 'Unable to create Excel file.');
        }
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rels);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $content = file_get_contents($tmp);
        @unlink($tmp);
        return $content;
    }

    private static function columnWidths(array $headers, array $rows): array
    {
        $widths = [];
        foreach ($headers as $i => $h) {
            $widths[$i] = max(10, min(40, mb_strlen($h) + 4));
        }
        foreach ($rows as $row) {
            $i = 0;
            foreach ($row as $value) {
                if (isset($widths[$i])) {
                    $len = mb_strlen((string)$value) + 2;
                    if ($len > $widths[$i]) {
                        $widths[$i] = min(42, $len);
                    }
                }
                $i++;
            }
        }
        return $widths;
    }

    private static function escape(string $text): string
    {
        $text = str_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], $text);
        return preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $text) ?? $text;
    }
}

if (!function_exists('colName')) {
    function colName(int $col): string
    {
        $name = '';
        while ($col > 0) {
            $mod = ($col - 1) % 26;
            $name = chr(65 + $mod) . $name;
            $col = intdiv($col - 1, 26);
        }
        return $name;
    }
}