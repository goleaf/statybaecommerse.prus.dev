<?php declare(strict_types=1);

namespace App\Livewire\Cpanel\Reviews;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Review;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractPageComponent implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Review::query()->with(['author', 'reviewrateable'])
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('author.full_name')
                    ->label(__('Author'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('reviewrateable.name')
                    ->label(__('Product'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('Content'))
                    ->toggleable()
                    ->limit(120)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label(__('Rating'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('Language'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_recommended')
                    ->label(__('Recommended')),
                Tables\Columns\ToggleColumn::make('approved')
                    ->label(__('Approved')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('id')
                    ->form([
                        Forms\Components\TextInput::make('id'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['id'] ?? null, fn($q, $id) => $q->where('id', $id));
                    }),
                Tables\Filters\Filter::make('product')
                    ->form([
                        Forms\Components\TextInput::make('product_name')->label(__('Product name')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['product_name'] ?? null, function ($q, $name) {
                            $q->whereHas('reviewrateable', fn($qq) => $qq->where('name', 'like', "%{$name}%"));
                        });
                    }),
                Tables\Filters\Filter::make('author')
                    ->form([
                        Forms\Components\TextInput::make('author_name')->label(__('Author name')),
                        Forms\Components\TextInput::make('author_id')->numeric()->label('Author ID'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query->when($data['author_id'] ?? null, fn($q, $id) => $q->where('author_id', $id));
                        $query->when($data['author_name'] ?? null, function ($q, $name) {
                            $q->whereHas('author', fn($qq) => $qq->where('full_name', 'like', "%{$name}%"));
                        });
                        return $query;
                    }),
                Tables\Filters\Filter::make('title')
                    ->form([
                        Forms\Components\TextInput::make('title_contains')->label(__('Title contains')),
                    ])
                    ->query(fn(Builder $query, array $data) => $query->when($data['title_contains'] ?? null, fn($qq, $v) => $qq->where('title', 'like', "%{$v}%"))),
                Tables\Filters\Filter::make('content')
                    ->form([
                        Forms\Components\TextInput::make('content_contains')->label(__('Content contains')),
                    ])
                    ->query(fn(Builder $query, array $data) => $query->when($data['content_contains'] ?? null, fn($qq, $v) => $qq->where('content', 'like', "%{$v}%"))),
                Tables\Filters\Filter::make('rating')
                    ->form([
                        Forms\Components\TextInput::make('min')->numeric()->label(__('Min rating')),
                        Forms\Components\TextInput::make('max')->numeric()->label(__('Max rating')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query->when($data['min'] ?? null, fn($qq, $v) => $qq->where('rating', '>=', (int) $v));
                        $query->when($data['max'] ?? null, fn($qq, $v) => $qq->where('rating', '<=', (int) $v));
                        return $query;
                    }),
                Tables\Filters\TernaryFilter::make('approved')->label(__('Approved')),
                Tables\Filters\TernaryFilter::make('is_recommended')->label(__('Recommended')),
                Tables\Filters\Filter::make('locale')
                    ->form([
                        Forms\Components\Select::make('locale')
                            ->label(__('Language'))
                            ->options(function (): array {
                                $list = array_map('trim', explode(',', (string) config('app.supported_locales', 'en')));
                                return array_combine($list, $list);
                            })
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['locale'] ?? null, fn($qq, $v) => $qq->where('locale', $v));
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('From')),
                        Forms\Components\DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query->when($data['from'] ?? null, fn($qq, $v) => $qq->whereDate('created_at', '>=', $v));
                        $query->when($data['until'] ?? null, fn($qq, $v) => $qq->whereDate('created_at', '<=', $v));
                        return $query;
                    }),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns([
                'sm' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve')
                    ->label(__('Approve selected'))
                    ->icon('untitledui-check')
                    ->color('success')
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            $record->update(['approved' => true]);
                        }
                    }),
                Tables\Actions\BulkAction::make('reject')
                    ->label(__('Reject selected'))
                    ->icon('untitledui-x')
                    ->color('danger')
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            $record->update(['approved' => false]);
                        }
                    }),
                Tables\Actions\BulkAction::make('toggle_recommended')
                    ->label(__('Toggle recommended'))
                    ->icon('untitledui-star-07')
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            $record->update(['is_recommended' => !(bool) $record->is_recommended]);
                        }
                    }),
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('untitledui-edit-02')
                    ->form([
                        Forms\Components\TextInput::make('title')->required()->minLength(3)->maxLength(150),
                        Forms\Components\Textarea::make('content')->rows(6)->required()->minLength(3)->maxLength(2000),
                        Forms\Components\TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->required(),
                        Forms\Components\Toggle::make('approved'),
                        Forms\Components\Toggle::make('is_recommended'),
                    ])
                    ->action(function (Review $record, array $data): void {
                        $record->update([
                            'title' => $data['title'],
                            'content' => $data['content'],
                            'rating' => (int) $data['rating'],
                            'approved' => (bool) ($data['approved'] ?? false),
                            'is_recommended' => (bool) ($data['is_recommended'] ?? false),
                        ]);
                    }),
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('untitledui-eye')
                    ->action(fn(Review $record) => $this->dispatch('openPanel', component: 'shopper-slide-overs.review-detail', arguments: ['review' => $record])),
                Tables\Actions\DeleteAction::make('delete'),
            ]);
    }

    public function render(): View
    {
        return view('livewire.cpanel.reviews.index')
            ->title(__('Reviews'));
    }
}
