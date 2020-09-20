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
                'item_name',
                'unit',
                'sales_price',
                'purchase_price'
            ])
            ->toArray();
    }

    public function addItem($data)
    {
        Item::create([
            'item_name'      => $data['item_name'],
            'unit'           => checkEmpty($data, 'unit', 0),
            'sales_price'    => checkEmpty($data, 'sales_price', 0),
            'purchase_price' => checkEmpty($data, 'purchase_price', 0)
        ]);
    }

    public function deleteItem($item_id)
    {
        Item::where('id', $item_id)->delete();
    }
}
