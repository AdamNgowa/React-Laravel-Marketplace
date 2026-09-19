<?php

use App\Filament\Resources\Products\Pages\ProductVariations;
use App\Models\Product;

it('keeps variation saves working when a row has no id yet', function () {
    $product = new Product();
    $product->setRelation('variationTypes', collect([
        (object) ['id' => 10, 'name' => 'Color'],
        (object) ['id' => 11, 'name' => 'Size'],
    ]));

    $page = new class($product) extends ProductVariations
    {
        public function __construct($record)
        {
            $this->record = $record;
        }

        public function runMutate(array $data): array
        {
            return $this->mutateFormDataBeforeSave($data);
        }
    };

    $result = $page->runMutate([
        'variations' => [[
            'variation_type_10' => ['id' => 5],
            'variation_type_11' => ['id' => 8],
            'quantity' => 12,
            'price' => 19.99,
        ]],
    ]);

    expect($result['variations'][0])->toMatchArray([
        'id' => null,
        'variation_type_option_ids' => [5, 8],
        'quantity' => 12,
        'price' => 19.99,
    ]);
});
