<?php


namespace App\Repositories\API;


use App\Models\Item;

class ItemRepository
{
    public function getItems()
    {
        return Item::where('is_active', 1)
            ->get([
                'id',
                'item_name'
            ])
            ->toArray();
    }

    public function addItem($data)
    {
        Item::create([
            'item_name' => $data['item_name']
        ]);
    }
}
