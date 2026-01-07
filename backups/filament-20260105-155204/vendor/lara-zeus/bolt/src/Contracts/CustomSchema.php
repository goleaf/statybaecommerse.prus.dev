<?php

namespace LaraZeus\Bolt\Contracts;

use LaraZeus\Accordion\Forms\Accordion;
use LaraZeus\Bolt\Fields\FieldsContract;

interface CustomSchema
{
    public function make(?FieldsContract $field = null): Accordion;

    public function hidden(?FieldsContract $field = null): array;
}
