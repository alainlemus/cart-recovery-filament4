<?php

namespace App\Filament\Resources\Configuration\Pages;

use App\Filament\Resources\Configuration\ConfigurationResource;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Auth;

class ViewConfiguration extends Page
{

    public static string $resource = ConfigurationResource::class;

    public string $name;
    public string $email;
    public string $card;
    public string $subscription;

    public function mount()
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->latest()->first();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->card = $subscription?->card_last_four ?? 'N/A';
        $this->subscription = $subscription?->name ?? 'N/A';
    }

    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextInput::make('name')->label('Name')->default($this->name)->disabled(),
                    TextInput::make('email')->label('Email')->default($this->email)->disabled(),
                    TextInput::make('card')->label('Card Last 4')->default($this->card)->disabled(),
                    TextInput::make('subscription')->label('Subscription Type')->default($this->subscription)->disabled(),
                ])
                ->columns(2),
        ]);
    }

}
