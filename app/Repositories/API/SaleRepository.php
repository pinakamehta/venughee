<?php


namespace App\Repositories\API;


use App\Models\Sales;

class SaleRepository
{
    private $sale;

    public function __construct()
    {
        $this->sales = new Sales();
    }

    public function getSales($data)
    {
        $this->sales = $this->sales->with(['item']);
        if (!empty($data['sales_date'])) {
            $this->sales = $this->sales->where('sales_date', $data['sales_date']);
        }

        $sales = $this->sales->get();

        $prepare_array = [];

        foreach ($sales as $sale) {
            $prepare_array[] = [
                'sales_id'      => $sale->id,
                'item_id'       => $sale->item_id,
                'item_name'     => $sale->item->item_name,
                'seller_name'   => $sale->seller_name,
                'item_quantity' => $sale->item_quantity,
                'item_price'    => $sale->item_price,
                'item_total'    => $sale->item_total,
                'sales_date'    => $sale->sales_date,
            ];
        }
        return $prepare_array;
    }

    public function addSales($data)
    {
        $this->sales->create([
            'item_id'       => $data['item_id'],
            'seller_name'   => $data['seller_name'],
            'item_quantity' => $data['item_quantity'],
            'item_price'    => $data['item_price'],
            'item_total'    => $data['item_total'],
            'sales_date'    => $data['sales_date'],
        ]);
    }
}
