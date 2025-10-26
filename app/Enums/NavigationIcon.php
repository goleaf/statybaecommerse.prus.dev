<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * NavigationIcon
 *
 * Enumeration defining a set of named constants with type safety.
 */
enum NavigationIcon: string
{
    case Document = 'document';
    case RectangleStack = 'rectangle-stack';
    case Tag = 'tag';
    case Map = 'map';
    case Cube = 'cube';
    case ShoppingBag = 'shopping-bag';
    case Users = 'users';
    case Cog = 'cog';
    case ChartBar = 'chart-bar';
    case ComputerDesktop = 'computer-desktop';
    case Megaphone = 'megaphone';
    case ArchiveBox = 'archive-box';
    case DocumentChartBar = 'document-chart-bar';
    case Gift = 'gift';
    case Globe = 'globe';
    case Building = 'building';
    case Currency = 'currency';
    case Location = 'location';
    case Collection = 'collection';
    case Attribute = 'attribute';
    case Media = 'media';
    case News = 'news';
    case Menu = 'menu';
    case City = 'city';
    case Country = 'country';
    case Address = 'address';
    case Customer = 'customer';
    case Cart = 'cart';
    case Order = 'order';
    case Coupon = 'coupon';
    case Campaign = 'campaign';
    case Analytics = 'analytics';
    case Report = 'report';
    case Activity = 'activity';
    case Stock = 'stock';
    case Price = 'price';
    case PriceList = 'price-list';
    case Discount = 'discount';
    case Referral = 'referral';
    case Partner = 'partner';
    case PartnerTier = 'partner-tier';
    case Seo = 'seo';
    case SystemSetting = 'system-setting';
    case SystemSettings = 'system-settings';

    public function icon(): string
    {
        return match ($this) {
            self::Document       => 'heroicon-o-document-text',
            self::RectangleStack => 'heroicon-o-rectangle-stack',
            self::Tag, self::Attribute => 'heroicon-o-tag',
            self::Map         => 'heroicon-o-map',
            self::Cube        => 'heroicon-o-cube',
            self::ShoppingBag => 'heroicon-o-shopping-bag',
            self::Users       => 'heroicon-o-users',
            self::Cog, self::SystemSetting, self::SystemSettings => 'heroicon-o-cog-6-tooth',
            self::ChartBar        => 'heroicon-o-chart-bar',
            self::ComputerDesktop => 'heroicon-o-computer-desktop',
            self::Megaphone, self::Campaign => 'heroicon-o-megaphone',
            self::ArchiveBox, self::Stock => 'heroicon-o-archive-box',
            self::DocumentChartBar, self::Report => 'heroicon-o-document-chart-bar',
            self::Gift, self::Referral => 'heroicon-o-gift',
            self::Globe    => 'heroicon-o-globe-alt',
            self::Building => 'heroicon-o-building-office',
            self::Currency, self::Price => 'heroicon-o-currency-dollar',
            self::Location    => 'heroicon-o-map-pin',
            self::Collection  => 'heroicon-o-folder',
            self::Media       => 'heroicon-o-photo',
            self::News        => 'heroicon-o-newspaper',
            self::Menu        => 'heroicon-o-bars-3',
            self::City        => 'heroicon-o-building-office-2',
            self::Country     => 'heroicon-o-flag',
            self::Address     => 'heroicon-o-home',
            self::Customer    => 'heroicon-o-user-group',
            self::Cart        => 'heroicon-o-shopping-cart',
            self::Order       => 'heroicon-o-clipboard-document-list',
            self::Coupon      => 'heroicon-o-ticket',
            self::Analytics   => 'heroicon-o-chart-pie',
            self::Activity    => 'heroicon-o-clock',
            self::PriceList   => 'heroicon-o-list-bullet',
            self::Discount    => 'heroicon-o-percent',
            self::Partner     => 'heroicon-o-handshake',
            self::PartnerTier => 'heroicon-o-star',
            self::Seo         => 'heroicon-o-magnifying-glass',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Document         => 'Document',
            self::RectangleStack   => 'Categories',
            self::Tag              => 'Tag',
            self::Map              => 'Map',
            self::Cube             => 'Products',
            self::ShoppingBag      => 'Orders',
            self::Users            => 'Users',
            self::Cog              => 'Settings',
            self::ChartBar         => 'Analytics',
            self::ComputerDesktop  => 'System',
            self::Megaphone        => 'Marketing',
            self::ArchiveBox       => 'Inventory',
            self::DocumentChartBar => 'Reports',
            self::Gift             => 'Referral',
            self::Globe            => 'Global',
            self::Building         => 'Building',
            self::Currency         => 'Currency',
            self::Location         => 'Location',
            self::Collection       => 'Collection',
            self::Attribute        => 'Attribute',
            self::Media            => 'Media',
            self::News             => 'News',
            self::Menu             => 'Menu',
            self::City             => 'City',
            self::Country          => 'Country',
            self::Address          => 'Address',
            self::Customer         => 'Customer',
            self::Cart             => 'Cart',
            self::Order            => 'Order',
            self::Coupon           => 'Coupon',
            self::Campaign         => 'Campaign',
            self::Analytics        => 'Analytics',
            self::Report           => 'Report',
            self::Activity         => 'Activity',
            self::Stock            => 'Stock',
            self::Price            => 'Price',
            self::PriceList        => 'Price List',
            self::Discount         => 'Discount',
            self::Referral         => 'Referral',
            self::Partner          => 'Partner',
            self::PartnerTier      => 'Partner Tier',
            self::Seo              => 'SEO',
            self::SystemSetting    => 'System Setting',
            self::SystemSettings   => 'System Settings',
        };
    }

    public static function fromResource(string $resourceName): self
    {
        return match (strtolower($resourceName)) {
            'post'           => self::Document,
            'category'       => self::RectangleStack,
            'brand'          => self::Tag,
            'region'         => self::Map,
            'product'        => self::Cube,
            'order'          => self::ShoppingBag,
            'user'           => self::Users,
            'setting'        => self::Cog,
            'analytics'      => self::ChartBar,
            'system'         => self::ComputerDesktop,
            'marketing'      => self::Megaphone,
            'inventory'      => self::ArchiveBox,
            'report'         => self::DocumentChartBar,
            'referral'       => self::Gift,
            'currency'       => self::Currency,
            'location'       => self::Location,
            'collection'     => self::Collection,
            'attribute'      => self::Attribute,
            'media'          => self::Media,
            'news'           => self::News,
            'menu'           => self::Menu,
            'city'           => self::City,
            'country'        => self::Country,
            'address'        => self::Address,
            'customer'       => self::Customer,
            'cart'           => self::Cart,
            'coupon'         => self::Coupon,
            'campaign'       => self::Campaign,
            'stock'          => self::Stock,
            'price'          => self::Price,
            'pricelist'      => self::PriceList,
            'discount'       => self::Discount,
            'partner'        => self::Partner,
            'partnertier'    => self::PartnerTier,
            'seo'            => self::Seo,
            'systemsetting'  => self::SystemSetting,
            'systemsettings' => self::SystemSettings,
            default          => self::Document,
        };
    }
}
