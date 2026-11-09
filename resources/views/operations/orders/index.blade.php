@extends('layouts.app')

@section('content')
<div class="activity-sec">
    <div class="inner-header button-align mb-2">
        <h3>Sales Order Management</h3>
    </div>

    {{-- ---------- Filter bar ---------- --}}
    <div class="listing-sec">
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-2">
                <label class="form-label">Delivery Date</label>
                <input type="date" id="filterDeliveryDate" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Vehicle Type</label>
                <select id="filterVehicleType" class="form-select">
                    <option value="">- Select Vehicle Type -</option>
                    @foreach($vehicleTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->vehicle_type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Destination</label>
                <select id="filterDestination" class="form-select">
                    <option value="">- Select Destination -</option>
                    @foreach($destinations as $dest)
                        <option value="{{ $dest->id }}">{{ $dest->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" id="btnExport" class="btn btn-outline-secondary">
                    <i class="fa fa-upload"></i> Export
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100" id="ordersTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Rate</th>
                        <th>Delivery Date</th>
                        <th>Vehicle Type</th>
                        <th>Remarks</th>
                        <th>Order ID</th>
                        <th>Dealer/ Customer</th>
                        <th>Destination</th>
                        <th>Vehicle Number</th>
                        <th>Driver Number</th>
                        <th>Total Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    if (!$("#ordersTable").length) return;

    var table = $("#ordersTable").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('operations.salesorders.index') }}",
            data: function (d) {
                d.delivery_date = $("#filterDeliveryDate").val();
                d.vehicle_type  = $("#filterVehicleType").val();
                d.destination   = $("#filterDestination").val();
            }
        },
        columns: [
            { data: "date",           name: "created_at" },
            { data: "rate",           name: "rate",           orderable: false },
            { data: "delivery_date",  name: "delivery_date" },
            { data: "vehicle_type",   name: "vehicle_type",   orderable: false },
            { data: "remarks",        name: "remarks",        orderable: false },
            { data: "order_id",       name: "id",             orderable: false },
            { data: "dealer_customer",name: "dealer_customer",orderable: false },
            { data: "destination",    name: "destination",    orderable: false },
            { data: "vehicle_number", name: "vehicle_number", orderable: false },
            { data: "driver_number",  name: "driver_number",  orderable: false },
            { data: "total_quantity", name: "total_quantity", orderable: false },
            { data: "action",         name: "action",         orderable: false, searchable: false }
        ],
        order: [[0, "desc"]],
        pageLength: 10,
        language: {
            info: "Show _START_ to _END_ of _TOTAL_ Schemes",
            infoEmpty: "Show 0 of 0 Schemes",
            infoFiltered: ""
        }
    });

    // Filters -> reload
    $("#filterDeliveryDate, #filterVehicleType, #filterDestination").on("change", function () {
        table.ajax.reload();
    });

    // Export with current filters
    $("#btnExport").on("click", function () {
        var params = $.param({
            delivery_date: $("#filterDeliveryDate").val(),
            vehicle_type:  $("#filterVehicleType").val(),
            destination:   $("#filterDestination").val()
        });
        window.location.href = "{{ route('operations.orders.exportSalesOrders') }}?" + params;
    });
});
</script>
@endsection