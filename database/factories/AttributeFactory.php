<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AttributeFactory extends Factory
{
    protected $model = \App\Models\Attribute::class;
    private static bool $schemaEnsured = false;

    public function definition(): array
    {
        self::ensureSchema();

        $label = $this->faker->randomElement(['Color', 'Size', 'Material', 'Fit', 'Length', 'Style']).' '.$this->faker->unique()->numerify('###');
        $types = ['text', 'number', 'boolean', 'select', 'multiselect', 'color', 'date', 'textarea', 'file', 'image'];
        $groupNames = ['basic_info', 'technical_specs', 'appearance', 'dimensions', 'materials', 'features', 'compatibility', 'warranty', 'shipping', 'seo'];

        return [
            'name' => $label,
            'slug' => strtolower(str_replace(' ', '-', $label)),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->optional(0.7)->sentence(),
            'validation_rules' => $this->faker->optional(0.3)->randomElement([
                ['required' => true, 'max' => 255],
                ['min' => 1, 'max' => 100],
                ['required' => true],
            ]),
            'default_value' => $this->faker->optional(0.4)->randomElement(['red', 'blue', 'green', 'small', 'medium', 'large']),
            'is_required' => $this->faker->boolean(30),
            'is_filterable' => $this->faker->boolean(80),
            'is_searchable' => $this->faker->boolean(60),
            'is_visible' => $this->faker->boolean(90),
            'is_editable' => $this->faker->boolean(85),
            'is_sortable' => $this->faker->boolean(70),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_enabled' => $this->faker->boolean(95),
            'is_active' => $this->faker->boolean(95),
            'category_id' => null,
            'group_name' => $this->faker->optional(0.6)->randomElement($groupNames),
            'icon' => $this->faker->optional(0.4)->randomElement([
                'heroicon-o-adjustments-horizontal',
                'heroicon-o-color-swatch',
                'heroicon-o-cube',
                'heroicon-o-cog-6-tooth',
                'heroicon-o-tag',
            ]),
            'color' => $this->faker->optional(0.3)->hexColor(),
            'min_value' => $this->faker->optional(0.2)->randomFloat(2, 0, 10),
            'max_value' => $this->faker->optional(0.2)->randomFloat(2, 10, 100),
            'step_value' => $this->faker->optional(0.1)->randomFloat(2, 0.1, 1),
            'placeholder' => $this->faker->optional(0.5)->sentence(3),
            'help_text' => $this->faker->optional(0.3)->sentence(),
            'meta_data' => $this->faker->optional(0.2)->randomElement([
                ['unit' => 'cm', 'precision' => 2],
                ['unit' => 'kg', 'precision' => 1],
                ['format' => 'currency', 'currency' => 'EUR'],
                ['format' => 'percentage'],
            ]),
        ];
    }

    private static function ensureSchema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        $schema = Schema::connection(config('database.default', 'sqlite'));

        if (! $schema->hasTable('attributes')) {
            $schema->create('attributes', static function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type')->default('text');
                $table->text('description')->nullable();
                $table->text('validation_rules')->nullable();
                $table->text('default_value')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_filterable')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_editable')->default(true);
                $table->boolean('is_sortable')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('group_name')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->decimal('min_value', 10, 2)->nullable();
                $table->decimal('max_value', 10, 2)->nullable();
                $table->decimal('step_value', 10, 2)->nullable();
                $table->string('placeholder')->nullable();
                $table->text('help_text')->nullable();
                $table->json('meta_data')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('attribute_values')) {
            $schema->create('attribute_values', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('value');
                $table->string('slug');
                $table->string('attribute_value_type')->nullable();
                $table->string('valueable_type')->nullable();
                $table->unsignedBigInteger('valueable_id')->nullable();
                $table->string('color_code')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->string('display_value')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('product_attributes')) {
            $schema->create('product_attributes', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('attribute_id');
                $table->unsignedBigInteger('attribute_value_id');
                $table->timestamps();

                $table->index('product_id');
                $table->index('attribute_id');
                $table->index('attribute_value_id');
            });
        }

        self::$schemaEnsured = true;
    }
}
