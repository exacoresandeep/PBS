@extends('layouts.app')

@section('content')

<style>
    /* #ordersTable tbody tr {
        cursor: move;
    }

    #ordersTable tbody tr.ui-sortable-helper {
        display: table;
        background: #eaf2ff;
    }

    #ordersTable tbody tr.ui-sortable-placeholder {
        visibility: visible !important;
        background: #dbeafe;
        height: 45px;
    } */
    #ordersTable tbody tr {
        cursor: grab;
    }

    #ordersTable tbody tr:active {
        cursor: grabbing;
    }

    #ordersTable tbody tr.ui-sortable-helper {
        display: table;
        background: #eaf2ff;
        opacity: 0.9;
    }

    #ordersTable tbody tr.ui-sortable-placeholder {
        visibility: visible !important;
        background: #dbeafe;
        height: 45px;
    }
</style>
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
                <button type="button" id="btnExport" class="btn btn-success">
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

<div class="modal fade" id="orderModal" tabindex="-1"
     aria-labelledby="orderModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">

            <!-- Header -->
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="orderModalLabel">
                        Update Order Quantity
                    </h5>
                    <small class="">
                        Review items and update quantities
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Order Summary -->
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body py-3">
                        <div class="row text-center">

                            <div class="col-md-4 border-end">
                                <small class="text-muted d-block">Order ID</small>
                                <h6 class="fw-bold mb-0" id="modalOrderId">
                                    OD1009876
                                </h6>
                            </div>

                            <div class="col-md-4 border-end">
                                <small class="text-muted d-block">
                                    Original Quantity
                                </small>
                                <h5 class="fw-bold mb-0" id="originalQuantity">
                                    10.00
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    Updated Quantity
                                </small>
                                <h5 class="fw-bold text-primary mb-0"
                                    id="updatedQuantity">
                                    10.00
                                </h5>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Quantity Change -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">
                        <i class="fa fa-list-ul me-1"></i>
                        Product Details
                    </h6>

                    <span class="badge bg-info text-dark" id="quantityDifference">
                        Change: 0.00
                    </span>
                </div>

                <!-- Items Table -->
                <div class="table-responsive border rounded">
                    <input type="hidden" id="currentOrderId">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="70" class="text-center">SI No.</th>
                                <th>Product Type</th>
                                <th width="180" class="text-center">
                                    Quantity
                                </th>
                            </tr>
                        </thead>

                        <tbody id="rows">
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No items loaded
                                </td>
                            </tr>
                        </tbody>

                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">
                                    Total Quantity
                                </th>
                                <th class="text-end">
                                    <span id="tableTotal">10.00</span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-warning py-2 mt-3 mb-0">
                    <i class="fa fa-info-circle me-1"></i>
                    Please verify the updated quantities before submitting.
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="submitBtn">
                    <i class="fa fa-save me-1"></i>
                    Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>
