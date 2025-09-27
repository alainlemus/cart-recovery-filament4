<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Models\Subscription;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewSubscription extends Page
{
    public static string $resource = SubscriptionResource::class;

    public string $name;
    public string $email;
    public string $product_name;
    public string $price;
    public string $next_payment_date;
    public string $card_last_four;

    public function mount(): void
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->latest()->first();
        $product = $subscription?->product()->first();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->product_name = $product?->name ?? 'N/A';
        $this->price = $product?->price ?? 'N/A';
        $this->next_payment_date = $subscription?->ends_at ? $subscription->ends_at->toDateString() : 'N/A';
        $this->card_last_four = $subscription?->card_last_four ?? 'N/A';
    }

    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextInput::make('name')->label('Name')->default($this->name)->disabled(),
                    TextInput::make('email')->label('Email')->default($this->email)->disabled(),
                    TextInput::make('card_last_four')->label('Card Last 4')->default($this->card_last_four)->disabled(),
                    TextInput::make('product_name')->label('Product Name')->default($this->product_name)->disabled(),
                    TextInput::make('price')->label('Price')->default($this->price)->disabled(),
                    TextInput::make('next_payment_date')->label('Next Payment Date')->default($this->next_payment_date)->disabled(),
                ])
                ->columns(2),
        ]);
    }
}
