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
            self::ComputerDesktop => 'heroicon-o-computer-desktop',
            self::Megaphone, self::Campaign => 'heroicon-o-megaphone',
            self::ArchiveBox, self::Stock => 'heroicon-o-archive-box',
            self::DocumentChartBar, self::Report => 'heroicon-o-document-chart-bar',
            self::Gift, self::Referral => 'heroicon-o-gift',
            self::Globe    => 'heroicon-o-globe-alt',
            self::Building => 'heroicon-o-building-office',
            self::Currency, self::Price => 'heroicon-o-currency-euro',
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
            self::Document         => __('enums.navigation_icon.document'),
            self::RectangleStack   => __('enums.navigation_icon.rectangle-stack'),
            self::Tag              => __('enums.navigation_icon.tag'),
            self::Map              => __('enums.navigation_icon.map'),
            self::Cube             => __('enums.navigation_icon.cube'),
            self::ShoppingBag      => __('enums.navigation_icon.shopping-bag'),
            self::Users            => __('enums.navigation_icon.users'),
            self::Cog              => __('enums.navigation_icon.cog'),
            self::ComputerDesktop  => __('enums.navigation_icon.computer-desktop'),
            self::Megaphone        => __('enums.navigation_icon.megaphone'),
            self::ArchiveBox       => __('enums.navigation_icon.archive-box'),
            self::DocumentChartBar => __('enums.navigation_icon.document-chart-bar'),
            self::Gift             => __('enums.navigation_icon.gift'),
            self::Globe            => __('enums.navigation_icon.globe'),
            self::Building         => __('enums.navigation_icon.building'),
            self::Currency         => __('enums.navigation_icon.currency'),
            self::Location         => __('enums.navigation_icon.location'),
            self::Collection       => __('enums.navigation_icon.collection'),
            self::Attribute        => __('enums.navigation_icon.attribute'),
            self::Media            => __('enums.navigation_icon.media'),
            self::News             => __('enums.navigation_icon.news'),
            self::Menu             => __('enums.navigation_icon.menu'),
            self::City             => __('enums.navigation_icon.city'),
            self::Country          => __('enums.navigation_icon.country'),
            self::Address          => __('enums.navigation_icon.address'),
            self::Customer         => __('enums.navigation_icon.customer'),
            self::Cart             => __('enums.navigation_icon.cart'),
            self::Order            => __('enums.navigation_icon.order'),
            self::Coupon           => __('enums.navigation_icon.coupon'),
            self::Campaign         => __('enums.navigation_icon.campaign'),
            self::Report           => __('enums.navigation_icon.report'),
            self::Activity         => __('enums.navigation_icon.activity'),
            self::Stock            => __('enums.navigation_icon.stock'),
            self::Price            => __('enums.navigation_icon.price'),
            self::PriceList        => __('enums.navigation_icon.price-list'),
            self::Discount         => __('enums.navigation_icon.discount'),
            self::Referral         => __('enums.navigation_icon.referral'),
            self::Partner          => __('enums.navigation_icon.partner'),
            self::PartnerTier      => __('enums.navigation_icon.partner-tier'),
            self::Seo              => __('enums.navigation_icon.seo'),
            self::SystemSetting    => __('enums.navigation_icon.system-setting'),
            self::SystemSettings   => __('enums.navigation_icon.system-settings'),
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
