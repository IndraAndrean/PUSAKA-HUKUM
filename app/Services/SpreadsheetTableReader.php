<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SpreadsheetTableReader
{
    public function read(UploadedFile $file): array
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'csv' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw new RuntimeException('Format spreadsheet harus CSV atau XLSX.'),
        };
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            throw new RuntimeException('File CSV tidak dapat dibaca.');
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count((string) $firstLine, ';') >= substr_count((string) $firstLine, ',') ? ';' : ',';
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_map(fn ($value) => $this->cleanValue($value), $row);
        }

        fclose($handle);

        return $this->mapRows($rows);
    }

    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('File XLSX tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->sharedStrings($zip);
            $sheetPath = $this->firstWorksheetPath($zip);
            $sheetXml = $zip->getFromName($sheetPath);

            if ($sheetXml === false) {
                throw new RuntimeException('Sheet pertama pada XLSX tidak ditemukan.');
            }

            $sheet = new SimpleXMLElement($sheetXml);
            $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $rows = [];

            foreach ($sheet->xpath('//x:sheetData/x:row') ?: [] as $rowNode) {
                $row = [];
                $rowNode->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

                foreach ($rowNode->xpath('x:c') ?: [] as $cell) {
                    $cell->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    $reference = (string) $cell['r'];
                    $column = $this->columnIndex($reference);
                    $type = (string) $cell['t'];
                    $value = '';

                    if ($type === 'inlineStr') {
                        $textNodes = $cell->xpath('x:is//x:t') ?: [];
                        $value = implode('', array_map(fn ($node) => (string) $node, $textNodes));
                    } else {
                        $raw = (string) ($cell->xpath('x:v')[0] ?? '');
                        $value = $type === 's' ? ($sharedStrings[(int) $raw] ?? '') : $raw;
                    }

                    $row[$column] = $this->cleanValue($value);
                }

                if ($row !== []) {
                    $maxColumn = max(array_keys($row));
                    $rows[] = array_map(
                        fn ($index) => $row[$index] ?? '',
                        range(0, $maxColumn)
                    );
                }
            }

            return $this->mapRows($rows);
        } finally {
            $zip->close();
        }
    }

    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $document = new SimpleXMLElement($xml);
        $document->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $strings = [];

        foreach ($document->xpath('//x:si') ?: [] as $item) {
            $item->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $textNodes = $item->xpath('.//x:t') ?: [];
            $strings[] = implode('', array_map(fn ($node) => (string) $node, $textNodes));
        }

        return $strings;
    }

    private function firstWorksheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relationsXml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = new SimpleXMLElement($workbookXml);
        $workbook->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $sheet = ($workbook->xpath('//x:sheets/x:sheet') ?: [])[0] ?? null;
        $relationId = $sheet?->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'] ?? null;

        if (! $relationId) {
            return 'xl/worksheets/sheet1.xml';
        }

        $relations = (new SimpleXMLElement($relationsXml))
            ->children('http://schemas.openxmlformats.org/package/2006/relationships');

        foreach ($relations->Relationship as $relation) {
            if ((string) $relation['Id'] === (string) $relationId) {
                return 'xl/'.ltrim((string) $relation['Target'], '/');
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function mapRows(array $rows): array
    {
        if (count($rows) < 2) {
            throw new RuntimeException('Spreadsheet belum berisi data dokumen.');
        }

        $headers = array_map(fn ($header) => $this->canonicalHeader((string) $header), array_shift($rows));

        if (in_array('', $headers, true)) {
            throw new RuntimeException('Header spreadsheet tidak boleh kosong.');
        }

        $mapped = [];

        foreach ($rows as $index => $row) {
            $values = array_pad($row, count($headers), '');
            $record = array_combine($headers, array_slice($values, 0, count($headers)));

            if (collect($record)->filter(fn ($value) => $value !== '')->isEmpty()) {
                continue;
            }

            $record['_row'] = $index + 2;
            $mapped[] = $record;
        }

        if ($mapped === []) {
            throw new RuntimeException('Spreadsheet belum berisi data dokumen.');
        }

        return $mapped;
    }

    private function canonicalHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', trim($header));
        $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $header));
        $normalized = trim($normalized, '_');

        return [
            'judul' => 'title',
            'penulis' => 'author',
            'jenis_dokumen' => 'document_type',
            'nomor_dokumen' => 'document_number',
            'tahun' => 'year',
            'tanggal_penetapan' => 'enacted_date',
            'tanggal_berlaku' => 'effective_date',
            'instansi_penerbit' => 'issuing_institution',
            'penerbit' => 'publisher',
            'isbn_issn' => 'isbn_issn',
            'edisi_volume' => 'edition_volume',
            'status_dokumen' => 'document_status',
            'kategori_hukum' => 'legal_category',
            'bidang_subbidang' => 'bidang_subbidang',
            'kata_kunci' => 'keywords',
            'ringkasan' => 'summary',
            'abstrak' => 'abstract',
            'dasar_hukum' => 'legal_basis',
            'peraturan_terkait' => 'related_regulation',
            'versi_dokumen' => 'document_version',
            'tanggal_review_terakhir' => 'last_reviewed_at',
            'level_akses' => 'access_level',
            'nama_file_pdf' => 'pdf_filename',
        ][$normalized] ?? $normalized;
    }

    private function columnIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/i', $reference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function cleanValue(mixed $value): string
    {
        return trim(str_replace("\xC2\xA0", ' ', (string) $value));
    }
}
