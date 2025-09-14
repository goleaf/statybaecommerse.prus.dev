# Global Scopes Implementation

This directory contains global scopes that automatically apply query constraints to models, ensuring consistent filtering across the application.

## Available Global Scopes

### ActiveScope
Automatically filters records where `is_active = true`, `is_enabled = true`, or `is_visible = true` (in order of preference).

**Applied to:**
- Product
- Category  
- Brand
- Collection
- News
- Post
- User
- Discount
- Coupon
- FeatureFlag
- Subscriber
- Partner
- Campaign
- Attribute
- Order
- Channel
- Zone
- Menu
- Inventory
- VariantInventory
- ProductImage
- ProductFeature
- ProductSimilarity
- NewsCategory
- NewsTag
- NewsComment
- NewsImage
- AttributeValue
- CollectionRule
- AdminUser
- CampaignClick
- CampaignConversion
- CampaignCustomerSegment
- CampaignProductTarget
- CampaignSchedule
- CampaignView
- City
- Country
- Region
- Currency
- CustomerGroup
- PartnerTier
- DiscountCode
- DiscountCondition
- DiscountRedemption
- Price
- PriceList
- PriceListItem
- Document
- DocumentTemplate
- Legal
- Location
- OrderShipping

### PublishedScope
Automatically filters records where `published_at` is not null and is in the past.

**Applied to:**
- Product
- News
- Post
- Legal

### VisibleScope
Automatically filters records where `is_visible = true`.

**Applied to:**
- Product
- Category
- Collection
- News
- Attribute
- MenuItem
- NewsComment

### EnabledScope
Automatically filters records where `is_enabled = true`.

**Applied to:**
- Category
- Brand
- Discount
- FeatureFlag
- Partner
- Campaign
- Attribute
- Channel
- Zone
- ProductVariant
- AttributeValue
- City
- Region
- Currency
- CustomerGroup
- PartnerTier
- Price
- PriceList
- Legal
- Location

### ApprovedScope
Automatically filters records where `is_approved = true`.

**Applied to:**
- Review
- NewsComment

### StatusScope
Automatically filters records by status field with model-specific allowed statuses.

**Applied to:**
- Order
- Campaign
- Channel
- Zone
- ProductVariant
- VariantInventory
- ProductRequest
- CampaignConversion
- DiscountCode
- DiscountRedemption
- Document

### ActiveCampaignScope
Automatically filters campaigns that are currently active based on start/end dates.

**Applied to:**
- Campaign

### TenantScope
Automatically filters records by tenant for multi-tenant data isolation.

**Applied to:**
- Models with tenant_id, company_id, organization_id, or workspace_id fields

### UserOwnedScope
Automatically filters records by user ownership for data privacy.

**Applied to:**
- CartItem
- Address
- UserWishlist
- WishlistItem
- OrderItem
- StockMovement
- UserBehavior
- UserPreference
- UserProductInteraction
- ProductHistory
- ProductComparison
- ProductRequest
- CouponUsage
- AnalyticsEvent
- DiscountRedemption
- OrderShipping

### DateRangeScope
Automatically filters records by date ranges for time-sensitive content.

**Applied to:**
- Models with date fields like published_at, expires_at, scheduled_at
- DiscountCode
- Price
- PriceList
- PriceListItem

## Usage Examples

### Basic Usage
```php
// These queries will automatically apply global scopes
$products = Product::all(); // Only active, published, visible products
$categories = Category::all(); // Only active, enabled, visible categories
$reviews = Review::all(); // Only active, approved reviews
```

### Bypassing Global Scopes
```php
// To get all records including inactive ones
$allProducts = Product::withoutGlobalScope(ActiveScope::class)->get();
$allProducts = Product::withoutGlobalScopes()->get();

// To get all reviews including unapproved ones
$allReviews = Review::withoutGlobalScope(ApprovedScope::class)->get();
```

### Combining with Other Scopes
```php
// Global scopes work seamlessly with local scopes
$featuredProducts = Product::featured()->get(); // Active, published, visible, AND featured
$recentCategories = Category::recent(30)->get(); // Active, enabled, visible, AND recent
```

## Benefits

1. **Consistency**: Ensures all queries automatically filter inactive/unpublished content
2. **Security**: Prevents accidental exposure of inactive content
3. **Performance**: Reduces the need to manually add filters to every query
4. **Maintainability**: Centralized filtering logic that's easy to update

## Implementation Details

Global scopes are applied using Laravel 11's `ScopedBy` attribute:

```php
#[ScopedBy([ActiveScope::class, PublishedScope::class, VisibleScope::class])]
class Product extends Model
{
    // Model implementation
}
```

The scopes automatically detect which columns exist in the model's table and apply the appropriate filters.
