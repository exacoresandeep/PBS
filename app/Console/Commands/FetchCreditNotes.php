<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CreditNote;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\OrderItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class FetchCreditNotes extends Command
{
    protected $signature = 'app:fetch-credit-notes';
    protected $description = 'Fetch Credit Notes from SAP product-wise using sap_id';

    public function handle()
    {
        Log::info('✅ Running FetchCreditNotes at ' . now());

        $start = Carbon::create(2021, 1, 1);
        $end   = Carbon::now()->startOfMonth();

        try {

            /** 🔹 Fetch Products with SAP ID */
            $products = Product::whereNotNull('sap_id')->get();

            if ($products->isEmpty()) {
                $this->error('No products with sap_id found');
                return 1;
            }

            /** 🔹 ODBC Connection */
            $conn = odbc_connect('HANAODBC', 'INDUS', 'Indus@123');
            if (!$conn) {
                $this->error('ODBC Connection Failed: ' . odbc_errormsg());
                return 1;
            }

            while ($start->lte($end)) {

                $fromDate = $start->format('Ymd');
                $toDate   = $start->copy()->addMonths(2)->subDay()->format('Ymd');

                foreach ($products as $product) {

                    $this->info("📦 {$product->product_name} | $fromDate → $toDate");

                    /** 🔹 Correct SAP Procedure Call */
                    $sql = 'CALL "PRABHU_NEW"."MobileApp_CreditNote_Detail_Param_F"(
                        \'' . $fromDate . '\',
                        \'' . $toDate . '\',
                        ' . (int)$product->sap_id . '
                    )';

                    $result = odbc_exec($conn, $sql);

                    if (!$result) {
                        $this->error('ODBC Query Failed: ' . odbc_errormsg($conn));
                        continue;
                    }

                    $response = [];
                    while ($row = odbc_fetch_array($result)) {
                        $response[] = array_map('trim', $row);
                    }

                    if (empty($response)) {
                        $this->info("No credit notes for {$product->product_name}");
                        continue;
                    }

                    DB::beginTransaction();

                    /** 🔹 Group by Credit Note Number */
                    $groupedData = [];
                    foreach ($response as $data) {
                        $groupedData[$data['Credit Note Number']][] = $data;
                    }

                    foreach ($groupedData as $creditNoteNumber => $items) {

                        $invoiceNumber = trim($items[0]['AR Invoice Number'] ?? '');
                        $dealerCode    = $items[0]['Customer Code'] ?? '';

                        $dealer = Dealer::where('dealer_code', $dealerCode)->first();

                        if (!$dealer) {
                            $this->warn("Dealer not found for Credit Note: $creditNoteNumber");
                            continue;
                        }

                        /** 🔹 Order handling */
                        $order = null;
                        if ($invoiceNumber) {
                            $order = Order::firstOrCreate(
                                ['invoice_number' => $invoiceNumber],
                                [
                                    'dealer_id'     => $dealer->id,
                                    'invoice_date'  => $this->safeParseDate($items[0]['Date']),
                                    'invoice_total' => $items[0]['Total'],
                                    'status'        => 'Delivered',
                                ]
                            );
                        }

                        $returnedItems = [];
                        $totalReturnQuantity = 0;
                        $totalRowAmount = 0;

                        foreach ($items as $item) {

                            $quantity  = (float) $item['Quantity'];
                            $lineTotal = (float) $item['Total'];
                            $itemCode  = $item['ItemCode'] ?? '';

                            $totalReturnQuantity += $quantity;
                            $totalRowAmount      += $lineTotal;

                            if (!$itemCode) {
                                continue;
                            }

                            $productType = ProductType::where('type_name', $itemCode)->first();
                            if (!$productType) {
                                continue;
                            }

                            $returnedItems[] = [
                                'product_type_id' => $productType->id,
                                'product_type'    => $itemCode,
                                'quantity'        => $quantity,
                                'rate'            => $lineTotal,
                                'totalAmount'     => $lineTotal,
                            ];

                            /** 🔹 Update Order Items */
                            if ($order) {

                                $productDetails = [[
                                    'product_type_id' => $productType->id,
                                    'quantity'        => $quantity,
                                    'rate'            => $lineTotal,
                                    'typeName'        => $itemCode,
                                    'totalAmount'     => $lineTotal,
                                ]];

                                OrderItem::updateOrCreate(
                                    [
                                        'order_id'  => $order->id,
                                        'product_id'=> $product->id,
                                    ],
                                    [
                                        'total_quantity'   => $quantity,
                                        'balance_quantity' => 0,
                                        'product_details'  => json_encode($productDetails),
                                    ]
                                );
                            }
                        }

                        /** 🔹 Save Credit Note */
                        CreditNote::updateOrCreate(
                            ['credit_note_number' => $creditNoteNumber],
                            [
                                'order_id'              => $order?->id,
                                'dealer_id'             => $dealer->id,
                                'product_id'            => $product->id,
                                'date'                  => $this->safeParseDate($items[0]['Date']),
                                'returned_items'        => $returnedItems,
                                'total_return_quantity' => $totalReturnQuantity,
                                'total_row_amount'      => $totalRowAmount,
                                'status'                => ($items[0]['Status'] === 'C') ? 'closed' : 'open',
                            ]
                        );
                    }

                    DB::commit();
                }

                $start->addMonths(2);
            }

            odbc_close($conn);

        } catch (Exception $e) {
            DB::rollBack();
            if (isset($conn)) odbc_close($conn);
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error($e);
        }
    }

    /** 🔹 Helpers */

    private function safeParseDate($value)
    {
        try {
            $cleaned = $this->cleanDateString($value);
            return Carbon::parse($cleaned)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    private function cleanDateString($value)
    {
        $value = preg_replace('/[^\d\-:\s\.]/', '', $value);
        return trim(explode('.', $value)[0]);
    }
}

