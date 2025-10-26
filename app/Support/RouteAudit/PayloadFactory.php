<?php

declare(strict_types=1);

namespace App\Support\RouteAudit;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory as ModelFactory;
use Illuminate\Database\Eloquent\Factories\FactoryNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Throwable;

final class PayloadFactory
{
    private Generator $faker;

    public function __construct(?Generator $faker = null)
    {
        $this->faker = $faker ?? FakerFactory::create();
    }

    /**
     * Build a payload for the provided route metadata entry.
     *
     * @param  array<string, mixed> $routeMeta
     * @return array<string, mixed>
     */
    public function build(array $routeMeta): array
    {
        $methods = array_map('strtoupper', $routeMeta['methods'] ?? []);
        $requestMethods = array_intersect($methods, ['POST', 'PUT', 'PATCH', 'DELETE']);

        if ($requestMethods === []) {
            return [];
        }

        $formRequests = $routeMeta['controller']['formRequests'] ?? [];

        if ($formRequests !== []) {
            $payload = $this->buildFromFormRequests($formRequests);

            if ($payload !== []) {
                return $payload;
            }
        }

        return $this->fallbackPayload($routeMeta);
    }

    /**
     * @param  list<class-string>   $formRequestClasses
     * @return array<string, mixed>
     */
    private function buildFromFormRequests(array $formRequestClasses): array
    {
        $payload = [];
        $confirmationFields = [];

        foreach ($formRequestClasses as $class) {
            try {
                $request = App::make($class);
            } catch (Throwable) {
                continue;
            }

            if (! method_exists($request, 'rules')) {
                continue;
            }

            try {
                $rules = $request->rules();
            } catch (Throwable) {
                continue;
            }

            foreach ($rules as $field => $ruleSet) {
                $normalizedRules = $this->normaliseRules($ruleSet);

                if ($normalizedRules === []) {
                    continue;
                }

                if (in_array('sometimes', $normalizedRules, true) && ! in_array('required', $normalizedRules, true)) {
                    continue;
                }

                if ($this->shouldProduceNull($normalizedRules)) {
                    $payload[$field] = null;

                    continue;
                }

                $value = $this->valueForRules($field, $normalizedRules);
                $payload[$field] = $value;

                if (in_array('confirmed', $normalizedRules, true)) {
                    $confirmationFields[$field . '_confirmation'] = $value;
                }
            }
        }

        foreach ($confirmationFields as $field => $value) {
            $payload[$field] = $value;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed> $routeMeta
     * @return array<string, mixed>
     */
    private function fallbackPayload(array $routeMeta): array
    {
        $uri = (string) ($routeMeta['uri'] ?? '');
        $payload = [];

        $payload['name'] = $this->faker->words(3, true);

        if (Str::contains($uri, 'email')) {
            $payload['email'] = $this->faker->unique()->safeEmail();
        }

        $payload['description'] = $this->faker->sentence();

        return $payload;
    }

    /**
     * @param array<int, string> $rules
     */
    private function shouldProduceNull(array $rules): bool
    {
        if (! in_array('nullable', $rules, true)) {
            return false;
        }

        if (in_array('required', $rules, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param  array{0?:string}|string|array<int, string|\Illuminate\Contracts\Validation\ValidationRule|Unique|Exists> $ruleSet
     * @return array<int, string>
     */
    private function normaliseRules(mixed $ruleSet): array
    {
        if (is_string($ruleSet)) {
            $ruleSet = explode('|', $ruleSet);
        }

        if ($ruleSet instanceof Unique || $ruleSet instanceof Exists) {
            return [$this->stringifyRuleObject($ruleSet)];
        }

        if (! is_array($ruleSet)) {
            return [];
        }

        $normalized = [];

        foreach ($ruleSet as $rule) {
            if ($rule instanceof Unique || $rule instanceof Exists) {
                $normalized[] = $this->stringifyRuleObject($rule);
            } elseif (is_string($rule)) {
                $normalized[] = $rule;
            }
        }

        return array_values(array_filter($normalized, static fn ($value) => $value !== ''));
    }

    private function stringifyRuleObject(Unique|Exists $rule): string
    {
        $table = $rule->table ?? null;
        $column = $rule->column ?? null;

        if ($table === null) {
            return '';
        }

        return sprintf('%s:%s,%s', $rule instanceof Unique ? 'unique' : 'exists', $table, $column ?? 'id');
    }

    /**
     * @param array<int, string> $rules
     */
    private function valueForRules(string $field, array $rules): mixed
    {
        $rules = array_map('strtolower', $rules);

        if (in_array('boolean', $rules, true)) {
            return $this->faker->boolean();
        }

        if (in_array('uuid', $rules, true)) {
            return $this->faker->uuid();
        }

        if (in_array('email', $rules, true)) {
            return $this->faker->unique()->safeEmail();
        }

        if (in_array('url', $rules, true) || in_array('active_url', $rules, true)) {
            return $this->faker->url();
        }

        if (in_array('integer', $rules, true)) {
            $bounds = $this->resolveNumericBounds($rules, defaultMin: 1, defaultMax: 1000);

            return $this->faker->numberBetween($bounds['min'], $bounds['max']);
        }

        if (in_array('numeric', $rules, true)) {
            $bounds = $this->resolveNumericBounds($rules, defaultMin: 1, defaultMax: 1000);

            return $this->faker->randomFloat(2, $bounds['min'], $bounds['max']);
        }

        if (in_array('date', $rules, true) || Str::startsWith($this->firstRuleMatching($rules, 'date_format'), 'date_format:')) {
            $formatRule = $this->firstRuleMatching($rules, 'date_format');
            $format = $formatRule !== null ? substr($formatRule, strlen('date_format:')) : 'Y-m-d';

            return $this->faker->date($format);
        }

        if (in_array('array', $rules, true)) {
            return [$field => $this->faker->word()];
        }

        $inRule = $this->firstRuleMatching($rules, 'in:');
        if ($inRule !== null) {
            $options = array_filter(explode(',', substr($inRule, 3)));
            if ($options !== []) {
                return Arr::random($options);
            }
        }

        $existsRule = $this->firstRuleMatching($rules, 'exists:');
        if ($existsRule !== null) {
            $value = $this->valueFromExistsRule($existsRule);
            if ($value !== null) {
                return $value;
            }
        }

        $uniqueRule = $this->firstRuleMatching($rules, 'unique:');
        if ($uniqueRule !== null) {
            $value = $this->uniqueCandidate($uniqueRule);
            if ($value !== null) {
                return $value;
            }
        }

        $maxRule = $this->firstRuleMatching($rules, 'max:');
        $length = $maxRule !== null ? min((int) substr($maxRule, 4), 255) : 120;

        return $this->faker->text(max(10, $length));
    }

    /**
     * @param  array<int, string>      $rules
     * @return array{min:int, max:int}
     */
    private function resolveNumericBounds(array $rules, int $defaultMin, int $defaultMax): array
    {
        $between = $this->firstRuleMatching($rules, 'between:');
        if ($between !== null) {
            $parts = explode(',', substr($between, 8));
            $min = isset($parts[0]) ? (int) $parts[0] : $defaultMin;
            $max = isset($parts[1]) ? (int) $parts[1] : $defaultMax;

            return [
                'min' => $min,
                'max' => max($min, $max),
            ];
        }

        $minRule = $this->firstRuleMatching($rules, 'min:');
        $maxRule = $this->firstRuleMatching($rules, 'max:');

        $min = $minRule !== null ? (int) substr($minRule, 4) : $defaultMin;
        $max = $maxRule !== null ? (int) substr($maxRule, 4) : $defaultMax;

        return [
            'min' => $min,
            'max' => max($min, $max),
        ];
    }

    /**
     * @param array<int, string> $rules
     */
    private function firstRuleMatching(array $rules, string $prefix): ?string
    {
        foreach ($rules as $rule) {
            if (Str::startsWith($rule, $prefix)) {
                return $rule;
            }
        }

        return null;
    }

    private function valueFromExistsRule(string $rule): mixed
    {
        $parts = explode(',', substr($rule, strlen('exists:')));
        $table = $parts[0] ?? null;
        $column = $parts[1] ?? 'id';

        if ($table === null) {
            return null;
        }

        $value = DB::table($table)->orderBy($column)->value($column);

        if ($value !== null) {
            return $value;
        }

        $modelClass = $this->guessModelForTable($table);

        if ($modelClass !== null) {
            return $this->createModelInstance($modelClass)?->{$column} ?? null;
        }

        return 1;
    }

    private function uniqueCandidate(string $rule): ?string
    {
        $parts = explode(',', substr($rule, strlen('unique:')));
        $table = $parts[0] ?? null;
        $column = $parts[1] ?? 'id';

        if ($table === null) {
            return null;
        }

        $base = Str::slug($this->faker->unique()->sentence(3));

        if ($column === 'email') {
            return $this->faker->unique()->safeEmail();
        }

        if ($column === 'uuid') {
            return $this->faker->uuid();
        }

        return $base . '-' . Str::random(6);
    }

    /**
     * Best effort attempt to locate an Eloquent model for a given table.
     *
     * @return class-string<Model>|null
     */
    private function guessModelForTable(string $table): ?string
    {
        $guessed = 'App\\Models\\' . Str::studly(Str::singular($table));

        if (class_exists($guessed) && is_subclass_of($guessed, Model::class)) {
            return $guessed;
        }

        return null;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function createModelInstance(string $modelClass): ?Model
    {
        if (! class_exists($modelClass)) {
            return null;
        }

        $model = new $modelClass;

        if (method_exists($modelClass, 'factory')) {
            try {
                /** @var ModelFactory $factory */
                $factory = $modelClass::factory();

                return $factory->create();
            } catch (FactoryNotFoundException) {
                // continue with database lookup.
            }
        }

        return $model->newQuery()->first();
    }
}
