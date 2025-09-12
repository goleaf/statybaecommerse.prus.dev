<?php declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportResourceTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_model(): void
    {
        $this->assertEquals(Report::class, ReportResource::getModel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_navigation_icon(): void
    {
        $this->assertEquals('heroicon-o-document-chart-bar', ReportResource::getNavigationIcon());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_navigation_group(): void
    {
        $this->assertEquals(__('navigation.groups.analytics'), ReportResource::getNavigationGroup());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_navigation_label(): void
    {
        $this->assertEquals(__('admin.navigation.reports'), ReportResource::getNavigationLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_model_label(): void
    {
        $this->assertEquals(__('admin.models.report'), ReportResource::getModelLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_plural_model_label(): void
    {
        $this->assertEquals(__('admin.models.reports'), ReportResource::getPluralModelLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_pages(): void
    {
        $pages = ReportResource::getPages();
        
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
        
        $this->assertStringContainsString('/reports', $pages['index']);
        $this->assertStringContainsString('/reports/create', $pages['create']);
        $this->assertStringContainsString('/reports/{record}/edit', $pages['edit']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_form_schema(): void
    {
        $schema = ReportResource::form(new \Filament\Schemas\Schema());
        
        $this->assertInstanceOf(\Filament\Schemas\Schema::class, $schema);
        
        $components = $schema->getComponents();
        $this->assertCount(1, $components);
        
        $section = $components[0];
        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals(__('admin.reports.sales_report'), $section->getHeading());
        
        $sectionComponents = $section->getChildComponents();
        $this->assertCount(8, $sectionComponents);
        
        // Test form components
        $this->assertInstanceOf(TextInput::class, $sectionComponents[0]);
        $this->assertEquals('name', $sectionComponents[0]->getName());
        
        $this->assertInstanceOf(Select::class, $sectionComponents[1]);
        $this->assertEquals('type', $sectionComponents[1]->getName());
        
        $this->assertInstanceOf(Select::class, $sectionComponents[2]);
        $this->assertEquals('date_range', $sectionComponents[2]->getName());
        
        $this->assertInstanceOf(Grid::class, $sectionComponents[3]);
        
        $this->assertInstanceOf(KeyValue::class, $sectionComponents[4]);
        $this->assertEquals('filters', $sectionComponents[4]->getName());
        
        $this->assertInstanceOf(Textarea::class, $sectionComponents[5]);
        $this->assertEquals('description', $sectionComponents[5]->getName());
        
        $this->assertInstanceOf(Toggle::class, $sectionComponents[6]);
        $this->assertEquals('is_active', $sectionComponents[6]->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_table_schema(): void
    {
        $table = ReportResource::table(new Table('test'));
        
        $this->assertInstanceOf(Table::class, $table);
        
        $columns = $table->getColumns();
        $this->assertCount(5, $columns);
        
        // Test table columns
        $this->assertInstanceOf(TextColumn::class, $columns[0]);
        $this->assertEquals('name', $columns[0]->getName());
        
        $this->assertInstanceOf(TextColumn::class, $columns[1]);
        $this->assertEquals('type', $columns[1]->getName());
        
        $this->assertInstanceOf(TextColumn::class, $columns[2]);
        $this->assertEquals('date_range', $columns[2]->getName());
        
        $this->assertInstanceOf(IconColumn::class, $columns[3]);
        $this->assertEquals('is_active', $columns[3]->getName());
        
        $this->assertInstanceOf(TextColumn::class, $columns[4]);
        $this->assertEquals('created_at', $columns[4]->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_table_filters(): void
    {
        $table = ReportResource::table(new Table('test'));
        
        $filters = $table->getFilters();
        $this->assertCount(2, $filters);
        
        // Test table filters
        $this->assertInstanceOf(SelectFilter::class, $filters[0]);
        $this->assertEquals('type', $filters[0]->getName());
        
        $this->assertInstanceOf(TernaryFilter::class, $filters[1]);
        $this->assertEquals('is_active', $filters[1]->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_table_actions(): void
    {
        $table = ReportResource::table(new Table('test'));
        
        $actions = $table->getActions();
        $this->assertCount(2, $actions);
        
        // Test table actions
        $this->assertInstanceOf(EditAction::class, $actions[0]);
        $this->assertInstanceOf(DeleteAction::class, $actions[1]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_table_bulk_actions(): void
    {
        $table = ReportResource::table(new Table('test'));
        
        $bulkActions = $table->getBulkActions();
        $this->assertCount(1, $bulkActions);
        
        // Test bulk actions
        $this->assertInstanceOf(BulkActionGroup::class, $bulkActions[0]);
        
        $bulkActionGroup = $bulkActions[0];
        $bulkActionGroupActions = $bulkActionGroup->getActions();
        $this->assertCount(1, $bulkActionGroupActions);
        
        $this->assertInstanceOf(DeleteBulkAction::class, $bulkActionGroupActions[0]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_edit_action_without_errors(): void
    {
        // Test that EditAction can be instantiated without errors
        $editAction = EditAction::make();
        
        $this->assertInstanceOf(EditAction::class, $editAction);
        $this->assertEquals('edit', $editAction->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_delete_action_without_errors(): void
    {
        // Test that DeleteAction can be instantiated without errors
        $deleteAction = DeleteAction::make();
        
        $this->assertInstanceOf(DeleteAction::class, $deleteAction);
        $this->assertEquals('delete', $deleteAction->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_bulk_action_group_without_errors(): void
    {
        // Test that BulkActionGroup can be instantiated without errors
        $bulkActionGroup = BulkActionGroup::make([
            DeleteBulkAction::make(),
        ]);
        
        $this->assertInstanceOf(BulkActionGroup::class, $bulkActionGroup);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_delete_bulk_action_without_errors(): void
    {
        // Test that DeleteBulkAction can be instantiated without errors
        $deleteBulkAction = DeleteBulkAction::make();
        
        $this->assertInstanceOf(DeleteBulkAction::class, $deleteBulkAction);
        $this->assertEquals('delete', $deleteBulkAction->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_select_filter_without_errors(): void
    {
        // Test that SelectFilter can be instantiated without errors
        $selectFilter = SelectFilter::make('type')
            ->options([
                'sales' => 'Sales Report',
                'customers' => 'Customer Analysis',
            ]);
        
        $this->assertInstanceOf(SelectFilter::class, $selectFilter);
        $this->assertEquals('type', $selectFilter->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_ternary_filter_without_errors(): void
    {
        // Test that TernaryFilter can be instantiated without errors
        $ternaryFilter = TernaryFilter::make('is_active');
        
        $this->assertInstanceOf(TernaryFilter::class, $ternaryFilter);
        $this->assertEquals('is_active', $ternaryFilter->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_text_column_without_errors(): void
    {
        // Test that TextColumn can be instantiated without errors
        $textColumn = TextColumn::make('name')
            ->searchable()
            ->sortable();
        
        $this->assertInstanceOf(TextColumn::class, $textColumn);
        $this->assertEquals('name', $textColumn->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_icon_column_without_errors(): void
    {
        // Test that IconColumn can be instantiated without errors
        $iconColumn = IconColumn::make('is_active')
            ->boolean();
        
        $this->assertInstanceOf(IconColumn::class, $iconColumn);
        $this->assertEquals('is_active', $iconColumn->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_form_components_without_errors(): void
    {
        // Test that form components can be instantiated without errors
        $textInput = TextInput::make('name')
            ->required()
            ->maxLength(255);
        
        $this->assertInstanceOf(TextInput::class, $textInput);
        $this->assertEquals('name', $textInput->getName());
        
        $select = Select::make('type')
            ->options([
                'sales' => 'Sales Report',
                'customers' => 'Customer Analysis',
            ])
            ->required();
        
        $this->assertInstanceOf(Select::class, $select);
        $this->assertEquals('type', $select->getName());
        
        $toggle = Toggle::make('is_active')
            ->default(true);
        
        $this->assertInstanceOf(Toggle::class, $toggle);
        $this->assertEquals('is_active', $toggle->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_validation_rules(): void
    {
        $schema = ReportResource::form(new \Filament\Schemas\Schema());
        $components = $schema->getComponents();
        $section = $components[0];
        $sectionComponents = $section->getChildComponents();
        
        // Test name field validation
        $nameField = $sectionComponents[0];
        $this->assertTrue($nameField->isRequired());
        $this->assertEquals(255, $nameField->getMaxLength());
        
        // Test type field validation
        $typeField = $sectionComponents[1];
        $this->assertTrue($typeField->isRequired());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_column_configurations(): void
    {
        $table = ReportResource::table(new Table('test'));
        $columns = $table->getColumns();
        
        // Test name column configuration
        $nameColumn = $columns[0];
        $this->assertTrue($nameColumn->isSearchable());
        $this->assertTrue($nameColumn->isSortable());
        
        // Test type column configuration
        $typeColumn = $columns[1];
        $this->assertTrue($typeColumn->isSortable());
        
        // Test date_range column configuration
        $dateRangeColumn = $columns[2];
        $this->assertTrue($dateRangeColumn->isSortable());
        
        // Test created_at column configuration
        $createdAtColumn = $columns[4];
        $this->assertTrue($createdAtColumn->isSortable());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_field_options(): void
    {
        $schema = ReportResource::form(new \Filament\Schemas\Schema());
        $components = $schema->getComponents();
        $section = $components[0];
        $sectionComponents = $section->getChildComponents();
        
        // Test type field options
        $typeField = $sectionComponents[1];
        $typeOptions = $typeField->getOptions();
        
        $expectedTypeOptions = [
            'sales' => __('admin.reports.sales_report'),
            'products' => __('admin.reports.product_performance'),
            'customers' => __('admin.reports.customer_analysis'),
            'inventory' => __('admin.reports.inventory_report'),
        ];
        
        $this->assertEquals($expectedTypeOptions, $typeOptions);
        
        // Test date_range field options
        $dateRangeField = $sectionComponents[2];
        $dateRangeOptions = $dateRangeField->getOptions();
        
        $expectedDateRangeOptions = [
            'today' => __('admin.date_ranges.today'),
            'yesterday' => __('admin.date_ranges.yesterday'),
            'last_7_days' => __('admin.date_ranges.last_7_days'),
            'last_30_days' => __('admin.date_ranges.last_30_days'),
            'last_90_days' => __('admin.date_ranges.last_90_days'),
            'this_year' => __('admin.date_ranges.this_year'),
            'custom' => __('admin.date_ranges.custom'),
        ];
        
        $this->assertEquals($expectedDateRangeOptions, $dateRangeOptions);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_filter_options(): void
    {
        $table = ReportResource::table(new Table('test'));
        $filters = $table->getFilters();
        
        // Test type filter options
        $typeFilter = $filters[0];
        $typeFilterOptions = $typeFilter->getOptions();
        
        $expectedTypeFilterOptions = [
            'sales' => __('admin.reports.sales_report'),
            'products' => __('admin.reports.product_performance'),
            'customers' => __('admin.reports.customer_analysis'),
            'inventory' => __('admin.reports.inventory_report'),
        ];
        
        $this->assertEquals($expectedTypeFilterOptions, $typeFilterOptions);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_grid_configuration(): void
    {
        $schema = ReportResource::form(new \Filament\Schemas\Schema());
        $components = $schema->getComponents();
        $section = $components[0];
        $sectionComponents = $section->getChildComponents();
        
        // Test section columns configuration
        $this->assertEquals(2, $section->getColumns());
        
        // Test grid configuration
        $grid = $sectionComponents[3];
        $this->assertInstanceOf(Grid::class, $grid);
        $this->assertEquals(2, $grid->getColumns());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_field_defaults(): void
    {
        $schema = ReportResource::form(new \Filament\Schemas\Schema());
        $components = $schema->getComponents();
        $section = $components[0];
        $sectionComponents = $section->getChildComponents();
        
        // Test is_active field default
        $isActiveField = $sectionComponents[6];
        $this->assertTrue($isActiveField->getDefaultState());
        
        // Test date_range field native configuration
        $dateRangeField = $sectionComponents[2];
        $this->assertFalse($dateRangeField->isNative());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_column_labels(): void
    {
        $table = ReportResource::table(new Table('test'));
        $columns = $table->getColumns();
        
        // Test column labels
        $this->assertEquals(__('admin.form_name'), $columns[0]->getLabel());
        $this->assertEquals(__('admin.fields.report_type'), $columns[1]->getLabel());
        $this->assertEquals(__('admin.fields.date_range'), $columns[2]->getLabel());
        $this->assertEquals(__('admin.products.status.active'), $columns[3]->getLabel());
        $this->assertEquals(__('admin.fields.created_at'), $columns[4]->getLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_filter_labels(): void
    {
        $table = ReportResource::table(new Table('test'));
        $filters = $table->getFilters();
        
        // Test filter labels
        $this->assertEquals(__('admin.fields.report_type'), $filters[0]->getLabel());
        $this->assertEquals(__('admin.products.status.active'), $filters[1]->getLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_field_labels(): void
    {
        $schema = ReportResource::form(new \Filament\Schemas\Schema());
        $components = $schema->getComponents();
        $section = $components[0];
        $sectionComponents = $section->getChildComponents();
        
        // Test form field labels
        $this->assertEquals(__('admin.form_name'), $sectionComponents[0]->getLabel());
        $this->assertEquals(__('admin.fields.report_type'), $sectionComponents[1]->getLabel());
        $this->assertEquals(__('admin.fields.date_range'), $sectionComponents[2]->getLabel());
        $this->assertEquals(__('admin.filament.table.filters'), $sectionComponents[4]->getLabel());
        $this->assertEquals(__('admin.form_description'), $sectionComponents[5]->getLabel());
        $this->assertEquals(__('admin.products.status.active'), $sectionComponents[6]->getLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_column_formats(): void
    {
        $table = ReportResource::table(new Table('test'));
        $columns = $table->getColumns();
        
        // Test type column badge format
        $typeColumn = $columns[1];
        $this->assertTrue($typeColumn->isBadge());
        
        // Test is_active column boolean format
        $isActiveColumn = $columns[3];
        $this->assertTrue($isActiveColumn->isBoolean());
        
        // Test created_at column dateTime format
        $createdAtColumn = $columns[4];
        $this->assertTrue($createdAtColumn->isDateTime());
        $this->assertTrue($createdAtColumn->isSince());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_field_span_configuration(): void
    {
        $schema = ReportResource::form(new \Filament\Schemas\Schema());
        $components = $schema->getComponents();
        $section = $components[0];
        $sectionComponents = $section->getChildComponents();
        
        // Test description field column span
        $descriptionField = $sectionComponents[5];
        $this->assertEquals('full', $descriptionField->getColumnSpan());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_resource_instantiation(): void
    {
        // Test that ReportResource can be instantiated without errors
        $resource = new ReportResource();
        
        $this->assertInstanceOf(ReportResource::class, $resource);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_static_methods(): void
    {
        // Test that all static methods work without errors
        $this->assertIsString(ReportResource::getModel());
        $this->assertIsString(ReportResource::getNavigationIcon());
        $this->assertIsString(ReportResource::getNavigationGroup());
        $this->assertIsString(ReportResource::getNavigationLabel());
        $this->assertIsString(ReportResource::getModelLabel());
        $this->assertIsString(ReportResource::getPluralModelLabel());
        $this->assertIsArray(ReportResource::getPages());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_schema_instantiation(): void
    {
        // Test that form schema can be created without errors
        $schema = new \Filament\Schemas\Schema();
        $formSchema = ReportResource::form($schema);
        
        $this->assertInstanceOf(\Filament\Schemas\Schema::class, $formSchema);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_schema_instantiation(): void
    {
        // Test that table schema can be created without errors
        $table = new Table('test');
        $tableSchema = ReportResource::table($table);
        
        $this->assertInstanceOf(Table::class, $tableSchema);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_import(): void
    {
        // Test that EditAction class can be imported and used
        $this->assertTrue(class_exists(EditAction::class));
        $this->assertTrue(class_exists(DeleteAction::class));
        $this->assertTrue(class_exists(BulkActionGroup::class));
        $this->assertTrue(class_exists(DeleteBulkAction::class));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_component_imports(): void
    {
        // Test that all table components can be imported and used
        $this->assertTrue(class_exists(TextColumn::class));
        $this->assertTrue(class_exists(IconColumn::class));
        $this->assertTrue(class_exists(SelectFilter::class));
        $this->assertTrue(class_exists(TernaryFilter::class));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_component_imports(): void
    {
        // Test that all form components can be imported and used
        $this->assertTrue(class_exists(TextInput::class));
        $this->assertTrue(class_exists(Select::class));
        $this->assertTrue(class_exists(Toggle::class));
        $this->assertTrue(class_exists(Textarea::class));
        $this->assertTrue(class_exists(KeyValue::class));
        $this->assertTrue(class_exists(Grid::class));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_schema_component_imports(): void
    {
        // Test that schema components can be imported and used
        $this->assertTrue(class_exists(Section::class));
    }
}