<script>
$(document).ready(function () {

    $(document).on('input', '.qty-input', function () {

        let total = 0;

        $('.qty-input').each(function () {
            total += parseFloat($(this).val()) || 0;
        });

        let original = parseFloat($('#originalQuantity').text()) || 0;
        let difference = total - original;

        $('#updatedQuantity').text(total.toFixed(2));
        $('#tableTotal').text(total.toFixed(2));

        $('#quantityDifference').text(
            'Change: ' +
            (difference > 0 ? '+' : '') +
            difference.toFixed(2)
        );

    });

    
    $('#submitBtn').on('click', function () {

        let orderId = $('#currentOrderId').val();
        let details = [];
        let valid = true;

        // Collect and validate individual product quantities
        $('.qty-input').each(function () {

            let quantity = $(this).val();
            let orderItemId = $(this).data('item-id');
            let detailIndex = $(this).data('detail-index');

            if (
                quantity === '' ||
                !Number.isFinite(Number(quantity)) ||
                Number(quantity) < 0 ||
                orderItemId === undefined ||
                detailIndex === undefined
            ) {
                $(this).addClass('is-invalid');
                valid = false;
                return;
            }

            $(this).removeClass('is-invalid');

            details.push({
                order_item_id: orderItemId,
                detail_index: detailIndex,
                quantity: Number(quantity)
            });
        });

        if (!orderId) {
            Swal.fire('Error!', 'Order ID not found.', 'error');
            return;
        }

        if (!valid) {
            Swal.fire(
                'Invalid Quantity',
                'Please enter valid quantities for all items.',
                'warning'
            );
            return;
        }

        if (details.length === 0) {
            Swal.fire('No Items', 'No order items found.', 'warning');
            return;
        }

        // Confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to change the order quantity?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            let button = $('#submitBtn');

            button.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: "{{ url('operations/orders/salesorders') }}/" + orderId + "/items",
                type: "PUT",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    details: details
                },

                success: function (response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message || 'Order quantities updated successfully.',
                        confirmButtonColor: '#0d6efd'
                    }).then(() => {

                        bootstrap.Modal.getOrCreateInstance(
                            document.getElementById('orderModal')
                        ).hide();

                        $('#ordersTable').DataTable().ajax.reload(null, false);
                    });
                },

                error: function (xhr) {

                    let message = xhr.responseJSON?.message
                        || 'Unable to update order quantities.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors)
                            .flat()
                            .join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed!',
                        text: message,
                        confirmButtonColor: '#dc3545'
                    });

                    console.error(xhr.responseJSON);
                },

                complete: function () {

                    button.prop('disabled', false)
                        .html('<i class="fa fa-save me-1"></i> Save Changes');
                }
            });

        });

    });


    $(document).on('click', '.view-order', function () {

        let orderId = $(this).data('id');
        $('#currentOrderId').val(orderId);
        $('#modalOrderId').text('Loading...');
        $('#originalQuantity').text('0.00');
        $('#updatedQuantity').text('0.00');
        $('#tableTotal').text('0.00');
        $('#quantityDifference').text('Change: 0.00');

        $('#rows').html(`
            <tr>
                <td colspan="3" class="text-center">
                    <span class="spinner-border spinner-border-sm"></span>
                    Loading items...
                </td>
            </tr>
        `);

        let modal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById('orderModal')
        );

        modal.show();

        $.ajax({
            url: "{{ url('operations/orders/salesorders') }}/" + orderId + "/items",
            type: "GET",

            
            success: function (response) {

                $('#modalOrderId').text(response.order_number);

                let originalTotal = parseFloat(response.total_quantity) || 0;

                $('#originalQuantity').text(originalTotal.toFixed(2));
                $('#updatedQuantity').text(originalTotal.toFixed(2));
                $('#tableTotal').text(originalTotal.toFixed(2));

                let rows = '';

                if (!response.items || response.items.length === 0) {

                    rows = `
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No items found
                            </td>
                        </tr>
                    `;

                } else {

                    $.each(response.items, function (index, item) {

                        rows += `
                            <tr>
                                <td class="text-center">
                                    ${String(index + 1).padStart(2, '0')}
                                </td>

                                <td class="fw-semibold product-name"></td>

                                <td>
                                    <input type="number"
                                        class="form-control form-control-sm qty-input text-end"
                                        data-item-id="${item.order_item_id}"
                                        data-detail-index="${item.detail_index}"
                                        step="0.01"
                                        min="0"
                                        value="${parseFloat(item.quantity).toFixed(2)}">
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#rows').html(rows);

                // Safely populate product names
                $('#rows tr').each(function (index) {
                    if (response.items && response.items[index]) {
                        $(this).find('.product-name')
                            .text(response.items[index].product_name || 'N/A');
                    }
                });
            },

            error: function (xhr) {

                $('#rows').html(`
                    <tr>
                        <td colspan="3" class="text-center text-danger">
                            Unable to load order items.
                        </td>
                    </tr>
                `);

                console.error(xhr.responseText);
            }
        });

    });
    if (!$("#ordersTable").length) return;

    var table = $("#ordersTable").DataTable({
        processing: true,
        serverSide: true,

        // Database ordering will control the order.
        ordering: false,

        ajax: {
            url: "{{ route('operations.salesorders.data') }}",
            data: function (d) {
                d.delivery_date = $("#filterDeliveryDate").val();
                d.vehicle_type  = $("#filterVehicleType").val();
                d.destination   = $("#filterDestination").val();
            }
        },

        columns: [
            { data: "date",            name: "created_at" },
            { data: "rate",            orderable: false, searchable: false },
            { data: "delivery_date",   name: "delivery_date" },
            { data: "vehicle_type",    orderable: false, searchable: false },
            { data: "remarks",         orderable: false, searchable: false },
            { data: "order_id",        orderable: false, searchable: false },
            { data: "dealer_customer", orderable: false, searchable: false },
            { data: "destination",     orderable: false, searchable: false },
            { data: "vehicle_number",  name: "vehicle_number" },
            { data: "driver_number",   orderable: false, searchable: false },
            { data: "total_quantity",  orderable: false, searchable: false },
            { data: "action",          orderable: false, searchable: false }
        ],

        pageLength: 10,

        language: {
            info: "Show _START_ to _END_ of _TOTAL_ Schemes",
            infoEmpty: "Show 0 of 0 Schemes",
            infoFiltered: ""
        },

        // Store the actual order ID in each table row.
        createdRow: function (row, data) {
            $(row).attr('data-order-id', data.id);
        },

        // Enable drag and drop after every DataTable reload.
        drawCallback: function () {

            let api = this.api();
            let tbody = $('#ordersTable tbody');

            // Check whether jQuery UI Sortable is loaded.
            if (typeof $.fn.sortable !== 'function') {
                console.error('jQuery UI Sortable is not loaded.');
                return;
            }

            if (tbody.hasClass('ui-sortable')) {
                tbody.sortable('destroy');
            }

            let deliveryDate = $('#filterDeliveryDate').val();

            // Delivery date is mandatory for rearranging orders.
            if (!deliveryDate) {
                 Swal.fire({
                    icon: 'info',
                    title: 'Select Delivery Date',
                    text: 'Please select a delivery date to enable drag and drop.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (
                $('#filterVehicleType').val() ||
                $('#filterDestination').val() ||
                api.search()
            ) {
                return;
            }

            tbody.sortable({
                items: '> tr:not(.dataTables_empty)',
                cursor: 'move',
                axis: 'y',
                tolerance: 'pointer',
                opacity: 0.8,
                placeholder: 'ui-sortable-placeholder',

                helper: function (e, tr) {

                    let originals = tr.children();
                    let helper = tr.clone();

                    helper.children().each(function (index) {
                        $(this).width(originals.eq(index).width());
                    });

                    return helper;
                },

                update: function () {
                    let orderIds = [];

                    tbody.find('tr').each(function () {
                        let id = $(this).attr('data-order-id');

                        if (id) {
                            orderIds.push(id);
                        }
                    });

                    if (!orderIds.length) {
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm Order Priority',
                        text: 'Are you sure you want to update the delivery order priority?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Update',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {

                        if (result.isConfirmed) {

                            $.ajax({
                                url: "{{ route('operations.salesorders.savePriority') }}",
                                type: "POST",
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    delivery_date: deliveryDate,
                                    order_ids: orderIds
                                },

                                success: function (response) {

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Updated!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    table.ajax.reload(null, false);
                                },

                                error: function (xhr) {

                                    let message = xhr.responseJSON?.message
                                        || 'Unable to update order priority.';

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Update Failed',
                                        text: message
                                    });

                                    table.ajax.reload(null, false);
                                }
                            });

                        } else {
                            // Restore the original order when cancelled.
                            table.ajax.reload(null, false);
                        }
                    });
                }

            }).disableSelection();
        }
    });


    // Delivery date filter
    $('#filterDeliveryDate').on('change', function () {

        if ($(this).val()) {
            // Display all orders for the selected delivery date.
            table.page.len(-1).draw();
        } else {
            table.page.len(10).draw();
        }
    });


    // Other filters
    $('#filterVehicleType, #filterDestination').on('change', function () {
        table.ajax.reload();
    });


    // Export
    $("#btnExport").on("click", function () {

        var params = $.param({
            delivery_date: $("#filterDeliveryDate").val(),
            vehicle_type: $("#filterVehicleType").val(),
            destination: $("#filterDestination").val()
        });

        window.location.href =
            "{{ route('operations.orders.exportSalesOrders') }}?" + params;
    });
});
</script>
@endsection