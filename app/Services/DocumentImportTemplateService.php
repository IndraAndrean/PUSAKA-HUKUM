<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class DocumentImportTemplateService
{
    public function createXlsx(): string
    {
        $directory = storage_path('app/temp');

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder sementara template tidak dapat dibuat.');
        }

        $path = $directory.'/template_import_dokumen_'.uniqid().'.xlsx';
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Template XLSX tidak dapat dibuat.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelations());
        $zip->addFromString('docProps/app.xml', $this->appProperties());
        $zip->addFromString('docProps/core.xml', $this->coreProperties());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelations());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->dataSheet());
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->guideSheet());
        $zip->close();

        return $path;
    }

    private function dataSheet(): string
    {
        $headers = [
            'judul',
            'jenis_dokumen',
            'nomor_dokumen',
            'tahun',
            'tanggal_penetapan',
            'tanggal_berlaku',
            'instansi_penerbit',
            'status_dokumen',
            'kategori_hukum',
            'bidang_subbidang',
            'kata_kunci',
            'ringkasan',
            'abstrak',
            'dasar_hukum',
            'peraturan_terkait',
            'versi_dokumen',
            'tanggal_review_terakhir',
            'level_akses',
            'nama_file_pdf',
            'penulis',
            'penerbit',
            'isbn_issn',
            'edisi_volume',
        ];
        $widths = [28, 22, 18, 10, 19, 19, 25, 18, 22, 20, 32, 42, 32, 32, 32, 16, 23, 16, 28, 28, 28, 20, 18];
        $columns = '';

        foreach ($widths as $index => $width) {
            $column = $index + 1;
            $columns .= "<col min=\"{$column}\" max=\"{$column}\" width=\"{$width}\" customWidth=\"1\"/>";
        }

        $headerCells = '';

        foreach ($headers as $index => $header) {
            $cell = $this->columnName($index + 1).'1';
            $headerCells .= $this->inlineCell($cell, $header, 1);
        }

        return $this->xmlHeader().<<<XML
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>
  <sheetFormatPr defaultRowHeight="18"/>
  <cols>{$columns}</cols>
  <sheetData><row r="1" ht="32" customHeight="1">{$headerCells}</row></sheetData>
  <autoFilter ref="A1:W501"/>
  <dataValidations count="3">
    <dataValidation type="list" allowBlank="0" sqref="H2:H501"><formula1>"berlaku,dicabut,diubah,tidak_berlaku"</formula1></dataValidation>
    <dataValidation type="list" allowBlank="0" sqref="J2:J501"><formula1>"kum,bankum,sunluhkum"</formula1></dataValidation>
    <dataValidation type="list" allowBlank="0" sqref="R2:R501"><formula1>"publik,internal,terbatas"</formula1></dataValidation>
  </dataValidations>
</worksheet>
XML;
    }

    private function guideSheet(): string
    {
        $rows = [
            1 => [['A', 'PANDUAN IMPORT MASSAL DOKUMEN PUSAKA HUKUM', 2]],
            3 => [['A', 'Langkah', 1], ['B', 'Keterangan', 1]],
            4 => [['A', '1', 0], ['B', 'Isi data mulai baris 2 pada sheet Data Dokumen.', 0]],
            5 => [['A', '2', 0], ['B', 'Jangan mengubah nama atau urutan kolom.', 0]],
            6 => [['A', '3', 0], ['B', 'Tanggal memakai format YYYY-MM-DD, contoh 2026-06-11.', 0]],
            7 => [['A', '4', 0], ['B', 'Kata kunci minimal 3 dan dipisahkan koma.', 0]],
            8 => [['A', '5', 0], ['B', 'Nama PDF harus sama persis dengan kolom nama_file_pdf.', 0]],
            9 => [['A', '6', 0], ['B', 'Satukan seluruh PDF ke satu file ZIP saat diunggah.', 0]],
            10 => [['A', '7', 0], ['B', 'Maksimal 500 dokumen per proses; setiap PDF maksimal 20 MB.', 0]],
            11 => [['A', '8', 0], ['B', 'Untuk koleksi perpustakaan, isi penulis dan penerbit. Nomor serta tanggal regulasi boleh kosong.', 0]],
            12 => [['A', 'Kelompok', 1], ['B', 'Nilai', 1], ['C', 'Keterangan', 1]],
            13 => [['A', 'Status', 0], ['B', 'berlaku', 0], ['C', 'Dokumen masih berlaku', 0]],
            14 => [['A', 'Status', 0], ['B', 'dicabut', 0], ['C', 'Dokumen telah dicabut', 0]],
            15 => [['A', 'Status', 0], ['B', 'diubah', 0], ['C', 'Dokumen telah diubah', 0]],
            16 => [['A', 'Status', 0], ['B', 'tidak_berlaku', 0], ['C', 'Dokumen tidak berlaku', 0]],
            17 => [['A', 'Bidang', 0], ['B', 'kum', 0], ['C', 'Subbidang Kum', 0]],
            18 => [['A', 'Bidang', 0], ['B', 'bankum', 0], ['C', 'Subbidang Bantuan Hukum', 0]],
            19 => [['A', 'Bidang', 0], ['B', 'sunluhkum', 0], ['C', 'Subbidang Penyuluhan Hukum', 0]],
            20 => [['A', 'Akses', 0], ['B', 'publik', 0], ['C', 'Dapat dibuka tanpa login', 0]],
            21 => [['A', 'Akses', 0], ['B', 'internal', 0], ['C', 'Untuk pengguna internal dan admin', 0]],
            22 => [['A', 'Akses', 0], ['B', 'terbatas', 0], ['C', 'Hanya admin', 0]],
            23 => [['A', 'Jenis dokumen', 0], ['B', 'Nama atau kode prefix', 0], ['C', 'Contoh: Peraturan Kapolri atau POL-PERKAP', 0]],
        ];
        $sheetRows = '';

        foreach ($rows as $rowNumber => $cells) {
            $contents = '';

            foreach ($cells as [$column, $value, $style]) {
                $contents .= $this->inlineCell($column.$rowNumber, $value, $style);
            }

            $height = $rowNumber === 1 ? ' ht="30" customHeight="1"' : '';
            $sheetRows .= "<row r=\"{$rowNumber}\"{$height}>{$contents}</row>";
        }

        return $this->xmlHeader().<<<XML
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>
  <sheetFormatPr defaultRowHeight="20"/>
  <cols>
    <col min="1" max="1" width="18" customWidth="1"/>
    <col min="2" max="2" width="42" customWidth="1"/>
    <col min="3" max="3" width="48" customWidth="1"/>
  </cols>
  <sheetData>{$sheetRows}</sheetData>
  <mergeCells count="1"><mergeCell ref="A1:C1"/></mergeCells>
</worksheet>
XML;
    }

    private function styles(): string
    {
        return $this->xmlHeader().<<<'XML'
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>
    <font><b/><color rgb="FFFFFFFF"/><sz val="14"/><name val="Calibri"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF123047"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border><left style="thin"><color rgb="FFD7E0E5"/></left><right style="thin"><color rgb="FFD7E0E5"/></right><top style="thin"><color rgb="FFD7E0E5"/></top><bottom style="thin"><color rgb="FFD7E0E5"/></bottom><diagonal/></border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }

    private function workbook(): string
    {
        return $this->xmlHeader().<<<'XML'
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Data Dokumen" sheetId="1" r:id="rId1"/>
    <sheet name="Panduan" sheetId="2" r:id="rId2"/>
  </sheets>
</workbook>
XML;
    }

    private function workbookRelations(): string
    {
        return $this->xmlHeader().<<<'XML'
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function rootRelations(): string
    {
        return $this->xmlHeader().<<<'XML'
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    private function contentTypes(): string
    {
        return $this->xmlHeader().<<<'XML'
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML;
    }

    private function appProperties(): string
    {
        return $this->xmlHeader().<<<'XML'
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>PUSAKA HUKUM</Application>
</Properties>
XML;
    }

    private function coreProperties(): string
    {
        $date = now()->utc()->format('Y-m-d\TH:i:s\Z');

        return $this->xmlHeader().<<<XML
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Template Import Dokumen PUSAKA HUKUM</dc:title>
  <dc:creator>Bidkum Polda Lampung</dc:creator>
  <dcterms:created xsi:type="dcterms:W3CDTF">{$date}</dcterms:created>
</cp:coreProperties>
XML;
    }

    private function inlineCell(string $reference, string $value, int $style): string
    {
        $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return "<c r=\"{$reference}\" s=\"{$style}\" t=\"inlineStr\"><is><t xml:space=\"preserve\">{$escaped}</t></is></c>";
    }

    private function columnName(int $number): string
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function xmlHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    }
}
