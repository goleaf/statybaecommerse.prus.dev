<?php

declare (strict_types=1);
namespace App\Filament\Resources\SubscriberResource\Widgets;

use App\Models\Subscriber;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * SubscriberGrowthWidget
 * 
 * Filament v4 resource for SubscriberGrowthWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|string|array $columnSpan
 * @property string|null $maxHeight
 * @property string|null $filter
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class SubscriberGrowthWidget extends ChartWidget
{
    protected static ?string $heading = 'Subscriber Growth (Last 30 Days)';
    protected int|string|array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    public ?string $filter = '30';
    /**
     * Handle getFilters functionality with proper error handling.
     * @return array|null
     */
    protected function getFilters(): ?array
    {
        return ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days'];
    }
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $days = (int) $this->filter;
        $startDate = now()->subDays($days)->startOfDay();
        $subscribers = Subscriber::select(DB::raw('DATE(subscribed_at) as date'), DB::raw('COUNT(*) as count'))->where('subscribed_at', '>=', $startDate)->groupBy('date')->orderBy('date')->get()->keyBy('date');
        $unsubscribed = Subscriber::select(DB::raw('DATE(unsubscribed_at) as date'), DB::raw('COUNT(*) as count'))->where('unsubscribed_at', '>=', $startDate)->groupBy('date')->orderBy('date')->get()->keyBy('date');
        $dates = [];
        $subscriberData = [];
        $unsubscribedData = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dates[] = $startDate->copy()->addDays($i)->format('M j');
            $subscriberData[] = $subscribers->get($date)?->count ?? 0;
            $unsubscribedData[] = $unsubscribed->get($date)?->count ?? 0;
        }
        return ['datasets' => [['label' => 'New Subscribers', 'data' => $subscriberData, 'borderColor' => 'rgb(59, 130, 246)', 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'fill' => true], ['label' => 'Unsubscribed', 'data' => $unsubscribedData, 'borderColor' => 'rgb(239, 68, 68)', 'backgroundColor' => 'rgba(239, 68, 68, 0.1)', 'fill' => true]], 'labels' => $dates];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'line';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['display' => true, 'position' => 'top']], 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]]];
    }
}