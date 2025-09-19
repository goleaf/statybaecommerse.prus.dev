<?php declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Analytics Translations', function () {
    it('has lithuanian translations for all analytics keys', function () {
        $ltTranslations = include base_path('lang/lt/analytics.php');

        expect($ltTranslations)->toBeArray();
        expect($ltTranslations)->toHaveKey('analytics_dashboard');
        expect($ltTranslations)->toHaveKey('analytics');
        expect($ltTranslations)->toHaveKey('total_revenue');
        expect($ltTranslations)->toHaveKey('monthly_revenue');
        expect($ltTranslations)->toHaveKey('total_orders');
        expect($ltTranslations)->toHaveKey('customers');
        expect($ltTranslations)->toHaveKey('products');
        expect($ltTranslations)->toHaveKey('reviews');
        expect($ltTranslations)->toHaveKey('status');
        expect($ltTranslations)->toHaveKey('pending');
        expect($ltTranslations)->toHaveKey('processing');
        expect($ltTranslations)->toHaveKey('shipped');
        expect($ltTranslations)->toHaveKey('delivered');
        expect($ltTranslations)->toHaveKey('cancelled');
        expect($ltTranslations)->toHaveKey('refunded');
    });

    it('has english translations for all analytics keys', function () {
        $enTranslations = include base_path('lang/en/analytics.php');

        expect($enTranslations)->toBeArray();
        expect($enTranslations)->toHaveKey('analytics_dashboard');
        expect($enTranslations)->toHaveKey('analytics');
        expect($enTranslations)->toHaveKey('total_revenue');
        expect($enTranslations)->toHaveKey('monthly_revenue');
        expect($enTranslations)->toHaveKey('total_orders');
        expect($enTranslations)->toHaveKey('customers');
        expect($enTranslations)->toHaveKey('products');
        expect($enTranslations)->toHaveKey('reviews');
        expect($enTranslations)->toHaveKey('status');
        expect($enTranslations)->toHaveKey('pending');
        expect($enTranslations)->toHaveKey('processing');
        expect($enTranslations)->toHaveKey('shipped');
        expect($enTranslations)->toHaveKey('delivered');
        expect($enTranslations)->toHaveKey('cancelled');
        expect($enTranslations)->toHaveKey('refunded');
    });

    it('lithuanian and english translations have same keys', function () {
        $ltTranslations = include base_path('lang/lt/analytics.php');
        $enTranslations = include base_path('lang/en/analytics.php');

        $ltKeys = array_keys($ltTranslations);
        $enKeys = array_keys($enTranslations);

        sort($ltKeys);
        sort($enKeys);

        expect($ltKeys)->toEqual($enKeys);
    });

    it('can translate analytics dashboard title', function () {
        // Test Lithuanian translations directly from file
        $ltTranslations = include base_path('lang/lt/analytics.php');
        expect($ltTranslations['analytics_dashboard'])->toBe('Analitikos skydelis');

        // Test English translations directly from file
        $enTranslations = include base_path('lang/en/analytics.php');
        expect($enTranslations['analytics_dashboard'])->toBe('Analytics Dashboard');
    });

    it('can translate order statuses', function () {
        app()->setLocale('lt');
        expect(__('analytics.pending'))->toBe('Laukiantis');
        expect(__('analytics.processing'))->toBe('Apdorojamas');
        expect(__('analytics.shipped'))->toBe('Išsiųstas');
        expect(__('analytics.delivered'))->toBe('Pristatytas');
        expect(__('analytics.cancelled'))->toBe('Atšauktas');
        expect(__('analytics.refunded'))->toBe('Grąžintas');

        app()->setLocale('en');
        expect(__('analytics.pending'))->toBe('Pending');
        expect(__('analytics.processing'))->toBe('Processing');
        expect(__('analytics.shipped'))->toBe('Shipped');
        expect(__('analytics.delivered'))->toBe('Delivered');
        expect(__('analytics.cancelled'))->toBe('Cancelled');
        expect(__('analytics.refunded'))->toBe('Refunded');
    });

    it('can translate revenue and financial terms', function () {
        app()->setLocale('lt');
        expect(__('analytics.total_revenue'))->toBe('Bendros pajamos');
        expect(__('analytics.monthly_revenue'))->toBe('Mėnesio pajamos');
        expect(__('analytics.avg_order_value'))->toBe('Vidutinė užsakymo vertė');

        app()->setLocale('en');
        expect(__('analytics.total_revenue'))->toBe('Total Revenue');
        expect(__('analytics.monthly_revenue'))->toBe('Monthly Revenue');
        expect(__('analytics.avg_order_value'))->toBe('Avg Order Value');
    });

    it('can translate customer and product terms', function () {
        app()->setLocale('lt');
        expect(__('analytics.customers'))->toBe('Klientai');
        expect(__('analytics.products'))->toBe('Produktai');
        expect(__('analytics.active_customers'))->toBe('Aktyvūs klientai');
        expect(__('analytics.featured'))->toBe('rekomenduojami');

        app()->setLocale('en');
        expect(__('analytics.customers'))->toBe('Customers');
        expect(__('analytics.products'))->toBe('Products');
        expect(__('analytics.active_customers'))->toBe('Active Customers');
        expect(__('analytics.featured'))->toBe('featured');
    });

    it('can translate date and time terms', function () {
        app()->setLocale('lt');
        expect(__('analytics.date'))->toBe('Data');
        expect(__('analytics.from_date'))->toBe('Nuo datos');
        expect(__('analytics.until_date'))->toBe('Iki datos');
        expect(__('analytics.this_month'))->toBe('Šį mėnesį');

        app()->setLocale('en');
        expect(__('analytics.date'))->toBe('Date');
        expect(__('analytics.from_date'))->toBe('From Date');
        expect(__('analytics.until_date'))->toBe('Until Date');
        expect(__('analytics.this_month'))->toBe('This Month');
    });

    it('can translate action terms', function () {
        app()->setLocale('lt');
        expect(__('analytics.export_report'))->toBe('Eksportuoti ataskaitą');
        expect(__('analytics.refresh_data'))->toBe('Atnaujinti duomenis');
        expect(__('analytics.view'))->toBe('Peržiūrėti');

        app()->setLocale('en');
        expect(__('analytics.export_report'))->toBe('Export Report');
        expect(__('analytics.refresh_data'))->toBe('Refresh Data');
        expect(__('analytics.view'))->toBe('View');
    });

    it('can translate success messages', function () {
        app()->setLocale('lt');
        expect(__('analytics.report_exported_successfully'))->toBe('Ataskaita sėkmingai eksportuota');
        expect(__('analytics.data_refreshed_successfully'))->toBe('Duomenys sėkmingai atnaujinti');

        app()->setLocale('en');
        expect(__('analytics.report_exported_successfully'))->toBe('Report exported successfully');
        expect(__('analytics.data_refreshed_successfully'))->toBe('Data refreshed successfully');
    });
});
