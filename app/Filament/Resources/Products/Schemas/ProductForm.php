<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use App\ProductStatusEnum;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                ->live(onBlur:true)
                ->required()
                ->afterStateUpdated(function (string $operation, $state, callable $set) {
                    $set('slug', Str::slug($state));
                }
            ),
            TextInput::make('slug')
             ->required(),
            Select::make('department_id')
             ->relationship('department', 'name')
             ->label(__('Department'))
             ->preload()
             ->searchable()
             ->required()
             ->reactive()
             ->afterStateUpdated(function (callable $set){
                    $set('category_id', null);
             
             }),
            Select::make('category_id')
                ->relationship(
                name:'category',
                titleAttribute: 'name',
                modifyQueryUsing:function(Builder $query,callable $get) {
                    $departmentId = $get('department_id');
                    if ($departmentId) {
                        $query->where('department_id',$departmentId); //Filter categories based on d
                    }
                }
                )
                ->label(__('Category'))
                ->preload()
                ->searchable()
                ->required(),
                RichEditor::make('description')
        ->required()
        ->toolbarButtons([
            // 'blockQuote',
            'bold',
            'bulletList',
            'h2',
            'h3',
            'italic',
            'link',
            'orderedList',
            'redo',
            'strike',
            'underline',
            'undo',
            'table',

        ])
        ->columnSpan(2),
        TextInput::make('price')
        ->numeric()
        ->required(),
        TextInput::make('quantity')
        ->integer(),
        Select::make('status')
        ->options(ProductStatusEnum::labels())
        ->default(ProductStatusEnum::Draft->value)
        ->required(),
        ],
        
        );
    }
}
