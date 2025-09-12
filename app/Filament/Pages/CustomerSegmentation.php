<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
final class