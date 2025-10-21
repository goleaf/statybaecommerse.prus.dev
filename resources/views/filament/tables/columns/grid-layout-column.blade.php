@php
    use Filament\Tables\Columns\Column;
    use Filament\Tables\Columns\ImageColumn;

    /** @var \App\Filament\Tables\Columns\GridLayoutColumn $column */
    $table = $column->getTable();
    $record = $getState();
    $recordKey = $table->getRecordKey($record);
    $rowLoop = $getRowLoop();

    $renderColumn = static function (Column $sourceColumn) use ($table, $record, $recordKey, $rowLoop): ?array {
        $columnClone = clone $sourceColumn;
        $columnClone->table($table);
        $columnClone->record($record);
        $columnClone->recordKey($recordKey);

        if ($rowLoop !== null) {
            $columnClone->rowLoop($rowLoop);
        }

        $columnClone->clearCachedState();

        if ($columnClone->isHidden() || $columnClone->isToggledHidden()) {
            return null;
        }

        $content = $columnClone->toHtmlString();

        if ($content === null) {
            return null;
        }

        $contentHtml = trim((string) $content);

        if ($contentHtml === '') {
            return null;
        }

        return [$columnClone, $contentHtml];
    };

    $columns = $column->getSourceColumns();
    $imageColumns = [];
    $textColumns = [];

    foreach ($columns as $sourceColumn) {
        if ($sourceColumn instanceof ImageColumn) {
            $imageColumns[] = $sourceColumn;

            continue;
        }

        $textColumns[] = $sourceColumn;
    }
@endphp

<div class="flex h-full flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-primary-500 dark:border-gray-700 dark:bg-gray-900">
    @foreach ($imageColumns as $imageColumn)
        @php
            $renderedImage = $renderColumn($imageColumn);
        @endphp

        @if ($renderedImage !== null)
            <?php [$imageClone, $imageHtml] = $renderedImage; ?>

            <div class="flex items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-800">
                {!! $imageHtml !!}
            </div>
        @endif
    @endforeach

    <dl class="flex flex-col gap-3">
        @foreach ($textColumns as $textColumn)
            @php
                $renderedColumn = $renderColumn($textColumn);
            @endphp

            @if ($renderedColumn === null)
                @continue
            @endif

            <?php [$textClone, $columnHtml] = $renderedColumn; ?>

            <div class="flex flex-col gap-1">
                <?php $label = $textClone->getLabel(); ?>

                @if (filled($label))
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                @endif

                <dd class="text-sm text-gray-900 dark:text-gray-100">
                    {!! $columnHtml !!}
                </dd>
            </div>
        @endforeach
    </dl>
</div>
