<?php


namespace App\Repositories\API;


use App\Models\Item;

class ItemRepository
{
    public function getItems($data)
    {
        $items = Item::where('is_active', 1)
            ->where('added_by', $data['user_id'])
            ->get([
                'id',
                'item_name',
                'unit',
                'sales_price',
                'sales_description',
                'purchase_price',
                'purchase_description',
                'gst'
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
                    'gst'                  => checkEmpty($item, 'gst', ''),
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
            'gst'                  => checkEmpty($data, 'gst', null),
            'sales_description'    => checkEmpty($data, 'sales_description', ''),
            'purchase_price'       => checkEmpty($data, 'purchase_price', 0),
            'purchase_description' => checkEmpty($data, 'purchase_description', ''),
            'added_by'             => $data['user_id']
        ]);
    }

    public function updateItem($data, $item_id)
    {
        $item = Item::where('id', $item_id)->first();

        if (empty($item)) {
            throw new \Exception('Invalid item id');
        }

        $item->item_name            = $data['item_name'];
        $item->unit                 = checkEmpty($data, 'unit', 0);
        $item->sales_price          = checkEmpty($data, 'sales_price', 0);
        $item->gst                  = checkEmpty($data, 'gst', 0);
        $item->sales_description    = checkEmpty($data, 'sales_description', '');
        $item->purchase_price       = checkEmpty($data, 'purchase_price', 0);
        $item->purchase_description = checkEmpty($data, 'purchase_description', '');

        $item->save();
    }

    public function deleteItem($item_id)
    {
        Item::where('id', $item_id)->delete();
    }
}
