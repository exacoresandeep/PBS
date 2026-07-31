<?php

namespace App\Exports;

use App\Models\InfluencerVisit;
use App\Models\ProductType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
class InfluencerVisitExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
	protected $product_id;
    	protected $month;
    protected $year;
    protected $row = 1;

    public function __construct($month, $year,$product_id)
    {
        $this->month = $month; 
	$this->year = $year;
	$this->product_id = $product_id;
    }
public function collection()
{
    $productID =$this->product_id ?? \App\Helpers\ProductHelper::getSelectedProductID();

    return InfluencerVisit::with([
            'district',
            'order.dealer',
            'order.paymentTerm',
            'followUps',
            'order.orderItems',
            'createdBy'
        ])
        ->whereHas('createdBy', function ($q) use ($productID) {
            $q->whereJsonContains('products', (string) $productID);
        })
->where(function ($query) {
                $query->where('status', '!=', 'Follow Up')
                    ->orWhere(function ($q) {
                        $q->where('status', 'Follow Up')
                            ->whereNotNull('follow_up_date');
                    });
            })        ->get()
        ->map(function ($visit) {

            /**
             * 🔑 SAME SORT DATE LOGIC AS API
             */
            if ($visit->status === 'Follow Up') {
                $sortDate = $visit->follow_up_date
                    ? Carbon::parse($visit->follow_up_date)
                    : $visit->updated_at;
            } else {
                $sortDate = $visit->created_at;
            }

            $visit->_sort_date = $sortDate;
            return $visit;
        })
        ->filter(function ($visit) {

            if (!$visit->_sort_date) {
                return false;
            }

            /**
             * 📅 Month / Year filter (EXPORT)
             */
            return $visit->_sort_date->year == $this->year
                && $visit->_sort_date->month == $this->month;
        })
        ->sortByDesc('_sort_date')
        ->values();
}
public function headings(): array
    {
        return [
            'S.No',
            'Date',
            'Time',
            'Influencer Name',
            'Phone',
            'Place',
            'Influencer Type',
            'Visit Type',
            'Purpose',

            'District',
            'Lead Type',
            'Current Project',
            'Upcoming Project',
            'Steel Used',

            'Total Deal Volume',
            'Status',
            'Follow Up Date',
            'Follow Up Reason',
            'Dealer (Won)',
            'Payment Terms',

            'Product Details',

            'Won Quantity', //notdone
            'Balance Quantity',
            'Total Order Amount',
            'Lost Volume',
            'Lost To Competitor',
	    'Reason For Lost',
	    'Created By'
        ];
    }

    public function map($visit): array
    {
        $order = $visit->order;

        $productSummary = '';
        $totalOrderedQty = 0;
        $totalOrderAmount = 0;

        if ($order && $order->orderItems) {

            $productSummary = $order->orderItems->map(function ($item) use (&$totalOrderedQty, &$totalOrderAmount) {

                $details = collect($item->product_details)->map(function ($detail) use (&$totalOrderedQty, &$totalOrderAmount) {

                    $typeName = \App\Models\ProductType::find($detail['product_type_id'])->type_name ?? 'N/A';
                    $qty = $detail['quantity'] ?? 0;
                    $amt = $detail['quantity'] * $detail['rate'] ?? 0;
                    $rate = $detail['rate'] ?? 0;

                    // Add totals
                    $totalOrderedQty += $qty;
                    $totalOrderAmount += $amt;

                    return "{$typeName} (Qty: {$qty}, Amount: {$amt}, Rate: {$rate})";
                });

                return $details->implode(' | ');

            })->implode(' || ');
        }

        // Balance Quantity = Total Deal Volume - Ordered Quantity
        $balanceQty = $visit->total_deal_volume - $totalOrderedQty;

        return [
            $this->row++,
            optional($visit->created_at)->format('Y-m-d'),
            optional($visit->created_at)->format('H:i:s'),
            $visit->influencer_name,
            $visit->phone,
            $visit->place,
            $visit->influencer_type,
            $visit->visit_type,
            $visit->purpose,

            optional($visit->district)->name,
            $visit->lead_type,
            $visit->current_project,
            $visit->upcoming_project,
            is_array($visit->steel_used) ? implode(', ', $visit->steel_used) : '',

            $visit->total_deal_volume,              // TOTAL DEAL VOLUME (FROM DB)
            $visit->status,
            $visit->status === 'Follow Up' ? optional($visit->follow_up_date)->format('Y-m-d') : '',
            $visit->status === 'Follow Up' ? optional($visit->followUps->first())->reason : '',
            $visit->status === 'Won' ? optional(optional($order)->dealer)->dealer_name : '',
            $visit->status === 'Won' ? optional(optional($order)->paymentTerm)->name : '',
            $productSummary,


            $visit->status === 'Won' ? $visit->won_volume : '',
            // $totalOrderedQty,                       // TOTAL ORDERED QTY
            $balanceQty,                            // BALANCE QTY
            $totalOrderAmount,                      // TOTAL ORDER AMOUNT



            $visit->status === 'Lost' ? $visit->lost_volume : '',
            $visit->status === 'Lost' ? $visit->lost_to_competitor : '',
	    $visit->status === 'Lost' ? $visit->reason_for_lost : '',
	    optional($visit->createdBy)->name ?? '',
	   // $visit->id
        ];
    }

    public function collectionOldd()
    {
        $productID = \App\Helpers\ProductHelper::getSelectedProductID();

        return InfluencerVisit::with([
                'district',
                'order.dealer',
                'order.paymentTerm',
                'followUps',
                'order.orderItems',
                'createdBy'
            ])
            ->whereHas('createdBy', function ($q) use ($productID) {
                $q->whereJsonContains('products', (string) $productID);
            })
            ->get()
            ->filter(function ($visit) {

                /**
                 * 1️⃣ Activity date (created / updated based on status)
                 */
                $activityDate = in_array($visit->status, ['Follow Up', 'Won', 'Lost'])
                    ? $visit->updated_at
                    : $visit->created_at;

                /**
                 * 2️⃣ Follow-up date
                 */
                $followUpDate = $visit->follow_up_date
                    ? Carbon::parse($visit->follow_up_date)
                    : null;

                /**
                 * 3️⃣ Effective date (latest wins)
                 */
                $effectiveDate = collect([$activityDate, $followUpDate])
                    ->filter()
                    ->max();

                if (!$effectiveDate) {
                    return false;
                }

                /**
                 * 4️⃣ Month / Year filter (EXPORT LOGIC)
                 */
                return $effectiveDate->year == $this->year
                    && $effectiveDate->month == $this->month;
            })
            ->sortByDesc(function ($visit) {

                $activityDate = in_array($visit->status, ['Follow Up', 'Won', 'Lost'])
                    ? $visit->updated_at
                    : $visit->created_at;

                $followUpDate = $visit->follow_up_date
                    ? Carbon::parse($visit->follow_up_date)
                    : null;

                return collect([$activityDate, $followUpDate])
                    ->filter()
                    ->max();
            })
            ->values();
    }
    public function collectionOld()
    {
	    $productID= \App\Helpers\ProductHelper::getSelectedProductID();
        return InfluencerVisit::with(['district', 'order.dealer', 'order.paymentTerm', 'followUps', 'order.orderItems',"createdBy"])
            ->whereYear('created_at', $this->year)
	    ->whereMonth('created_at', $this->month)
    ->whereHas('createdBy', function($q) use ($productID) { //push
                $q->whereJsonContains('products', (string)$productID);
            })
            ->get();
    }

    public function headingsold(): array
    {
        return [
            'S.No',
            'Date',
            'Time',
            'Influencer Name',
            'Phone',
            'Place',
            'Influencer Type',
            'Visit Type',
            'Purpose',
            'District',
            'Lead Type',
            'Current Project',
            'Upcoming Project',
            'Steel Used',
            'Total Deal Volume',
            'Total Ordered Quantity',
            'Total Order Amount',
            'Balance Quantity',
            'Status',

            'Follow Up Date',
            'Follow Up Reason',

            'Lost Volume',
            'Lost To Competitor',
            'Reason For Lost',

            'Dealer (Won)',
            'Payment Terms',
            'Credit Days',
            'Order Total Amount',

            'Product Details',
            'Created By',
	    'Created At',
	    'Updated At'
        ];
    }

    public function mapold($visit): array
    {
        $order = $visit->order;

        $productSummary = '';
        $totalOrderedQty = 0;
        $totalOrderAmount = 0;

        if ($order && $order->orderItems) {

            $productSummary = $order->orderItems->map(function ($item) use (&$totalOrderedQty, &$totalOrderAmount) {

                $details = collect($item->product_details)->map(function ($detail) use (&$totalOrderedQty, &$totalOrderAmount) {

                    $typeName = \App\Models\ProductType::find($detail['product_type_id'])->type_name ?? 'N/A';
                    $qty = $detail['quantity'] ?? 0;
                    $amt = $detail['totalAmount'] ?? 0;
                    $rate = $detail['rate'] ?? 0;

                    // Add totals
                    $totalOrderedQty += $qty;
                    $totalOrderAmount += $amt;

                    return "{$typeName} (Qty: {$qty}, Amount: {$amt}, Rate: {$rate})";
                });

                return $details->implode(' | ');

            })->implode(' || ');
        }

        // Balance Quantity = Total Deal Volume - Ordered Quantity
        $balanceQty = $visit->total_deal_volume - $totalOrderedQty;

        return [
            $this->row++,
            optional($visit->created_at)->format('Y-m-d'),
            optional($visit->created_at)->format('H:i:s'),
            $visit->influencer_name,
            $visit->phone,
            $visit->place,
            $visit->influencer_type,
            $visit->visit_type,
            $visit->purpose,
            optional($visit->district)->name,
            $visit->lead_type,
            $visit->current_project,
            $visit->upcoming_project,
            is_array($visit->steel_used) ? implode(', ', $visit->steel_used) : '',
            $visit->total_deal_volume,              // TOTAL DEAL VOLUME (FROM DB)
            $totalOrderedQty,                       // TOTAL ORDERED QTY
            $totalOrderAmount,                      // TOTAL ORDER AMOUNT
            $balanceQty,                            // BALANCE QTY

            $visit->status,

            $visit->status === 'Follow Up' ? optional($visit->follow_up_date)->format('Y-m-d') : '',
            $visit->status === 'Follow Up' ? optional($visit->followUps->first())->reason : '',

            $visit->status === 'Lost' ? $visit->lost_volume : '',
            $visit->status === 'Lost' ? $visit->lost_to_competitor : '',
            $visit->status === 'Lost' ? $visit->reason_for_lost : '',
$visit->status === 'Won' ? optional(optional($order)->dealer)->dealer_name : '',
     //       $visit->status === 'Won' ? optional($order->dealer)->dealer_name : '',
     //       $visit->status === 'Won' ? optional($order->paymentTerm)->name : '',
    //        $visit->status === 'Won' ? $order->credit_days : '',
    //        $visit->status === 'Won' ? $order->total_amount : '',
$visit->status === 'Won' ? optional(optional($order)->paymentTerm)->name : '',
$visit->status === 'Won' ? optional($order)->credit_days : '',
$visit->status === 'Won' ? optional($order)->total_amount : '',
            $productSummary,
            optional($visit->createdBy)->name ?? '',
	    optional($visit->created_at)->format('Y-m-d H:i:s'),
	    optional($visit->updated_at)->format('Y-m-d H:i:s'),

        ];
    }
}
