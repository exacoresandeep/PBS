<?php

namespace App\Exports;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllLeadExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnWidths,
    WithStyles
{
    protected $request;

    protected int $rowNumber = 0;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    */

    public function query()
    {
        $request = $this->request;

        $query = Lead::query()
            ->select([
                'id',
                'created_at',
                'updated_at',
                'customer_type',
                'customer_name',
                'phone',
                'city',
                'location',
                'address',
                'district_id',
                'created_by',
                'status',
                'lead_source',
                'source_name',
                'lead_score',
                'type_of_visit',
                'stage_of_construction',
                'construction_type',
                'total_volume',
                'total_quantity',
                'lost_volume',
                'reason_for_lost',
                'lost_to_competitor',
            ])

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            ->with([
                'customerType:id,name',

                'district:id,name',

                'createdBy:id,employee_code,name',

                'followUps',

                'orders' => function ($query) {
                    $query->orderByDesc('created_at');
                },

                'orders.dealer',

                'orders.paymentTerm',

                'orders.orderItems',
            ]);

        /*
        |--------------------------------------------------------------------------
        | From Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $query->where(
                'created_at',
                '>=',
                $request->from_date . ' 00:00:00'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('to_date')) {

            $query->where(
                'created_at',
                '<=',
                $request->to_date . ' 23:59:59'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | District
        |--------------------------------------------------------------------------
        */

        if ($request->filled('district')) {

            $query->where(
                'district_id',
                $request->district
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('customer_type')) {

            $query->where(
                'customer_type',
                $request->customer_type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        if ($request->filled('employee_id')) {

            $query->where(
                'created_by',
                $request->employee_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Order
        |--------------------------------------------------------------------------
        */

        return $query->orderBy('id');
    }


    /*
    |--------------------------------------------------------------------------
    | Headings
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [

            // Basic Information

            'Sl No.',
            'Lead ID',
            'Created Date',
            'Created Time',
            'Customer Type',
            'Customer Name',
            'Phone',
            'City',
            'Location',
            'Address',
            'District',
            'Employee Name',
            'Employee Code',
            'Status',

            // Lead Information

            'Lead Source',
            'Source Name',
            'Lead Score',
            'Type of Visit',
            'Stage of Construction',
            'Construction Type',
            'Total Deal Volume',
            'Total Quantity',

            // Follow Up

            'Follow Up Date',
            'Follow Up Reason',
            'Follow Up Remarks',
            'Follow Up Count',

            // Lost Information

            'Lost Deal Volume',
            'Reason for Lost',
            'Lost To Competitor',

            // Order Information

            'Approval Status',
            'Dealer Code',
            'Dealer Name',
            'Buyer Type',
            'Payment Type',
            'Order Status',
            'Billing Date',
            'Invoice Number',
            'Invoice Quantity',
            'Invoice Total',

            // Product Information

            'Product Types',
            'Product Quantities',
            'Product Rates',
            'Order Total Quantity',
            'Order Total Amount',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Map
    |--------------------------------------------------------------------------
    */

    public function map($lead): array
    {
        $this->rowNumber++;

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        $customerType = $lead->customerType;

        /*
        |--------------------------------------------------------------------------
        | Follow Ups
        |--------------------------------------------------------------------------
        */

        $followUps = $lead->followUps ?? collect();

        $followUpDates = [];

        $followUpReasons = [];

        $followUpRemarks = [];

        foreach ($followUps as $followUp) {

            /*
            | Date
            */

            if (!empty($followUp->follow_up_date)) {

                try {

                    $followUpDates[] = Carbon::parse(
                        $followUp->follow_up_date
                    )->format('d/m/Y');

                } catch (\Exception $e) {

                    $followUpDates[] = $followUp->follow_up_date;
                }
            }


            /*
            | Reason
            */

            if (!empty($followUp->reason)) {

                $followUpReasons[] = $followUp->reason;
            }


            /*
            | Remarks
            */

            if (!empty($followUp->notification_status)) {

                $followUpRemarks[] =
                    $followUp->notification_status;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Latest Order
        |--------------------------------------------------------------------------
        */

        $order = $lead->orders
            ->sortByDesc('created_at')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Approval Status
        |--------------------------------------------------------------------------
        */

        $approvalStatus = 'NA';

        if ($lead->status === 'Won' && $order) {

            $orderStatus = $order->status;

            if (in_array($orderStatus, [
                'Pending',
                'Accepted'
            ])) {

                $approvalStatus = 'Pending';

            } elseif (in_array($orderStatus, [
                'Rejected',
                'Accounts Rejected'
            ])) {

                $approvalStatus = 'Rejected';

            } elseif (in_array($orderStatus, [
                'Dispatched',
                'In Transit',
                'Delivered',
                'Accounts Approved'
            ])) {

                $approvalStatus = 'Approved';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $productTypes = [];

        $productQuantities = [];

        $productRates = [];

        $orderTotalQuantity = 0;

        $orderTotalAmount = 0;


        if ($order) {

            foreach ($order->orderItems ?? [] as $orderItem) {

                /*
                |--------------------------------------------------------------------------
                | product_details is JSON / array
                |--------------------------------------------------------------------------
                */

                $productDetails = $orderItem->product_details ?? [];

                /*
                | If JSON is returned as string
                */

                if (is_string($productDetails)) {

                    $decoded = json_decode(
                        $productDetails,
                        true
                    );

                    $productDetails = is_array($decoded)
                        ? $decoded
                        : [];
                }


                if (!is_array($productDetails)) {
                    continue;
                }


                foreach ($productDetails as $product) {

                    if (!is_array($product)) {
                        continue;
                    }

                    $typeName =
                        $product['type_name'] ?? '-';

                    $quantity =
                        (float) ($product['quantity'] ?? 0);

                    $rate =
                        (float) ($product['rate'] ?? 0);


                    /*
                    | Product Type
                    */

                    $productTypes[] = $typeName;


                    /*
                    | Quantity
                    */

                    $productQuantities[] =
                        number_format(
                            $quantity,
                            2,
                            '.',
                            ''
                        );


                    /*
                    | Rate
                    */

                    $productRates[] =
                        number_format(
                            $rate,
                            2,
                            '.',
                            ''
                        );


                    /*
                    | Totals
                    */

                    $orderTotalQuantity += $quantity;

                    $orderTotalAmount +=
                        $quantity * $rate;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        $createdDate = $lead->created_at
            ? $lead->created_at->format('d/m/Y')
            : '-';

        $createdTime = $lead->created_at
            ? $lead->created_at->format('h:i A')
            : '-';


        /*
        |--------------------------------------------------------------------------
        | Return Row
        |--------------------------------------------------------------------------
        */

        return [

            /*
            |--------------------------------------------------------------------------
            | Basic
            |--------------------------------------------------------------------------
            */

            $this->rowNumber,

            $lead->id,

            $createdDate,

            $createdTime,

            $customerType->name ?? '-',

            $lead->customer_name ?? '-',

            $lead->phone ?? '-',

            $lead->city ?? '-',

            $lead->location ?? '-',

            $lead->address ?? '-',

            $lead->district->name ?? '-',

            $lead->createdBy->name ?? '-',

            $lead->createdBy->employee_code ?? '-',

            $lead->status ?? '-',


            /*
            |--------------------------------------------------------------------------
            | Lead
            |--------------------------------------------------------------------------
            */

            $lead->lead_source ?? '-',

            $lead->source_name ?? '-',

            $lead->lead_score ?? '-',

            $lead->type_of_visit ?? '-',

            $lead->stage_of_construction ?? '-',

            $lead->construction_type ?? '-',

            $lead->total_volume ?? '-',

            $lead->total_quantity ?? '-',


            /*
            |--------------------------------------------------------------------------
            | Follow Up
            |--------------------------------------------------------------------------
            */

            !empty($followUpDates)
                ? implode(', ', $followUpDates)
                : '-',

            !empty($followUpReasons)
                ? implode(' | ', $followUpReasons)
                : '-',

            !empty($followUpRemarks)
                ? implode(' | ', $followUpRemarks)
                : '-',

            $followUps->count(),


            /*
            |--------------------------------------------------------------------------
            | Lost
            |--------------------------------------------------------------------------
            */

            $lead->lost_volume ?? '-',

            $lead->reason_for_lost ?? '-',

            $lead->lost_to_competitor ?? '-',


            /*
            |--------------------------------------------------------------------------
            | Order
            |--------------------------------------------------------------------------
            */

            $approvalStatus,

            $order?->dealer?->dealer_code ?? '-',

            $order?->dealer?->dealer_name ?? '-',

            $customerType->name ?? '-',

            $order?->paymentTerm?->name ?? '-',

            $order?->status ?? '-',

            $order?->billing_date ?? '-',

            $order?->invoice_number ?? '-',

            $order?->invoice_quantity ?? '-',

            $order?->invoice_total ?? '-',


            /*
            |--------------------------------------------------------------------------
            | Products
            |--------------------------------------------------------------------------
            */

            !empty($productTypes)
                ? implode(', ', $productTypes)
                : '-',

            !empty($productQuantities)
                ? implode(', ', $productQuantities)
                : '-',

            !empty($productRates)
                ? implode(', ', $productRates)
                : '-',

            $orderTotalQuantity > 0
                ? number_format(
                    $orderTotalQuantity,
                    2,
                    '.',
                    ''
                )
                : '-',

            $orderTotalAmount > 0
                ? number_format(
                    $orderTotalAmount,
                    2,
                    '.',
                    ''
                )
                : '-',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Column Widths
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [

            'A' => 8,
            'B' => 12,
            'C' => 15,
            'D' => 14,
            'E' => 18,
            'F' => 25,
            'G' => 16,
            'H' => 16,
            'I' => 20,
            'J' => 30,
            'K' => 18,
            'L' => 25,
            'M' => 18,
            'N' => 15,

            'O' => 18,
            'P' => 20,
            'Q' => 15,
            'R' => 18,
            'S' => 25,
            'T' => 35,
            'U' => 20,
            'V' => 18,

            'W' => 18,
            'X' => 30,
            'Y' => 30,
            'Z' => 15,

            'AA' => 20,
            'AB' => 30,
            'AC' => 25,

            'AD' => 18,
            'AE' => 18,
            'AF' => 25,
            'AG' => 18,
            'AH' => 18,
            'AI' => 18,
            'AJ' => 18,
            'AK' => 20,
            'AL' => 18,
            'AM' => 18,

            'AN' => 30,
            'AO' => 25,
            'AP' => 25,
            'AQ' => 20,
            'AR' => 22,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Styles
    |--------------------------------------------------------------------------
    */

    public function styles(Worksheet $sheet)
    {
        return [

            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],

        ];
    }
}