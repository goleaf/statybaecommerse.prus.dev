<?php declare(strict_types=1);

namespace App\Enums;

enum NavigationIcon: string
{
    case Document = 'heroicon-o-document-text';
    case RectangleStack = 'heroicon-o-rectangle-stack';
    case Tag = 'heroicon-o-tag';
    case Map = 'heroicon-o-map';
    case Cube = 'heroicon-o-cube';
    case ShoppingBag = 'heroicon-o-shopping-bag';
    case Users = 'heroicon-o-users';
    case Cog = 'heroicon-o-cog-6-tooth';
    case ChartBar = 'heroicon-o-chart-bar';
    case ComputerDesktop = 'heroicon-o-computer-desktop';
    case Megaphone = 'heroicon-o-megaphone';
    case ArchiveBox = 'heroicon-o-archive-box';
    case DocumentChartBar = 'heroicon-o-document-chart-bar';
    case Gift = 'heroicon-o-gift';
    case Globe = 'heroicon-o-globe-alt';
    case Building = 'heroicon-o-building-office';
    case Currency = 'heroicon-o-currency-dollar';
    case Location = 'heroicon-o-map-pin';
    case Collection = 'heroicon-o-folder';
    case Attribute = 'heroicon-o-tag';
    case Media = 'heroicon-o-photo';
    case News = 'heroicon-o-newspaper';
    case Menu = 'heroicon-o-bars-3';
    case City = 'heroicon-o-building-office-2';
    case Country = 'heroicon-o-flag';
    case Zone = 'heroicon-o-map';
    case Address = 'heroicon-o-home';
    case Customer = 'heroicon-o-user-group';
    case Cart = 'heroicon-o-shopping-cart';
    case Order = 'heroicon-o-clipboard-document-list';
    case Coupon = 'heroicon-o-ticket';
    case Campaign = 'heroicon-o-megaphone';
    case Analytics = 'heroicon-o-chart-pie';
    case Report = 'heroicon-o-document-chart-bar';
    case Activity = 'heroicon-o-clock';
    case Stock = 'heroicon-o-archive-box';
    case Price = 'heroicon-o-currency-dollar';
    case PriceList = 'heroicon-o-list-bullet';
    case Discount = 'heroicon-o-percent';
    case Referral = 'heroicon-o-gift';
    case Partner = 'heroicon-o-handshake';
    case PartnerTier = 'heroicon-o-star';
    case Seo = 'heroicon-o-magnifying-glass';
    case SystemSetting = 'heroicon-o-cog-6-tooth';
    case SystemSettings = 'heroicon-o-cog-6-tooth';

    public function label(): string
    {
        return match ($this) {
            self::Document => 'Document',
            self::RectangleStack => 'Categories',
            self::Tag => 'Tag',
            self::Map => 'Map',
            self::Cube => 'Products',
            self::ShoppingBag => 'Orders',
            self::Users => 'Users',
            self::Cog => 'Settings',
            self::ChartBar => 'Analytics',
            self::ComputerDesktop => 'System',
            self::Megaphone => 'Marketing',
            self::ArchiveBox => 'Inventory',
            self::DocumentChartBar => 'Reports',
            self::Gift => 'Referral',
            self::Globe => 'Global',
            self::Building => 'Building',
            self::Currency => 'Currency',
            self::Location => 'Location',
            self::Collection => 'Collection',
            self::Attribute => 'Attribute',
            self::Media => 'Media',
            self::News => 'News',
            self::Menu => 'Menu',
            self::City => 'City',
            self::Country => 'Country',
            self::Zone => 'Zone',
            self::Address => 'Address',
            self::Customer => 'Customer',
            self::Cart => 'Cart',
            self::Order => 'Order',
            self::Coupon => 'Coupon',
            self::Campaign => 'Campaign',
            self::Analytics => 'Analytics',
            self::Report => 'Report',
            self::Activity => 'Activity',
            self::Stock => 'Stock',
            self::Price => 'Price',
            self::PriceList => 'Price List',
            self::Discount => 'Discount',
            self::Referral => 'Referral',
            self::Partner => 'Partner',
            self::PartnerTier => 'Partner Tier',
            self::Seo => 'SEO',
            self::SystemSetting => 'System Setting',
            self::SystemSettings => 'System Settings',
        };
    }

    public static function fromResource(string $resourceName): self
    {
        return match (strtolower($resourceName)) {
            'post' => self::Document,
            'category' => self::RectangleStack,
            'brand' => self::Tag,
            'region' => self::Map,
            'product' => self::Cube,
            'order' => self::ShoppingBag,
            'user' => self::Users,
            'setting' => self::Cog,
            'analytics' => self::ChartBar,
            'system' => self::ComputerDesktop,
            'marketing' => self::Megaphone,
            'inventory' => self::ArchiveBox,
            'report' => self::DocumentChartBar,
            'referral' => self::Gift,
            'currency' => self::Currency,
            'location' => self::Location,
            'collection' => self::Collection,
            'attribute' => self::Attribute,
            'media' => self::Media,
            'news' => self::News,
            'menu' => self::Menu,
            'city' => self::City,
            'country' => self::Country,
            'zone' => self::Zone,
            'address' => self::Address,
            'customer' => self::Customer,
            'cart' => self::Cart,
            'coupon' => self::Coupon,
            'campaign' => self::Campaign,
            'stock' => self::Stock,
            'price' => self::Price,
            'pricelist' => self::PriceList,
            'discount' => self::Discount,
            'partner' => self::Partner,
            'partnertier' => self::PartnerTier,
            'seo' => self::Seo,
            'systemsetting' => self::SystemSetting,
            'systemsettings' => self::SystemSettings,
            default => self::Document,
        };
    }
}
