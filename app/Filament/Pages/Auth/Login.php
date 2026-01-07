<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
// use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Pages\Page as BaseLogin;

final class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->extraInputAttributes(['name' => 'data[email]'], merge: true);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->extraInputAttributes(['name' => 'data[password]'], merge: true);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->extraInputAttributes(['name' => 'data[remember]', 'value' => '1'], merge: true);
    }
}
