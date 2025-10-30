<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\EmailMarketingPage;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(EmailMarketingPage::class)]
final class EmailMarketingPageTest extends TestCase
{
    public function test_page_metadata_matches_expected_copy(): void
    {
        // Resolve the page so we can verify its localized labels.
        $page = app(EmailMarketingPage::class);

        $this->assertSame('Email Marketing Manager', $page->getTitle());
        $this->assertSame('Email Marketing Manager', $page->getHeading());
        $this->assertSame('Manage your email campaigns and subscriber sync with Mailchimp', $page->getSubheading());
        $this->assertSame('heroicon-o-envelope-open', EmailMarketingPage::getNavigationIcon());
    }
}
