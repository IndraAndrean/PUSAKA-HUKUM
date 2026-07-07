<?php

namespace App\Services;

use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Consultation;
use App\Models\Document;
use App\Models\DocumentImportBatch;
use App\Models\DocumentType;
use App\Models\Faq;
use App\Models\KpiTarget;
use App\Models\LegalCategory;
use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    private const SENSITIVE_FIELDS = [
        'password',
        'remember_token',
        'password_reset_token',
        'api_token',
    ];

    public function record(
        string $action,
        Model $subject,
        array $oldValues = [],
        array $newValues = [],
        ?User $actor = null,
        ?string $description = null,
    ): AuditLog {
        $request = app()->bound('request') ? app(Request::class) : null;
        $actor ??= auth()->user();
        $module = $this->moduleFor($subject);
        $label = $this->labelFor($subject);

        return AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'subject_label' => $label,
            'description' => $description ?? $this->description($action, $module, $label),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    public function moduleFor(Model $subject): string
    {
        return match (true) {
            $subject instanceof Document => 'Dokumen',
            $subject instanceof DocumentType => 'Jenis Dokumen',
            $subject instanceof LegalCategory => 'Kategori Hukum',
            $subject instanceof Article => 'Artikel',
            $subject instanceof Faq => 'FAQ',
            $subject instanceof Consultation => 'Konsultasi',
            $subject instanceof User => 'Pengguna',
            $subject instanceof DocumentImportBatch => 'Import Dokumen',
            $subject instanceof Backup => 'Backup',
            $subject instanceof OrganizationProfile => 'Profil Instansi',
            $subject instanceof KpiTarget => 'Indikator Keberhasilan',
            default => class_basename($subject),
        };
    }

    public function labelFor(Model $subject): string
    {
        return match (true) {
            $subject instanceof Document => trim($subject->document_code.' - '.$subject->title, ' -'),
            $subject instanceof DocumentType => (string) $subject->name,
            $subject instanceof LegalCategory => (string) $subject->name,
            $subject instanceof Article => (string) $subject->title,
            $subject instanceof Faq => (string) $subject->question,
            $subject instanceof Consultation => 'Konsultasi '.$subject->name,
            $subject instanceof User => trim($subject->name.' - '.$subject->email, ' -'),
            $subject instanceof DocumentImportBatch => (string) $subject->spreadsheet_name,
            $subject instanceof Backup => (string) $subject->filename,
            $subject instanceof OrganizationProfile => (string) $subject->organization_name,
            $subject instanceof KpiTarget => 'Target KPI PUSAKA HUKUM',
            default => (string) ($subject->getKey() ?? class_basename($subject)),
        };
    }

    private function description(string $action, string $module, string $label): string
    {
        $verb = match ($action) {
            'created' => 'menambahkan',
            'updated' => 'memperbarui',
            'deleted' => 'menghapus',
            'imported' => 'mengimpor',
            'downloaded' => 'mengunduh',
            'restored' => 'memulihkan',
            default => $action,
        };

        return ucfirst("{$verb} {$module}: {$label}");
    }

    private function sanitize(array $values): ?array
    {
        unset($values['created_at'], $values['updated_at'], $values['email_verified_at']);

        foreach ($values as $key => $value) {
            if (
                in_array($key, self::SENSITIVE_FIELDS, true)
                || str_contains(strtolower((string) $key), 'token')
                || str_contains(strtolower((string) $key), 'password')
            ) {
                $values[$key] = '[DISEMBUNYIKAN]';
            }
        }

        return $values === [] ? null : $values;
    }
}
