<?php

namespace App\Filament\Admin\Resources\SubscriptionItems;

use App\Filament\Admin\Resources\SubscriptionItems\Pages\CreateSubscriptionItems;
use App\Filament\Admin\Resources\SubscriptionItems\Pages\EditSubscriptionItems;
use App\Filament\Admin\Resources\SubscriptionItems\Pages\ListSubscriptionItems;
use App\Filament\Admin\Resources\SubscriptionItems\Schemas\SubscriptionItemsForm;
use App\Filament\Admin\Resources\SubscriptionItems\Tables\SubscriptionItemsTable;
use App\Models\SubscriptionItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionItemsResource extends Resource
{
    protected static ?string $model = SubscriptionItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'SubscriptionItem';

    public static function form(Schema $schema): Schema
    {
        return SubscriptionItemsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionItems::route('/'),
            'create' => CreateSubscriptionItems::route('/create'),
            'edit' => EditSubscriptionItems::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
