<?php declare(strict_types=1);

use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing, assertSoftDeleted};

beforeEach(function () {
	$this->admin = User::factory()->create();
	$adminRole = Role::firstOrCreate(['name' => 'admin']);
	$this->admin->assignRole($adminRole);
	$this->user = User::factory()->create();
	$this->product = Product::factory()->create();
	actingAs($this->admin);
});

it('can render review resource index page', function () {
	$this->get(ReviewResource::getUrl('index'))->assertSuccessful();
});

it('can render review resource create page', function () {
	$this->get(ReviewResource::getUrl('create'))->assertSuccessful();
});

it('can create review', function () {
	$newData = [
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
		'title' => 'Great Product',
		'content' => 'This product exceeded my expectations. Highly recommended!',
		'rating' => 5,
		'is_approved' => false,
	];

	Livewire::test(ReviewResource\Pages\CreateReview::class)
		->fillForm($newData)
		->call('create')
		->assertHasNoFormErrors();

	assertDatabaseHas('reviews', [
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
		'rating' => 5,
		'is_approved' => false,
		'title' => 'Great Product',
	]);
});

it('can render review resource view page', function () {
	$review = Review::factory()->create([
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	$this->get(ReviewResource::getUrl('view', ['record' => $review]))->assertSuccessful();
});

it('can render review resource edit page', function () {
	$review = Review::factory()->create([
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	$this->get(ReviewResource::getUrl('edit', ['record' => $review]))->assertSuccessful();
});

it('can update review', function () {
	$review = Review::factory()->create([
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);
	
	$newData = [
		'title' => 'Updated Review Title',
		'content' => 'Updated review content',
		'rating' => 4,
		'is_approved' => true,
	];

	Livewire::test(ReviewResource\Pages\EditReview::class, [
		'record' => $review->getRouteKey(),
	])
		->fillForm(array_merge($newData, [
			'product_id' => $this->product->id,
			'user_id' => $this->user->id,
		]))
		->call('save')
		->assertHasNoFormErrors();

	assertDatabaseHas('reviews', array_merge(['id' => $review->id], $newData));
});

it('can delete review', function () {
	$review = Review::factory()->create([
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	Livewire::test(ReviewResource\Pages\EditReview::class, [
		'record' => $review->getRouteKey(),
	])
		->callAction('delete');

	assertSoftDeleted('reviews', ['id' => $review->id]);
});

it('can list reviews', function () {
	$reviews = Review::factory()->count(5)->create([
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	Livewire::test(ReviewResource\Pages\ListReviews::class)
		->assertCanSeeTableRecords($reviews);
});

it('can filter reviews by rating', function () {
	$fiveStarReview = Review::factory()->create([
		'rating' => 5,
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);
	$oneStarReview = Review::factory()->create([
		'rating' => 1,
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	Livewire::test(ReviewResource\Pages\ListReviews::class)
		->filterTable('rating', 5)
		->assertCanSeeTableRecords([$fiveStarReview])
		->assertCanNotSeeTableRecords([$oneStarReview]);
});

it('can filter approved reviews', function () {
	$approvedReview = Review::factory()->create([
		'is_approved' => true,
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);
	$pendingReview = Review::factory()->create([
		'is_approved' => false,
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	Livewire::test(ReviewResource\Pages\ListReviews::class)
		->filterTable('approved')
		->assertCanSeeTableRecords([$approvedReview])
		->assertCanNotSeeTableRecords([$pendingReview]);
});

it('can filter pending reviews', function () {
	$approvedReview = Review::factory()->create([
		'is_approved' => true,
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);
	$pendingReview = Review::factory()->create([
		'is_approved' => false,
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	Livewire::test(ReviewResource\Pages\ListReviews::class)
		->filterTable('pending')
		->assertCanSeeTableRecords([$pendingReview])
		->assertCanNotSeeTableRecords([$approvedReview]);
});

it('can approve review using action', function () {
	$review = Review::factory()->create([
		'is_approved' => false,
		'approved_at' => null,
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	Livewire::test(ReviewResource\Pages\ListReviews::class)
		->callTableAction('approve', $review);

	assertDatabaseHas('reviews', [
		'id' => $review->id,
		'is_approved' => true,
	]);
});

it('can reject review using action', function () {
	$review = Review::factory()->create([
		'is_approved' => true,
		'approved_at' => now(),
		'product_id' => $this->product->id,
		'user_id' => $this->user->id,
	]);

	Livewire::test(ReviewResource\Pages\ListReviews::class)
		->callTableAction('reject', $review);

	assertDatabaseHas('reviews', [
		'id' => $review->id,
		'is_approved' => false,
		'approved_at' => null,
	]);
});

it('validates required fields when creating review', function () {
	Livewire::test(ReviewResource\Pages\CreateReview::class)
		->fillForm([])
		->call('create')
		->assertHasFormErrors(['product_id', 'user_id', 'title', 'content', 'rating']);
});

it('validates rating is within valid range', function () {
	Livewire::test(ReviewResource\Pages\CreateReview::class)
		->fillForm([
			'product_id' => $this->product->id,
			'user_id' => $this->user->id,
			'title' => 'Test Review',
			'content' => 'Test content',
			'rating' => 6, // Invalid - over 5
		])
		->call('create')
		->assertHasFormErrors(['rating']);

	Livewire::test(ReviewResource\Pages\CreateReview::class)
		->fillForm([
			'product_id' => $this->product->id,
			'user_id' => $this->user->id,
			'title' => 'Test Review',
			'content' => 'Test content',
			'rating' => 0, // Invalid - under 1
		])
		->call('create')
		->assertHasFormErrors(['rating']);
});
