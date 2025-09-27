<?php

namespace App\Filament\Resources\AbandonedCheckouts\Tables;

use App\Mail\CartRecoveryMail;
use App\services\ShopifyDiscountService;
use App\services\TwilioWhatsAppService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\View;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\URL;

class AbandonedCheckoutsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $hasMultipleShops = $user && $user->shops()->count() > 1;

        return $table
            ->columns([
                // Si el usuario tiene más de una tienda, muestra la columna de tienda
                ...($hasMultipleShops ? [
                    TextColumn::make('shop.name')->label('Store'),
                ] : []),
                TextColumn::make('id_cart')->label('Checkout ID'),
                TextColumn::make('email_client')->label('Email'),
                TextColumn::make('total_price')->label('Total')->money(fn ($record) => $record->currency),
                TextColumn::make('abandoned_at')->dateTime(),
                TextColumn::make('abandoned_checkout_url')
                    ->label('URL de Recuperación')
                    ->formatStateUsing(fn () => 'Open link') // <-- muestra solo el texto "Abrir"
                    ->url(fn ($record) => $record->abandoned_checkout_url) // usa el valor real como enlace
                    ->openUrlInNewTab(),
            ])
            ->defaultGroup($hasMultipleShops ? 'shop.name' : null) // Agrupar por tienda si tiene más de una
            ->paginated(false) // Shopify API ya pagina
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->fillForm(function ($record){
                        $response = is_array($record->response)
                        ? $record->response
                        : json_decode($record->response, true);
                        // datos dentro de response
                        return [
                            'email' => data_get($response, 'email'),
                            'currency' => data_get($response, 'response.currency'),
                            'total_price' => data_get($response, 'total_price'),

                            'customer_state' => data_get($response, 'response.customer.default_address.province_code'),
                            'customer_country' => data_get($response, 'response.customer.default_address.country_name'),
                            'customer_province' => data_get($response, 'response.customer.default_address.province'),
                            'customer_phone' => data_get($response, 'response.phone'),
                            'cart_link' => data_get($response, 'abandoned_checkout_url.'),

                            'product_titles' => collect(data_get($response, 'response.line_items', []))
                                ->pluck('title')
                                ->implode(', '),

                            'response_json' => is_array($response)
                                ? json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : (string) $response,

                            'response_view' => is_array($response)
                                ? json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : (string) $response,

                            'response' => is_array($response)
                                ? json_encode($response['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : (string) $response,
                        ];
                    })
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('currency')
                            ->label('Currency')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('total_price')
                            ->label('Total Price')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('customer_state')
                            ->label('State')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('customer_country')
                            ->label('Country')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('customer_province')
                            ->label('Providence')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('product_titles')
                            ->label('Product')
                            ->readOnly()
                            ->dehydrated(false),

                        Textarea::make('response_json')
                            ->label('Json Response')
                            ->readOnly()
                            ->rows(10)
                            ->dehydrated(false),

                        KeyValue::make('response_view')
                            ->label('Complet Response')
                            //->readOnly()
                            //->rows(10)
                            ->dehydrated(false),

                        KeyValue::make('response')
                            ->label('Response')
                            //->readOnly()
                            //->rows(10)
                            ->dehydrated(false),
                    ]),
                Action::make('Send CartRecovery')
                    ->icon(Heroicon::ShoppingBag)
                    ->mountUsing(function ($record, $form) {
                            $response = is_array($record->response)
                                ? $record->response
                                : json_decode($record->response, true);

                            $user = auth()->user();
                            $shop = $user->shops()->first();
                            $token = $shop->access_token;

                            $form->fill([
                                'phone_number' => data_get($response, 'customer.phone'),
                                'email' => data_get($response, 'email'),
                                'currency' => data_get($response, 'response.customer.currency'),
                                'total_price' => data_get($response, 'total_price'),
                                'customer_state' => data_get($response, 'customer.default_address.province_code'),
                                'customer_country' => data_get($response, 'customer.default_address.country_name'),
                                'customer_province' => data_get($response, 'customer.default_address.province'),
                                'product_titles' => collect(data_get($response, 'line_items', []))
                                    ->pluck('title')
                                    ->implode(', '),
                                'response_json' => json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                                // 🟢 NUEVO: para poder usarlos luego
                                'shop_domain' => $shop->shopify_domain,
                                'access_token' => $token,
                            ]);
                        })
                    ->steps([
                        Step::make('Data cart')
                            ->description('Checkout details')
                            ->schema([
                                TextInput::make('phone_number')
                                    ->label('Phone number')
                                    ->readOnly()
                                     ->visible(function ($get) {
                                        // Validar si phone_number no es null ni vacío
                                        return !is_null($get('phone_number')) && !empty($get('phone_number'));
                                    })
                                    ->dehydrated(false),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->readOnly()
                                    ->dehydrated(false),

                                TextInput::make('currency')
                                    ->label('Currency')
                                    ->readOnly()
                                    ->dehydrated(false),

                                TextInput::make('total_price')
                                    ->label('Total Price')
                                    ->readOnly()
                                    ->dehydrated(false),
                                    ]),

                        Step::make('Message')
                            ->description('Add some message')
                            ->schema([
                                MarkdownEditor::make('description')
                                    ->label('Message to customer')
                                    //->default("Hey there! Your cart is packed with awesome finds! Don't let them slip away—complete your purchase now and score these amazing deals! 🚀 Let's make it happen!")
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'strike'],
                                        ['heading'],
                                        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                        ['undo', 'redo'],
                                    ])
                                     ->afterStateHydrated(function ($component, $state) {
                                        if (blank($state)) {
                                            $component->state("Hey there! Your cart is packed with awesome finds! Don't let them slip away—complete your purchase now and score these amazing deals! 🚀 Let's make it happen!");
                                        }
                                    }),
                            ]),

                        Step::make('Create Discount Code')
                            ->description('Create a discount code for this customer')
                            ->schema([
                                Toggle::make('create_discount')
                                    ->label('Create Discount Code?')
                                    ->default(false)
                                    ->reactive(),

                                TextInput::make('discount_value')
                                    ->label('Discount %')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->visible(fn ($get) => $get('create_discount'))
                                    ->required(fn ($get) => $get('create_discount')),

                                TextInput::make('discount_code')
                                    ->label('Discount code')
                                    ->required(fn ($get) => $get('create_discount'))
                                    ->visible(fn ($get) => $get('create_discount'))
                                    ->afterStateUpdated(function ($component, $state, $get) {
                                        if ($get('create_discount')) {
                                            $service = new ShopifyDiscountService(
                                                $get('shop_domain'),
                                                $get('access_token')
                                            );
                                            $value = $get('discount_value') ?? 10;
                                            $prefix = $get('discount_code') ?? 'CART';
                                            $couponData = $service->createDiscountCode($prefix, $value);

                                            if (is_array($couponData)) {
                                                // Guardar o actualizar el cupón en la base de datos
                                                \App\Models\Coupon::updateOrCreate(
                                                    ['code' => $couponData['code']],
                                                    $couponData
                                                );
                                                $component->state($couponData['code']);
                                            } else {
                                                $component->state('Error generating coupon');
                                            }
                                        }
                                    })
                                ]),

                        Step::make('See before sending')
                            ->description('See the mesage to verify')
                            ->schema([
                                TextEntry::make('to')
                                    ->label('📧 To')
                                    ->state(fn ($get) => $get('email')),

                                TextEntry::make('subject')
                                    ->label('📝 Subject')
                                    ->state(fn () => 'Don’t miss out — your cart is ready to checkout!'),

                                TextEntry::make('subject')
                                    ->label('📝 Subject')
                                    ->state(fn ($get) => $get('description')),


                            ]),
                    ])->action(function ($data, $record) {

                        // Puedes incluir el cupón en el mensaje:
                        $message = $data['description'];

                        if (!empty($data['discount_code'])) {
                            $message .= "\n\n🎁 Use this coupon for your purchase: {$data['discount_code']}";
                        }

                        $recoveryUrlWhatsapp = route('cart.recover', [
                            'token' => $record->recovery_token,
                            'via' => 'whatsapp'
                        ]);

                        // 1. Enviar WhatsApp
                        if (!empty($record['phone_client']) && $record['phone_client'] && !empty($record['phone_client'])) {
                            $phone = preg_replace('/\D/', '', $record['phone_client']); // limpiar
                            $message .= "\n\n🛒 Recover your cart here: {$recoveryUrlWhatsapp}";

                            $ok = TwilioWhatsAppService::sendMessage($phone, $message);

                            if ($ok) {
                                Notification::make()
                                    ->title('Mensaje de WhatsApp enviado')
                                    ->success()
                                    ->send();
                            } else {

                                Notification::make()
                                    ->title('Error al enviar WhatsApp')
                                    ->danger()
                                    ->send();
                            }
                        }

                        // 2. Enviar el correo
                        try {

                            $recoveryUrlEmail = route('cart.recover', [
                                'token' => $record->recovery_token,
                                'via' => 'email'
                            ]);

                            //dd($data, $record);
                            Mail::to($record['email_client'])->send(new CartRecoveryMail([
                                'email' => $record['email_client'],
                                'description' => $message,
                                'checkout_url' => $recoveryUrlEmail,
                                'total_price' => $record['total_price'] ?? null,
                                'currency' => $record['response.currency'] ?? null,
                            ]));

                            Log::info("email enviado correctamente: ");

                            Notification::make()
                                ->title('Email sending correctly!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error("error al enviar email: ");
                            Notification::make()
                                ->title('Error when send to email!')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
