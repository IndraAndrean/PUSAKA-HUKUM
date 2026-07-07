<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'action' => ['nullable', Rule::in(['created', 'updated', 'deleted', 'imported', 'downloaded', 'restored'])],
            'module' => ['nullable', 'string', 'max:80'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $logs = AuditLog::with('user')
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $keyword = $filters['q'];
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('description', 'like', "%{$keyword}%")
                        ->orWhere('subject_label', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($user) => $user
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%"));
                });
            })
            ->when(filled($filters['action'] ?? null), fn ($query) => $query->where('action', $filters['action']))
            ->when(filled($filters['module'] ?? null), fn ($query) => $query->where('module', $filters['module']))
            ->when(filled($filters['user_id'] ?? null), fn ($query) => $query->where('user_id', $filters['user_id']))
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'users' => User::whereIn('role', ['super_admin', 'admin'])->orderBy('name')->get(),
            'modules' => AuditLog::query()->distinct()->orderBy('module')->pluck('module'),
            'actions' => $this->actions(),
            'summary' => [
                'today' => AuditLog::whereDate('created_at', today())->count(),
                'last_seven_days' => AuditLog::where('created_at', '>=', now()->subDays(7))->count(),
                'deletions' => AuditLog::where('action', 'deleted')->where('created_at', '>=', now()->subDays(30))->count(),
                'imports' => AuditLog::where('action', 'imported')->where('created_at', '>=', now()->subDays(30))->count(),
            ],
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', [
            'log' => $auditLog,
            'actions' => $this->actions(),
        ]);
    }

    private function actions(): array
    {
        return [
            'created' => 'Tambah',
            'updated' => 'Ubah',
            'deleted' => 'Hapus',
            'imported' => 'Import',
            'downloaded' => 'Unduh',
            'restored' => 'Pulihkan',
        ];
    }
}
