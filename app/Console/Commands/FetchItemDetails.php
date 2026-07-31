<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductDetails;
use App\Models\ProductType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FetchItemDetails extends Command
{
    protected $signature = 'app:fetch-item-details';
    protected $description = 'Fetch item details from SAP and update products_details table';

    /**
     * Clean SAP string values (fix NBSP, broken UTF-8, � symbol)
     */
    private function cleanSapString($value)
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        // Replace SAP non-breaking spaces
        $value = str_replace(["\xC2\xA0", "\xA0"], ' ', $value);

        // Remove replacement characters
        $value = str_replace('�', '', $value);

        // Force valid UTF-8
        $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return trim($value);
    }

    public function handle()
    {
        Log::info('✅ Running FetchItemDetails at ' . now());

        try {
            $conn = odbc_connect('HANAODBC', 'INDUS', 'Indus@123');

            if (!$conn) {
                $this->error('ODBC Connection Failed: ' . odbc_errormsg());
                return 1;
            }

            $sql = 'SELECT * FROM "PRABHU_NEW"."MOBILEAPP_ITEMDETAIL"';
            $result = odbc_exec($conn, $sql);

            if (!$result) {
                $this->error('ODBC Query Failed: ' . odbc_errormsg($conn));
                return 1;
            }

            $items = [];
            while ($row = odbc_fetch_array($result)) {
                // 🔹 Clean ALL SAP values here
                $items[] = array_map(function ($value) {
                    return $this->cleanSapString($value);
                }, $row);
            }

            if (empty($items)) {
                $this->warn('No item details found in SAP response.');
                return 0;
            }

            foreach ($items as $item) {
                DB::beginTransaction();

                try {
                    // 🔹 Product mapping
                    $brand = $this->cleanSapString($item['Type'] ?? '');
                    $brandCode = strtolower($brand);

                    $product = \App\Models\Product::where('product_code', $brandCode)->first();

                    if (!$product) {
                        $this->error("❌ No product found for brand: {$brandCode}");
                        DB::rollBack();
                        continue;
                    }

                    $product_id = $product->id;

                    // 🔹 Product Type
                    $productTypeName = $this->cleanSapString($item['Product Code'] ?? '');

                    $productType = ProductType::updateOrCreate(
	[
                            'type_name'  => $productTypeName,
                            'product_id' => $product_id
                        ],
                        []       
		    );

                    // 🔹 Insert / Update Product Details
                    ProductDetails::updateOrCreate(
                        [
				'product_name' => $this->cleanSapString($item['Product'] ?? ''),
				'product_id'   => $product_id
                        ],
                        [
                         
                            'item_profile' => $this->cleanSapString($item['Item Profile'] ?? null),
                            'item_thickness' => $this->cleanSapString($item['Item Thickness'] ?? null),
                            'type_id' => $productType->id,
                            'primary_group' => $this->cleanSapString($item['Primary Group'] ?? null),

                            // ✅ Safe decimal handling
                            'weight' => (isset($item['Weight']) && is_numeric($item['Weight']))
                                ? round((float) $item['Weight'], 6)
                                : null,

                            'total_available_quantity' => 0,
                            'availability_status' =>
                                ($item['Availability Status'] ?? '') === 'Available'
                                    ? 'Available'
                                    : 'Unavailable',
'status' => ($item['Status'] == "N") ? "Inactive" : "Active",
                            'stock_updated_at' => Carbon::now(),
                            'rate' => 0,
                            'updated_at' => now(),
                        ]
                    );

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error('❌ Database Error: ' . $e->getMessage());
                }
            }

            odbc_close($conn);
            $this->info('✅ Item details successfully updated from SAP.');

        } catch (\Exception $e) {
            if (isset($conn)) {
                odbc_close($conn);
            }

            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}

