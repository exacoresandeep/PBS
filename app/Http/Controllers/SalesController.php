<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Dealer;
use App\Models\ProductType;
use App\Models\Price;
use App\Models\OutstandingNew;
use App\Helpers\ProductHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class salesController extends Controller
{
    public function index()
    {
        return view('sales.order-request.index');
    }
    
    public function orderList(Request $request)
    {
        $productID = ProductHelper::getSelectedProductId();
        $statusFilter = $request->get('status');       

        
	$dealerIdsWithDue = OutstandingNew::where('due_balance', '>', 0)
                ->pluck('dealer_id');

        $dueDealerData = DB::table('outstanding_payments as op1')
                     ->select('op1.dealer_id', DB::raw('MAX(op1.due_date) as due_date'))
                      ->join('orders as o', 'o.id', '=', 'op1.order_id')
		      ->where('o.product_id', $productID)
		      ->whereIn('op1.dealer_id', $dealerIdsWithDue)
                    ->whereNotNull('op1.due_date')
                    ->where('op1.status', 'open')
                    ->whereDate('op1.due_date', '<', Carbon::now()->subDays(25))
                    ->groupBy('op1.dealer_id')//            ->distinct()
                    ->get();
        $dueDealerMap = $dueDealerData->keyBy('dealer_id'); // for due_date
        $dueDealerIds = $dueDealerData->pluck('dealer_id'); // for filtering
        $orders = Order::select([
                'orders.id',
                'orders.dealer_id',
                'orders.created_by_dealer',
                'orders.created_by',
                'orders.dealer_flag_order',
                'orders.send_for_approval',
                'orders.send_for_approval_by',
                'orders.created_at',
                'orders.total_amount',
                'orders.status'
            ])
            
            ->with([
                'orderItems:id,order_id,product_id',
                'dealer:id,dealer_name,dealer_code',
                'dealers:id,dealer_name,dealer_code',
                'createdBy:id,name,employee_code,employee_type_id',
                'createdBy.employeeType:id,type_name',
                'sendForApprovalBy:id,name,employee_code'
            ])
    ->whereDate('created_at', '>=', now()->subDays(60))
    		->whereHas('orderItems', function ($q) use ($productID) {
                $q->where('product_id', $productID);
            })
            ->where(function ($query) use ($dueDealerIds)  {
                $query->where(function ($subQuery) use ($dueDealerIds)  {
                    $subQuery->where('dealer_flag_order', '1')
			     ->whereNotIn('status', ['Pending', 'Rejected'])
		     	->where(function ($q) use ($dueDealerIds) {
                            $q->whereNotIn('orders.dealer_id', $dueDealerIds)
                            ->orWhere(function ($inner) use ($dueDealerIds) {
                                $inner->whereIn('orders.dealer_id', $dueDealerIds)
                                        ->where('status', 'Accepted');
                            });
                        })
                        ->where(function ($approvalQuery) {
                            $approvalQuery->where('send_for_approval', '0')
                                        ->orWhere('send_for_approval', '1');
                        });
                })
                ->orWhere(function ($subQuery) use ($dueDealerIds) {
                    $subQuery->whereHas('createdBy', function ($employeeQuery) {
                        $employeeQuery->whereIn('employee_type_id', [2, 3, 4, 5,7]);
		    })
			     ->where(function ($q) use ($dueDealerIds) {
                            $q->whereNotIn('orders.dealer_id', $dueDealerIds)
                            ->orWhere(function ($inner) use ($dueDealerIds) {
                                $inner->whereIn('orders.dealer_id', $dueDealerIds)
                                        ->where('status', 'Accepted');
                            });
                        })
                    ->where('dealer_flag_order', '!=', '1')
                    ->where(function ($sourceQuery) {
                        $sourceQuery->whereNull('source')
                                    ->orWhereNotIn('source', ['lead_won', 'influencer_won']);
                    });
                });
            })
            ->orderByRaw("
                CASE
                    WHEN order_approved = '1' THEN accepted_time
                    WHEN order_approved = '2' THEN rejected_time
                    ELSE created_at
                END DESC
            ");

        if ($statusFilter === 'Approved') {
            $orders->where('order_approved', '1');
        } elseif ($statusFilter === 'Rejected') {
            $orders->where('order_approved', '2');
        } elseif ($statusFilter === 'Pending') {
            $orders->where(function ($query) {
                $query->whereNull('order_approved')->orWhereNotIn('order_approved', ['1', '2']);
            });
        }         

	return DataTables::of($orders)
		->filter(function ($query) use ($request) {
                if ($search = $request->get('search')['value'] ?? false) {

                    $query->where(function ($q) use ($search) {
                        $q->where('orders.id', 'LIKE', "%{$search}%")
                        ->orWhere('orders.total_amount', 'LIKE', "%{$search}%")
                        ->orWhere('orders.created_at', 'LIKE', "%{$search}%")
                        ->orWhereHas('dealer', function ($d) use ($search) {
                            $d->where('dealer_name', 'LIKE', "%{$search}%")
                                ->orWhere('dealer_code', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('dealers', function ($d) use ($search) {
                            $d->where('dealer_name', 'LIKE', "%{$search}%")
                                ->orWhere('dealer_code', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('createdBy', function ($u) use ($search) {
                            $u->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('employee_code', 'LIKE', "%{$search}%");
                        });
                    });
                }
            })
            ->addIndexColumn()
            ->addColumn('date', function ($order) {
                if ($order->order_approved == 1 && $order->accepted_time) {
                    return $order->accepted_time->format('d/m/Y h:i A');
                } elseif ($order->order_approved == 2 && $order->rejected_time) {
                    return $order->rejected_time->format('d/m/Y h:i A');
                } else {
                    return $order->created_at->format('d/m/Y h:i A');
                }
            })
            ->addColumn('order_id', fn($order) => 'OD00' . $order->id)
            ->addColumn('dealer_name', fn($order) =>
                $order->created_by_dealer ? ($order->dealers?->dealer_name ?? 'N/A') : ($order->dealer?->dealer_name ?? 'N/A')
            )
            ->addColumn('dealer_code', fn($order) =>
                $order->created_by_dealer ? ($order->dealers?->dealer_code ?? 'N/A') : ($order->dealer?->dealer_code ?? 'N/A')
            )
            ->addColumn('employee_type', function ($order) {
                if ($order->dealer_flag_order == "1") {
                    if ($order->send_for_approval == "0") return 'Area Sales Officer';
                    if ($order->send_for_approval == "1" && !empty($order->send_for_approval_by)) return 'Sales Manager';
                    return 'N/A';
                }
                return $order->createdBy?->employeeType?->type_name ?? 'N/A';
            })
            ->addColumn('employee_name_code', function ($order) {
                if ($order->dealer_flag_order == '1') {
                    if ($order->send_for_approval == 1 && $order->sendForApprovalBy) {
                        return 'Dhanesh Kamath - PS007';
                    }

                    if ($order->send_for_approval == 0 && $order->dealers) {
                        $dealerId = $order->dealers->id;

                        $assignment = \App\Models\DealerRouteAssignment::where('dealer_id', $dealerId)
                            ->where('employee_type_id', 2) // ASO
                            ->with('employee')
                            ->first();

                        if ($assignment && $assignment->employee) {
                            return $assignment->employee->name . ' - ' . $assignment->employee->employee_code;
                        }
                    }

                    return $order->dealer?->dealer_name ?? '-';
                }

                if ($order->createdBy) {
                    return $order->createdBy->name . ' - ' . $order->createdBy->employee_code;
                }

                return 'N/A';
            })
            ->addColumn('amount', fn($order) => (float) ($order->total_amount))
            ->addColumn('status', function ($order) {
       switch ($order->status) {

    case 'Pending':
        return '<span class="badge bg-warning">Pending</span>';

    case 'Accepted':
        return '<span class="badge bg-primary">Accepted</span>';

    case 'Approved':
        return '<span class="badge bg-success">Approved</span>';

    case 'Rejected':
        return '<span class="badge bg-danger">Rejected</span>';

    case 'Dispatched':
        return '<span class="badge bg-info">Dispatched</span>';

    case 'In Transit':
        return '<span class="badge bg-secondary">In Transit</span>';

    case 'Delivered':
        return '<span class="badge bg-success">Delivered</span>';

    case 'Accounts Approved':
        return '<span class="badge bg-success">Accounts Approved</span>';

    case 'Accounts Rejected':
        return '<span class="badge bg-danger">Accounts Rejected</span>';

    default:
        return '<span class="badge bg-dark">Unknown</span>';
}
            })
            ->addColumn('action', fn($order) =>
                '<button class="btn btn-info btn-sm view-order" data-id="' . $order->id . '" title="View">
                    <i class="fa fa-eye"></i>
                </button>'
            )
            ->rawColumns(['status', 'action'])
            ->make(true);
    }


    public function viewOrder($id)
    {
        try {
            $order = Order::with([
                'dealer',
                'createdBy.employeeType',
                'sendForApprovalBy.employeeType',
                'orderItems',
                'orderType',
                'paymentTerm'
            ])->find($id);

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found!'], 404);
            }

            $dealerId = $order->dealer_flag_order == 1 ? $order->created_by_dealer : $order->dealer_id;
            $dealer = Dealer::find($dealerId);

            $totalOutstandingAmount = OutstandingNew::where('dealer_id', $dealerId)->sum('outstanding_amount');

            $employeeType = '-';
            $employeeNameCode = '-';
            $dealerName = $dealer?->dealer_name ?? 'N/A';
            $dealerCode = $dealer?->dealer_code ?? 'N/A';
            $dealerPhone = $dealer?->phone ?? 'N/A';
            $dealerAddress = $dealer?->address ?? 'N/A';

            if ($order->dealer_flag_order == 1) {
                if ($order->send_for_approval == 0) {
                    $employeeType = 'Area Sales Officer';

                    $dealerRoute = \App\Models\DealerRouteAssignment::where('dealer_id', $dealer->id)->first();
                    if ($dealerRoute) {
                        $assignRoute = \App\Models\AssignRoute::with('employee')->find($dealerRoute->assign_route_id);
                        if ($assignRoute && $assignRoute->employee) {
                            $employee = $assignRoute->employee;
                            $employeeNameCode = $employee->name . ' - ' . $employee->employee_code;
                        }
                    }
                } elseif ($order->send_for_approval == 1 && $order->send_for_approval_by) {
                    $employeeType = 'Sales Manager';
                    $employee = $order->sendForApprovalBy;
                    $employeeNameCode = $employee ? ($employee->name . ' - ' . $employee->employee_code) : 'N/A';
                }
            } else {
                $employee = $order->createdBy;
                $employeeType = $employee?->employeeType?->type_name ?? 'N/A';
                $employeeNameCode = $employee ? ($employee->name . ' - ' . $employee->employee_code) : 'N/A';
            }

            $orderCreatedAt = $order->created_at->format('Y-m-d');

            $orderItems = $order->orderItems->flatMap(function ($item) use ($orderCreatedAt) {
                $product = $item->product;

                return collect($item->product_details)->map(function ($detail) use ($product, $orderCreatedAt) {
                    $productName = $product->product_name;// 'TATA TISCON';
                    $productType = isset($detail['product_type_id'])
                        ? ProductType::find($detail['product_type_id'])
                        : null;

                    $typeName = $productType?->type_name ?? 'N/A';
                    $quantity = (float) ($detail['quantity'] ?? 0);
                    $rate = (float) ($detail['rate'] ?? 0);
            $pieces=$detail['pieces'] ?? 0;
                    $tonnage=$detail['tonnage'] ?? 0;
                    $price = Price::where('product_id', $product->id)
                        ->where('product_type_id', $detail['product_type_id'])
                        ->where('start_date', '<=', $orderCreatedAt)
                        ->where('end_date', '>=', $orderCreatedAt)
                        ->latest()
                        ->first();

                    return [
                        'product_name' => $productName,
                        'type_name' => $typeName,
			'quantity' => $quantity,
			'pieces' => $pieces,
                        'tonnage' => $tonnage,
                        'rate' => $rate,
                        'adp_price' => (float) ($price?->advance_dealer_price ?? 0),
                        'dp_price' => (float) ($price?->dealer_price ?? 0),
                    ];
                });
            });

            $orderData = [
                'order_id' => 'OD00' . $order->id,
                'date' => $order->created_at->format('d/m/Y'),
                'employee_type' => $employeeType,
                'employee_name_code' => $employeeNameCode,
                'dealer_name' => $dealerName,
                'dealer_code' => $dealerCode,
                'dealer_phone' => $dealerPhone,
		'dealer_address' => $dealerAddress,
		'product_id' => $order->product_id,
                'order_type' => $order->orderType?->name ?? 'N/A',
                'payment_type' => $order->paymentTerm?->name ?? 'N/A',
                'billing_date' => $order->billing_date ?? 'N/A',
                'status_badge' => $order->status,
		'scheme' => $order->scheme,
		'credit_days' => $order->credit_days,
                'instructions' => $order->additional_information,
                'reason_for_rejection' => $order->reason_for_rejection,
                'remarks' => $order->order_remarks,
                'order_approved' => $order->order_approved,
                'payment_term' => $order->order_payment_terms,
                'order_status' => match ($order->order_approved) {
                    '1' => '<span class="badge bg-success">Approved</span>',
                    '2' => '<span class="badge bg-danger">Rejected</span>',
                    default => '<span class="badge bg-warning">Pending</span>',
                },
                'order_items' => $orderItems,
                'total_outstanding' => (float) $totalOutstandingAmount ?? 0.00,
            ];

            $decodedAttachment = is_string($order->attachment)
                ? json_decode($order->attachment, true)
                : $order->attachment;

            $orderData['attachments'] = is_array($decodedAttachment) ? $decodedAttachment : [];

            return response()->json(['success' => true, 'order' => $orderData]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order details: ' . $e->getMessage(),
            ], 500);
        }
    }

   
   


    public function export(Request $request)
    {
        $statusFilter = $request->get('status');

        $orders = Order::with(['dealer', 'dealers', 'createdBy.employeeType'])
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('dealer_flag_order', '1')
                        ->where('status', 'Accepted')
                        ->where(function ($approvalQuery) {
                            $approvalQuery->where('send_for_approval', '0')
                                        ->orWhere('send_for_approval', '1');
                        });
                })
                ->orWhere(function ($subQuery) {
                    $subQuery->whereHas('createdBy', function ($employeeQuery) {
                        $employeeQuery->whereIn('employee_type_id', [2, 3, 4, 5]);
                    })->where('dealer_flag_order', '!=', '1')
                    ->where(function ($sourceQuery) {
                        $sourceQuery->whereNull('source')->orWhere('source', '!=', 'lead_won');
                    });
                });
            });

        if ($statusFilter === 'Approved') {
            $orders->where('order_approved', '1');
        } elseif ($statusFilter === 'Rejected') {
            $orders->where('order_approved', '2');
        } elseif ($statusFilter === 'Pending') {
            $orders->where(function ($query) {
                $query->whereNull('order_approved')->orWhereNotIn('order_approved', ['1', '2']);
            });
        }

        $data = $orders->latest()->get()->map(function ($order) {
            return [
                'Date' => $order->created_at->format('d/m/Y'),
                'Order ID' => 'OD00' . $order->id,
                'Order Type' => $order->orderType?->name ?? 'N/A',
                
                'Dealer Name' => $order->created_by_dealer ? $order->dealers?->dealer_name : $order->dealer?->dealer_name,
                'Dealer Code' => $order->created_by_dealer ? $order->dealers?->dealer_code : $order->dealer?->dealer_code,
                'Address' => $order->created_by_dealer ? $order->dealers?->address : $order->dealer?->address,

                'Employee Type' => $order->dealer_flag_order == 1 ? '-' : ($order->createdBy?->employeeType?->type_name ?? 'N/A'),
                'Employee Name - Code' => $order->dealer_flag_order == 1
                    ? ($order->dealer?->dealer_name ?? '-')
                    : ($order->createdBy->name ?? '-') . ' - ' . ($order->createdBy->employee_code ?? '-'),
                'Amount' => number_format($order->total_amount, 2),
                'Payment Type' => $order->paymentTerm?->name ?? 'N/A', 
                'Billing Date' => $order->billing_date ?? 'N/A', 
                'Scheme' => $order->scheme ?? 'N/A', 
                'Status' => match ($order->order_approved) {
                    '1' => 'Approved',
                    '2' => 'Rejected',
                    default => 'Pending',
                
                },
            ];
        });

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return [
                    'Date',
                    'Order ID',
                    'Order Type',
                    'Dealer Name',
                    'Dealer Code',
                    'Address',
                    'Employee Type',
                    'Employee Name - Code',
                    'Amount',
                    'Payment Type', 
                    'Billing Date',
                    'Scheme',   
                    'Status',
                ];
            }
        }, 'orders.xlsx');
    }

    

}
