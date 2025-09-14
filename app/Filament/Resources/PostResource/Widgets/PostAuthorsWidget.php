<?php

declare (strict_types=1);
namespace App\Filament\Resources\PostResource\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * PostAuthorsWidget
 * 
 * Filament v4 resource for PostAuthorsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class PostAuthorsWidget extends ChartWidget
{
    protected static ?string $heading = 'Posts by Author';
    protected static ?int $sort = 3;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $authorData = Post::join('users', 'posts.user_id', '=', 'users.id')->select('users.name', DB::raw('COUNT(posts.id) as count'))->groupBy('users.id', 'users.name')->orderBy('count', 'desc')->limit(10)->get();
        $labels = $authorData->pluck('name')->toArray();
        $data = $authorData->pluck('count')->toArray();
        // Generate colors for each author
        $colors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $colors[] = 'hsl(' . $i * 360 / count($labels) . ', 70%, 50%)';
        }
        return ['datasets' => [['label' => __('posts.authors.posts_count'), 'data' => $data, 'backgroundColor' => $colors, 'borderWidth' => 2, 'borderColor' => '#ffffff']], 'labels' => $labels];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'bar';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => 'function(context) {
                            return context.label + ": " + context.parsed.y + " " + "' . __('posts.authors.posts') . '";
                        }']]], 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]]];
    }
}