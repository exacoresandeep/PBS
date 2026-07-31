<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\OutstandingNew;
use Illuminate\Support\Facades\Log;
use Exception;

class FetchOutstandingNew extends Command
{
    protected $signature = 'app:fetch-outstanding-new';
    protected $description = 'Fetch outstanding balances from SAP HANA (Dealer × Product)';

    public function handle()
    {
        Log::info('FetchOutstandingNew started at ' . now());
        $this->info('Starting Outstanding Balance Sync...');

        try {
            /* ---------------- ODBC CONNECTION ---------------- */
            $conn = odbc_connect('HANAODBC', 'INDUS', 'Indus@123');
            if (!$conn) {
                $this->error('ODBC Connection Failed: ' . odbc_errormsg());
                return 1;
            }

            $dealers  = Dealer::whereNotNull('dealer_code')->get();
            $products = Product::whereNotNull('sap_id')->get();

            if ($products->isEmpty()) {
                $this->error('No products with sap_id found');
                return 1;
            }

            /* ---------------- DEALER × PRODUCT LOOP ---------------- */
            foreach ($dealers as $dealer) {
                foreach ($products as $product) {

                    $dealerCode = $dealer->dealer_code;
                    $sapId      = $product->sap_id;

                    $this->info(
                        "Processing Dealer: {$dealerCode}, Product: {$product->product_name}"
                    );

                    $sql = "CALL \"PRABHU_NEW\".\"@CustomerBalance_F\"(
                        '{$dealerCode}',
                        {$sapId}
                    )";

                    $result = odbc_exec($conn, $sql);
                    if (!$result) {
                        Log::error("SAP query failed: {$dealerCode} | {$product->product_name}");
                        continue;
                    }

                    /* ---------------- STORE DATA ---------------- */
                    while ($row = odbc_fetch_array($result)) {

                        $data = array_map('trim', $row);

                        if (!isset($data['ShortName'], $data['Balance'])) {
                            continue;
                        }

                        $matchedDealer = $dealers->firstWhere(
                            'dealer_code',
                            $data['ShortName']
                        );

                        if (!$matchedDealer) {
                            Log::warning("Dealer not found: {$data['ShortName']}");
                            continue;
                        }

                        OutstandingNew::updateOrCreate(
                            [
                                'dealer_id'  => $matchedDealer->id,
                                'product_id' => $product->id,
                            ],
                            [
                                'outstanding_amount' => (float) str_replace(',', '', $data['Balance']),
                                'due_balance' => (
                                    isset($data['DueBalance']) || $data['DueBalance'] === '0'
                                )
                                    ? (float) str_replace(',', '', $data['DueBalance'])
                                    : 0,
                            ]
                        );

                        $this->info(
                            "Stored → Dealer: {$matchedDealer->dealer_code}, " .
                            "Product: {$product->product_name}, " .
                            "Outstanding: {$data['Balance']}"
                        );
                    }
                }
            }

            odbc_close($conn);

            $this->info('Outstanding balance sync completed successfully');
            Log::info('FetchOutstandingNew completed at ' . now());

            return 0;

        } catch (Exception $e) {

            if (isset($conn)) {
                odbc_close($conn);
            }

            Log::error($e);
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}

