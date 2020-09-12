<?php


namespace App\Repositories\API;


use App\Models\Purchase;

class PurchaseRepository
{
    private $purchase;

    public function __construct()
    {
        $this->purchase = new Purchase();
    }

    public function getPurchases($data)
    {
        $this->purchase = $this->purchase->with(['item']);
        if (!empty($data['purchase_date'])) {
            $this->purchase = $this->purchase->where('purchase_date', $data['purchase_date']);
        }

        $purchases = $this->purchase->get();

        $prepare_array = [];

        foreach ($purchases as $purchase) {
            $prepare_array[] = [
                'purchase_id'    => $purchase->id,
                'item_id'        => $purchase->item_id,
                'item_name'      => $purchase->item->item_name,
                'purchaser_name' => $purchase->purchaser_name,
                'item_quantity'  => $purchase->item_quantity,
                'item_price'     => $purchase->item_price,
                'item_total'     => $purchase->item_total,
                'purchase_date'  => $purchase->purchase_date,
                'gst'            => $purchase->gst,
            ];
        }
        return $prepare_array;
    }

    public function addPurchase($data)
    {
        $this->purchase->create([
            'item_id'        => $data['item_id'],
            'purchaser_name' => $data['purchaser_name'],
            'item_quantity'  => $data['item_quantity'],
            'item_price'     => $data['item_price'],
            'item_total'     => $data['item_total'],
            'purchase_date'  => $data['purchase_date'],
            'gst'            => !empty($data['gst']) ? $data['gst'] : 0,
        ]);
    }
}
