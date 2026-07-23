<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class SystemBackupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_super_admin_can_open_backup_management(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.backups.index'))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Backup dan Pemulihan Sistem');
    }

    public function test_super_admin_can_create_backup_and_action_is_audited(): void
    {
        $superAdmin = $this->user('super_admin');
        $backup = $this->backup($superAdmin);
        $this->mock(BackupService::class, function (MockInterface $mock) use ($backup, $superAdmin) {
            $mock->shouldReceive('create')
                ->once()
                ->withArgs(fn ($user, $type) => $user->is($superAdmin) && $type === 'manual')
                ->andReturn($backup);
        });

        $this->actingAs($superAdmin)
            ->post(route('admin.backups.store'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'created',
            'module' => 'Backup',
            'subject_id' => $backup->id,
        ]);
    }

    public function test_restore_requires_current_password_and_confirmation_phrase(): void
    {
        $superAdmin = $this->user('super_admin');
        $backup = $this->backup($superAdmin);
        $this->mock(BackupService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('restore');
        });

        $this->actingAs($superAdmin)
            ->post(route('admin.backups.restore', $backup), [
                'current_password' => 'password-salah',
                'confirmation' => 'SALAH',
            ])
            ->assertSessionHasErrors(['current_password', 'confirmation']);
    }

    public function test_successful_restore_logs_out_super_admin_and_is_audited(): void
    {
        $superAdmin = $this->user('super_admin');
        $backup = $this->backup($superAdmin);
        $backup->update([
            'status' => 'restored',
            'restored_by' => $superAdmin->id,
            'restored_at' => now(),
        ]);
        $this->mock(BackupService::class, function (MockInterface $mock) use ($backup, $superAdmin) {
            $mock->shouldReceive('restore')
                ->once()
                ->withArgs(fn ($selected, $user) => $selected->is($backup) && $user->is($superAdmin))
                ->andReturn($backup);
        });

        $this->actingAs($superAdmin)
            ->post(route('admin.backups.restore', $backup), [
                'current_password' => 'password',
                'confirmation' => 'PULIHKAN',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertTrue(AuditLog::where('action', 'restored')
            ->where('module', 'Backup')
            ->where('subject_id', $backup->id)
            ->exists());
    }

    public function test_checksum_validation_rejects_modified_archive(): void
    {
        Storage::fake('local');
        $superAdmin = $this->user('super_admin');
        $backup = $this->backup($superAdmin);
        $path = Storage::disk('local')->path($backup->disk_path);
        $this->writeValidArchive($path);
        $backup->update([
            'size_bytes' => filesize($path),
            'checksum_sha256' => hash_file('sha256', $path),
        ]);
        file_put_contents($path, 'diubah', FILE_APPEND);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Checksum backup tidak cocok');

        app(BackupService::class)->validateBackup($backup->fresh());
    }

    public function test_delete_requires_password_and_records_audit(): void
    {
        $superAdmin = $this->user('super_admin');
        $backup = $this->backup($superAdmin);
        $this->mock(BackupService::class, function (MockInterface $mock) use ($backup) {
            $mock->shouldReceive('delete')
                ->once()
                ->withArgs(fn ($selected) => $selected->is($backup))
                ->andReturnUsing(fn (Backup $selected) => $selected->delete());
        });

        $this->actingAs($superAdmin)
            ->delete(route('admin.backups.destroy', $backup), [
                'current_password' => 'password',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('backups', ['id' => $backup->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'module' => 'Backup',
            'subject_id' => $backup->id,
        ]);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst(str_replace('_', ' ', $role)).' Backup',
            'email' => $role.'-backup-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function backup(User $creator): Backup
    {
        $filename = 'PUSAKA_HUKUM_TEST_'.uniqid().'.zip';

        return Backup::create([
            'created_by' => $creator->id,
            'filename' => $filename,
            'disk_path' => 'backups/'.$filename,
            'type' => 'manual',
            'status' => 'completed',
            'size_bytes' => 1024,
            'database_size_bytes' => 512,
            'documents_count' => 1,
            'checksum_sha256' => str_repeat('a', 64),
        ]);
    }

    private function writeValidArchive(string $path): void
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('database.sql', 'SELECT 1;');
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'SIPAKEM',
            'format_version' => 1,
            'database_dump' => 'database.sql',
            'database' => config('database.connections.mysql.database'),
        ]));
        $zip->close();
    }
}
