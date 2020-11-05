<?php


namespace App\Repositories\API;


use App\Models\Invoice;
use App\Models\Item;
use Exception;

class ItemRepository
{
    private $item;

    public function __construct()
    {
        $this->item = new Item();
    }

    public function getItems($data)
    {
        $item_for = checkEmpty($data, 'item_for', 'sales');

        $items = $this->item->where('is_active', 1)
            ->where('item_for', $item_for)
            ->where('added_by', $data['user_id'])
            ->get([
                'id',
                'item_name',
                'unit',
                'stock',
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
                    'stock'                => $item->stock,
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
        return $this->item->create([
            'item_name'            => $data['item_name'],
            'item_for'             => checkEmpty($data, 'item_for', 'sales'),
            'unit'                 => checkEmpty($data, 'unit', 'kg'),
            'stock'                => checkEmpty($data, 'stock', 0),
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
        $item = $this->item->where('id', $item_id)->first();

        if (empty($item)) {
            throw new Exception('Invalid item id');
        }

        $item->item_name            = $data['item_name'];
        $item->unit                 = checkEmpty($data, 'unit', $item->unit);
        $item->stock                = checkEmpty($data, 'stock', $item->stock);
        $item->sales_price          = checkEmpty($data, 'sales_price', $item->sales_price);
        $item->gst                  = checkEmpty($data, 'gst', $item->gst);
        $item->sales_description    = checkEmpty($data, 'sales_description', $item->sales_description);
        $item->purchase_price       = checkEmpty($data, 'purchase_price', $item->purchase_price);
        $item->purchase_description = checkEmpty($data, 'purchase_description', $item->purchase_description);

        $item->save();
    }

    public function deleteItem($item_id)
    {
        $this->item->where('id', $item_id)->delete();
    }

    public function itemStock($item_id)
    {
        $item = $this->item->where('id', $item_id)->first();

        if (empty($item)) {
            throw new Exception('Invalid item id');
        }

        return $item->stock;
    }

    public function getInvoices($item_id)
    {
        $item = $this->item->where('id', $item_id)->first();

        if (empty($item)) {
            throw new Exception("Invalid Item Id");
        }

        $item_data = [
            'id'                   => $item->id,
            'item_name'            => $item->item_name,
            'unit'                 => $item->unit,
            'stock'                => $item->stock,
            'sales_price'          => $item->sales_price,
            'sales_description'    => checkEmpty($item, 'sales_description', ''),
            'purchase_price'       => $item->purchase_price,
            'purchase_description' => checkEmpty($item, 'purchase_description', ''),
            'gst'                  => checkEmpty($item, 'gst', ''),
        ];

        $invoices = Invoice::leftJoin("stock_transactions", "stock_transactions.invoice_id", "=", "invoices.id")
            ->where("stock_transactions.item_id", $item->id)
            ->get();

        $invoice_data = [];

        if (!empty($invoices)) {
            foreach ($invoices as $invoice) {
                $invoice_data[] = [
                    "title"          => "Invoice",
                    "invoice_number" => !empty($invoice->custom_invoice_number) ? $invoice->custom_invoice_number : $invoice->invoice_number,
                    "invoice_date"   => $invoice->invoice_date,
                    "item_quantity"  => $invoice->item_quantity
                ];
            }
        }

        $item_data['invoices'] = $invoice_data;

        return $item_data;
    }
}
