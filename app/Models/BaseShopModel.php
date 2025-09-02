<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseShopModel extends Model
{
    protected array $guarded = [];

    protected string $shopTable = '';

    public function getTable(): string
    {
        if ($this->shopTable !== '') {
            return shopper_table($this->shopTable);
        }

        return parent::getTable();
    }
}


