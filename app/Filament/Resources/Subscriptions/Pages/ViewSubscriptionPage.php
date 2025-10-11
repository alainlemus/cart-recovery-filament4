<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Cancel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Can;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Filament\Notifications\Notification;

class ViewSubscriptionPage extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    public string $name;
    public string $email;
    public string $product_name;
    public string $price;
    public string $next_payment_date;
    public string $card_last_four;

    protected function getHeaderActions(): array
    {
        return [
            //EditAction::make(),
            Action::make('cancel')
                ->label('Cancel Subscription')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->action(function ($record) {
                    Stripe::setApiKey(config('cashier.secret'));
                    if ($record->stripe_id) {
                        try {
                            StripeSubscription::update($record->stripe_id, [
                                'cancel_at_period_end' => true,
                            ]);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error to cancel in Stripe')
                                 //->title('Error al cancelar en Stripe') --- IGNORE ---
                                ->danger()
                                ->send();
                        }
                    }
                    $record->stripe_status = 'inactive';
                    $record->save();
                    Notification::make()
                        ->title('Subscription cancelled')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->where('stripe_status', 'active')->latest()->first();
        $product = $subscription?->product()->first();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->product_name = $product?->name ?? 'N/A';
        $this->price = $product?->price ?? 'N/A';
        $this->next_payment_date = $subscription?->ends_at ? $subscription->ends_at->toDateString() : 'N/A';
        $this->card_last_four = $subscription?->card_last_four ?? 'N/A';

        return $schema
            ->components([
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
