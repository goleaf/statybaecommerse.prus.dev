<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Cache\CacheWarmer;
use App\Support\Localization\LocaleResolver;
use Illuminate\Console\Command;

class WarmCaches extends Command
{
    protected $signature = 'cache:warm {--locale= : Specific locale to warm}';

    protected $description = 'Warm critical storefront caches for optimal performance';

    public function handle(CacheWarmer $warmer, LocaleResolver $localeResolver): int
    {
        $this->info('Starting cache warming...');

        $locale = $this->option('locale');
        
        if ($locale) {
            if (!in_array($locale, $localeResolver->getSupportedLocales(), true)) {
                $this->error("Unsupported locale: {$locale}");
                return self::FAILURE;
            }
            
            $this->info("Warming caches for locale: {$locale}");
            $warmer->warmHomePageCaches($locale);
            $warmer->warmNavigationCaches($locale);
            $warmer->warmCatalogCaches($locale);
        } else {
            $this->info('Warming caches for all supported locales...');
            $warmer->warmStorefront();
        }

        $this->info('Cache warming completed successfully!');
        
        return self::SUCCESS;
    }
}