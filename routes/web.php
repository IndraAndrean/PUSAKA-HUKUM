<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\BackupController as AdminBackupController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\DocumentDivisionController as AdminDocumentDivisionController;
use App\Http\Controllers\Admin\DocumentTypeController as AdminDocumentTypeController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\KpiController as AdminKpiController;
use App\Http\Controllers\Admin\LegalCategoryController as AdminLegalCategoryController;
use App\Http\Controllers\Admin\OrganizationProfileController as AdminOrganizationProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\PublicDigitalLibraryController;
use App\Http\Controllers\PublicDocumentController;
use App\Http\Controllers\PublicEducationMaterialController;
use App\Http\Controllers\PublicFaqController;
use App\Http\Controllers\SatisfactionSurveyController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserActivityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/profil-instansi', OrganizationProfileController::class)->name('organization-profile.show');
Route::middleware('two_factor.required')->group(function () {
    Route::get('/dokumen', [PublicDocumentController::class, 'index'])->name('documents.index');
    Route::get('/perpustakaan', PublicDigitalLibraryController::class)->name('library.index');
    Route::get('/materi-penyuluhan', PublicEducationMaterialController::class)->name('education-materials.index');
    Route::get('/dokumen/{document}', [PublicDocumentController::class, 'show'])->name('documents.show');
    Route::get('/dokumen/{document}/preview', [PublicDocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/dokumen/{document}/download', [PublicDocumentController::class, 'download'])->name('documents.download');
});
Route::get('/artikel', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article:slug}', [PublicArticleController::class, 'show'])->name('articles.show');
Route::get('/faq', [PublicFaqController::class, 'index'])->name('faqs.index');
Route::get('/konsultasi', [ConsultationController::class, 'create'])->name('consultation.create');
Route::post('/konsultasi', [ConsultationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('consultation.store');
Route::get('/konsultasi/status', [ConsultationController::class, 'status'])
    ->middleware('throttle:20,1')
    ->name('consultation.status');
Route::get('/survei-kepuasan', [SatisfactionSurveyController::class, 'create'])->name('surveys.create');
Route::post('/survei-kepuasan', [SatisfactionSurveyController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('surveys.store');

Route::permanentRedirect('/profil-bidkum', '/profil-instansi')->name('legacy.organization-profile');
Route::permanentRedirect('/perpustakaan-digital', '/perpustakaan')->name('legacy.library');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');
    Route::get('/verifikasi-dua-langkah', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/verifikasi-dua-langkah', [TwoFactorController::class, 'verify'])
        ->middleware('throttle:5,1')
        ->name('two-factor.verify');
    Route::delete('/verifikasi-dua-langkah', [TwoFactorController::class, 'cancel'])->name('two-factor.cancel');
    Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-kata-sandi/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-kata-sandi', [PasswordResetController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/verifikasi-dua-langkah/aktivasi', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::get('/verifikasi-dua-langkah/aktivasi/kode', [TwoFactorController::class, 'setupConfirm'])->name('two-factor.setup.confirm');
    Route::post('/verifikasi-dua-langkah/aktivasi', [TwoFactorController::class, 'setupStore'])
        ->middleware('throttle:5,1')
        ->name('two-factor.setup.store');
    Route::get('/profil/verifikasi-dua-langkah', [TwoFactorController::class, 'edit'])->name('profile.two-factor');
    Route::post('/profil/verifikasi-dua-langkah', [TwoFactorController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('profile.two-factor.store');
    Route::delete('/profil/verifikasi-dua-langkah', [TwoFactorController::class, 'destroy'])->name('profile.two-factor.destroy');

    Route::middleware('two_factor.required')->group(function () {
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::get('/aktivitas-saya', UserActivityController::class)->name('account.activity');
        Route::get('/konsultasi-saya', [ConsultationController::class, 'mine'])->name('consultation.mine');
    });
});

Route::middleware(['auth', 'two_factor.required', 'role:super_admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/audit', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-logs.show');
    Route::get('/profil-instansi', [AdminOrganizationProfileController::class, 'edit'])->name('organization-profile.edit');
    Route::put('/profil-instansi', [AdminOrganizationProfileController::class, 'update'])->name('organization-profile.update');
    Route::get('/indikator', [AdminKpiController::class, 'index'])->name('kpi.index');
    Route::put('/indikator', [AdminKpiController::class, 'update'])->name('kpi.update');
    Route::get('/indikator/ekspor', [AdminKpiController::class, 'export'])->name('kpi.export');
    Route::resource('dokumen', AdminDocumentController::class)
        ->parameters(['dokumen' => 'document'])
        ->names('documents')
        ->except(['show']);
    Route::resource('jenis-dokumen', AdminDocumentTypeController::class)
        ->parameters(['jenis-dokumen' => 'documentType'])
        ->names('document-types')
        ->except(['show']);
    Route::resource('kategori-hukum', AdminLegalCategoryController::class)
        ->parameters(['kategori-hukum' => 'legalCategory'])
        ->names('legal-categories')
        ->except(['show']);
    Route::resource('bidang-subbidang', AdminDocumentDivisionController::class)
        ->parameters(['bidang-subbidang' => 'documentDivision'])
        ->names('document-divisions')
        ->except(['show']);
    Route::resource('artikel', AdminArticleController::class)
        ->parameters(['artikel' => 'article'])
        ->names('articles')
        ->except(['show']);
    Route::resource('faq', AdminFaqController::class)
        ->parameters(['faq' => 'faq'])
        ->names('faqs')
        ->except(['show']);
    Route::resource('konsultasi', AdminConsultationController::class)
        ->parameters(['konsultasi' => 'consultation'])
        ->names('consultations')
        ->only(['index', 'show', 'update', 'destroy']);

    Route::permanentRedirect('/documents', '/admin/dokumen')->name('legacy.documents');
    Route::permanentRedirect('/document-types', '/admin/jenis-dokumen')->name('legacy.document-types');
    Route::permanentRedirect('/legal-categories', '/admin/kategori-hukum')->name('legacy.legal-categories');
    Route::permanentRedirect('/document-divisions', '/admin/bidang-subbidang')->name('legacy.document-divisions');
    Route::permanentRedirect('/articles', '/admin/artikel')->name('legacy.articles');
    Route::permanentRedirect('/faqs', '/admin/faq')->name('legacy.faqs');
    Route::permanentRedirect('/consultations', '/admin/konsultasi')->name('legacy.consultations');
    Route::permanentRedirect('/audit-logs', '/admin/audit')->name('legacy.audit-logs');
    Route::permanentRedirect('/organization-profile', '/admin/profil-instansi')->name('legacy.organization-profile');
    Route::permanentRedirect('/kpi', '/admin/indikator')->name('legacy.kpi');
});

Route::middleware(['auth', 'two_factor.required', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/backup', [AdminBackupController::class, 'index'])->name('backups.index');
    Route::post('/backup', [AdminBackupController::class, 'store'])->name('backups.store');
    Route::get('/backup/{backup}/download', [AdminBackupController::class, 'download'])->name('backups.download');
    Route::post('/backup/{backup}/restore', [AdminBackupController::class, 'restore'])->name('backups.restore');
    Route::delete('/backup/{backup}', [AdminBackupController::class, 'destroy'])->name('backups.destroy');
    Route::resource('pengguna', AdminUserController::class)
        ->parameters(['pengguna' => 'user'])
        ->names('users')
        ->except(['show']);

    Route::permanentRedirect('/backups', '/admin/backup')->name('legacy.backups');
    Route::permanentRedirect('/users', '/admin/pengguna')->name('legacy.users');
});
