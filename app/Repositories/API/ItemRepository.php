<?php


namespace App\Repositories\API;


use App\Models\Item;

class ItemRepository
{
    public function getItems()
    {
        $items = Item::where('is_active', 1)
            ->get([
                'id',
                'item_name',
                'unit',
                'sales_price',
                'sales_description',
                'purchase_price',
                'purchase_description'
            ]);

        $items_array = [];

        if (!empty($items->toArray())) {
            foreach ($items as $item) {
                $items_array[] = [
                    'id'                   => $item->id,
                    'item_name'            => $item->item_name,
                    'unit'                 => $item->unit,
                    'sales_price'          => $item->sales_price,
                    'sales_description'    => checkEmpty($item, 'sales_description', ''),
                    'purchase_price'       => $item->purchase_price,
                    'purchase_description' => checkEmpty($item, 'purchase_description', ''),
                ];
            }
        }
        return $items_array;
    }

    public function addItem($data)
    {
        return Item::create([
            'item_name'            => $data['item_name'],
            'unit'                 => checkEmpty($data, 'unit', 0),
            'sales_price'          => checkEmpty($data, 'sales_price', 0),
            'sales_description'    => checkEmpty($data, 'sales_description', ''),
            'purchase_price'       => checkEmpty($data, 'purchase_price', 0),
            'purchase_description' => checkEmpty($data, 'purchase_description', '')
        ]);
    }

    public function deleteItem($item_id)
    {
        Item::where('id', $item_id)->delete();
    }
}
