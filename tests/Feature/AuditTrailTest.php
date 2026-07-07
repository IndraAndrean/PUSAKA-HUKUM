<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\AuditLog;
use App\Models\LegalCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_create_action_is_logged_with_actor_and_request_context(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->post(route('admin.faqs.store'), [
            'question' => 'Bagaimana audit aktivitas bekerja?',
            'answer' => 'Setiap perubahan admin dicatat oleh sistem.',
            'category' => 'Audit',
            'status' => 'published',
        ])->assertRedirect(route('admin.faqs.index'));

        $log = AuditLog::where('module', 'FAQ')->where('action', 'created')->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('Bagaimana audit aktivitas bekerja?', $log->subject_label);
        $this->assertSame('published', $log->new_values['status']);
        $this->assertNotNull($log->ip_address);
    }

    public function test_update_log_contains_only_changed_old_and_new_values(): void
    {
        $admin = $this->user('admin');
        $article = Article::create([
            'title' => 'Artikel Audit Awal',
            'slug' => 'artikel-audit-awal',
            'category' => 'Edukasi',
            'excerpt' => 'Ringkasan awal.',
            'content' => 'Isi artikel sebelum diperbarui.',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => 'Artikel Audit Diperbarui',
            'category' => 'Edukasi',
            'excerpt' => 'Ringkasan setelah diperbarui.',
            'content' => 'Isi artikel setelah diperbarui.',
            'status' => 'published',
        ])->assertRedirect(route('admin.articles.index'));

        $log = AuditLog::where('module', 'Artikel')->where('action', 'updated')->firstOrFail();

        $this->assertSame('Artikel Audit Awal', $log->old_values['title']);
        $this->assertSame('Artikel Audit Diperbarui', $log->new_values['title']);
        $this->assertArrayNotHasKey('created_by', $log->new_values);
        $this->assertArrayNotHasKey('updated_at', $log->new_values);
    }

    public function test_deleted_object_label_and_values_remain_available(): void
    {
        $admin = $this->user('admin');
        $category = LegalCategory::create([
            'name' => 'Kategori Audit Dihapus',
            'slug' => 'kategori-audit-dihapus',
            'description' => 'Kategori sementara untuk pengujian audit.',
        ]);
        $categoryId = $category->id;

        $this->actingAs($admin)
            ->delete(route('admin.legal-categories.destroy', $category))
            ->assertSessionHas('success');

        $log = AuditLog::where('module', 'Kategori Hukum')->where('action', 'deleted')->firstOrFail();

        $this->assertSame($categoryId, $log->subject_id);
        $this->assertSame('Kategori Audit Dihapus', $log->subject_label);
        $this->assertSame('kategori-audit-dihapus', $log->old_values['slug']);
        $this->assertNull($log->new_values);
    }

    public function test_password_is_never_stored_in_audit_values(): void
    {
        $superAdmin = $this->user('super_admin');

        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Operator Audit',
            'email' => 'operator-audit@pusakahukum.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'internal',
            'satuan_kerja' => 'Polres Audit',
            'jabatan' => 'Operator',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $log = AuditLog::where('module', 'Pengguna')->where('action', 'created')->firstOrFail();
        $serialized = json_encode([$log->old_values, $log->new_values]);

        $this->assertSame('[DISEMBUNYIKAN]', $log->new_values['password']);
        $this->assertStringNotContainsString('password123', $serialized);
        $this->assertStringNotContainsString('$2y$', $serialized);
    }

    public function test_admin_can_filter_and_view_audit_but_internal_user_is_denied(): void
    {
        $admin = $this->user('admin');
        $internal = $this->user('internal');
        $log = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'deleted',
            'module' => 'Artikel',
            'subject_type' => Article::class,
            'subject_id' => 999,
            'subject_label' => 'Artikel Audit Filter',
            'description' => 'Menghapus artikel untuk pengujian filter.',
            'old_values' => ['title' => 'Artikel Audit Filter'],
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', ['action' => 'deleted', 'module' => 'Artikel']))
            ->assertOk()
            ->assertSee('Artikel Audit Filter');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.show', $log))
            ->assertOk()
            ->assertSee('Perubahan Data')
            ->assertSee('Artikel Audit Filter');

        $this->actingAs($internal)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst(str_replace('_', ' ', $role)).' Audit',
            'email' => $role.'-audit-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
