<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class ProductImages extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static string|null $title = 'Images';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-photo';

    // IMPORTANT: instance method, exact signature and return type
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->model($this->getRecord())
            ->operation('edit')
            ->statePath('data')
            ->components([
                SpatieMediaLibraryFileUpload::make('images')
                    ->hiddenLabel()
                    ->image()
                    ->openable()
                    ->multiple()
                    ->panelLayout('grid')
                    ->collection('images')
                    ->reorderable()
                    ->appendFiles()
                    ->preserveFilenames()
                    ->columnSpan(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Product Images';
    }
}
