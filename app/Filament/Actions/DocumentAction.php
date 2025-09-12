<?php declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

final class DocumentAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'generate_document';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('admin.actions.generate_document'))
            ->icon('heroicon-o-document-text')
            ->color('info')
            ->form([
                Select::make('template_id')
                    ->label(__('admin.documents.fields.template'))
                    ->options(DocumentTemplate::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $template = DocumentTemplate::find($state);
                            if ($template) {
                                $set('title', $template->name);
                                $set('description', $template->description);
                            }
                        }
                    }),
                
                TextInput::make('title')
                    ->label(__('admin.documents.fields.title'))
                    ->required()
                    ->maxLength(255),
                
                Textarea::make('description')
                    ->label(__('admin.documents.fields.description'))
                    ->maxLength(1000)
                    ->rows(3),
                
                Select::make('format')
                    ->label(__('admin.documents.fields.format'))
                    ->options([
                        'html' => __('admin.documents.formats.html'),
                        'pdf' => __('admin.documents.formats.pdf'),
                    ])
                    ->default('pdf')
                    ->required(),
                
                Toggle::make('is_public')
                    ->label(__('admin.documents.fields.is_public'))
                    ->default(false),
            ])
            ->action(function (array $data, Model $record): void {
                try {
                    $documentService = app(DocumentService::class);
                    
                    // Prepare variables for the document
                    $variables = $this->prepareDocumentVariables($record);
                    
                    // Generate the document
                    $document = $documentService->generateDocument(
                        templateId: $data['template_id'],
                        documentable: $record,
                        variables: $variables,
                        title: $data['title'],
                        description: $data['description'] ?? null,
                        format: $data['format'],
                        isPublic: $data['is_public'] ?? false
                    );
                    
                    Notification::make()
                        ->title(__('admin.documents.generated_successfully'))
                        ->body(__('admin.documents.generated_successfully_description', ['title' => $document->title]))
                        ->success()
                        ->send();
                        
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('admin.documents.generation_failed'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    private function prepareDocumentVariables(Model $record): array
    {
        $variables = [];
        
        // Basic customer information
        $variables['CUSTOMER_NAME'] = $record->name ?? '';
        $variables['CUSTOMER_EMAIL'] = $record->email ?? '';
        $variables['CUSTOMER_PHONE'] = $record->phone_number ?? '';
        $variables['CUSTOMER_FIRST_NAME'] = $record->first_name ?? '';
        $variables['CUSTOMER_LAST_NAME'] = $record->last_name ?? '';
        $variables['CUSTOMER_COMPANY'] = $record->company ?? '';
        $variables['CUSTOMER_POSITION'] = $record->position ?? '';
        
        // Customer statistics
        $variables['CUSTOMER_TOTAL_SPENT'] = '€' . number_format($record->total_spent ?? 0, 2);
        $variables['CUSTOMER_ORDERS_COUNT'] = $record->orders_count ?? 0;
        $variables['CUSTOMER_AVERAGE_ORDER_VALUE'] = '€' . number_format($record->average_order_value ?? 0, 2);
        $variables['CUSTOMER_REVIEWS_COUNT'] = $record->reviews_count ?? 0;
        $variables['CUSTOMER_LAST_ORDER_DATE'] = $record->last_order_date ?? __('admin.customers.no_orders');
        $variables['CUSTOMER_LAST_LOGIN'] = $record->last_login_at?->format('d/m/Y H:i') ?? __('admin.customers.never_logged_in');
        
        // Customer groups
        $customerGroups = $record->customerGroups ?? collect();
        $variables['CUSTOMER_GROUPS'] = $customerGroups->pluck('name')->join(', ');
        $variables['CUSTOMER_GROUP_NAMES'] = $customerGroups->pluck('name')->join(', ');
        
        // Addresses
        $defaultAddress = $record->default_address;
        if ($defaultAddress) {
            $variables['CUSTOMER_ADDRESS'] = $defaultAddress->address_line_1 . ', ' . $defaultAddress->city . ', ' . $defaultAddress->postal_code . ', ' . $defaultAddress->country;
            $variables['CUSTOMER_CITY'] = $defaultAddress->city ?? '';
            $variables['CUSTOMER_POSTAL_CODE'] = $defaultAddress->postal_code ?? '';
            $variables['CUSTOMER_COUNTRY'] = $defaultAddress->country ?? '';
        }
        
        // Dates
        $variables['CURRENT_DATE'] = now()->format('d/m/Y');
        $variables['CURRENT_TIME'] = now()->format('H:i');
        $variables['CURRENT_DATETIME'] = now()->format('d/m/Y H:i');
        $variables['CUSTOMER_REGISTRATION_DATE'] = $record->created_at?->format('d/m/Y') ?? '';
        
        // Company information (from settings or config)
        $variables['COMPANY_NAME'] = config('app.name', 'Our Company');
        $variables['COMPANY_EMAIL'] = config('mail.from.address', 'info@company.com');
        $variables['COMPANY_PHONE'] = config('app.phone', '');
        $variables['COMPANY_ADDRESS'] = config('app.address', '');
        $variables['COMPANY_WEBSITE'] = config('app.url', '');
        
        return $variables;
    }
}
