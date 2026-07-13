<?php

declare(strict_types = 1);

namespace App\Filament\Auth;

use App\Events\UserRegistered;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\{Select, TextInput};
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{App, Cookie};

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getLocaleFormComponent(),
            ]);
    }

    protected function getNameFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getNameFormComponent();

        return $component
            ->label(fn (): string => __('filament-panels::auth/pages/register.form.name.label'));
    }

    protected function getEmailFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getEmailFormComponent();

        return $component
            ->label(fn (): string => __('filament-panels::auth/pages/register.form.email.label'));
    }

    protected function getPasswordFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getPasswordFormComponent();

        return $component
            ->label(fn (): string => __('filament-panels::auth/pages/register.form.password.label'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getPasswordConfirmationFormComponent();

        return $component
            ->label(fn (): string => __('filament-panels::auth/pages/register.form.password_confirmation.label'));
    }

    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()
            ->label(fn (): string => __('filament-panels::auth/pages/register.form.actions.register.label'));
    }

    protected function getLocaleFormComponent(): Select
    {
        return Select::make('locale')
            ->label(fn (): string => __('profile.locale'))
            ->options(fn (): array => [
                'en'    => __('profile.locales.en'),
                'pt_BR' => __('profile.locales.pt_BR'),
            ])
            ->default(app()->getLocale())
            ->selectablePlaceholder(false)
            ->required()
            ->live()
            ->afterStateUpdated(function (?string $state): void {
                if ($state === null) {
                    return;
                }

                App::setLocale($state);
                Cookie::queue(cookie()->forever('locale', $state));
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        unset($data['locale']);

        $user = parent::handleRegistration($data);

        if ($user instanceof User) {
            UserRegistered::dispatch($user);
        }

        return $user;
    }
}
