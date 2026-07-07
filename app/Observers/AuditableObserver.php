<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        if (! $this->shouldAudit()) {
            return;
        }

        $this->logger()->record('created', $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldAudit()) {
            return;
        }

        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $key) {
            $oldValues[$key] = $model->getOriginal($key);
        }

        $this->logger()->record('updated', $model, $oldValues, $changes);
    }

    public function deleted(Model $model): void
    {
        if (! $this->shouldAudit()) {
            return;
        }

        $this->logger()->record('deleted', $model, $model->getOriginal(), []);
    }

    private function logger(): AuditLogger
    {
        return app(AuditLogger::class);
    }

    private function shouldAudit(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}
