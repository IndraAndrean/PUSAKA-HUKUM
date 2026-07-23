<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Consultation;
use App\Models\Document;
use App\Models\DocumentDivision;
use App\Models\DocumentType;
use App\Models\Faq;
use App\Models\KpiTarget;
use App\Models\LegalCategory;
use App\Models\OrganizationProfile;
use App\Models\User;
use App\Observers\AuditableObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.pusaka');
        Paginator::defaultSimpleView('vendor.pagination.pusaka');

        foreach ([
            Document::class,
            DocumentDivision::class,
            DocumentType::class,
            LegalCategory::class,
            Article::class,
            Faq::class,
            Consultation::class,
            OrganizationProfile::class,
            KpiTarget::class,
            User::class,
        ] as $model) {
            $model::observe(AuditableObserver::class);
        }

        $profile = null;
        View::composer(['layouts.app', 'layouts.admin', 'public.*', 'auth.*'], function ($view) use (&$profile) {
            $profile ??= OrganizationProfile::current();
            $view->with('organizationProfile', $profile);
        });
    }
}
