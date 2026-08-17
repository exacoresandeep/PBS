@extends('layouts.app')

@section('content')

<div class="activity-sec">

    <div class="inner-header">
        <h3>Lead Management</h3>
    </div>


    {{-- ================= FILTERS ================= --}}

    <div class="lead-filter-row row">
        {{-- Date --}}
        <div class="lead-filter col-2">

            <label> From Date</label>

            <input type="date"
                   id="lead_from_date"
                   class="form-control form-control-sm">

        </div>

        {{-- Date --}}
        <div class="lead-filter col-2">

            <label>To Date</label>

            <input type="date"
                   id="lead_to_date"
                   class="form-control form-control-sm">

        </div>


        {{-- District --}}
        <div class="lead-filter  col-2">

            <label>District</label>

            <select id="lead_district"
                    class="form-control form-control-sm">

                <option value="">
                    Select District
                </option>

                @foreach($districts as $district)

                    <option value="{{ $district->id }}">
                        {{ $district->name }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Customer Type --}}
        <div class="lead-filter  col-2">

            <label>Customer Type</label>

            <select id="customer_type"
                    class="form-control form-control-sm">

                <option value="">
                    Select Type
                </option>

                @foreach($customerTypes as $type)

                    <option value="{{ $type->id }}">
                        {{ $type->name }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Employee --}}
        <div class="lead-filter  col-2">

            <label>Employee Name</label>

            <select id="employee"
                    class="form-control form-control-sm">

                <option value="">
                    Select Employee
                </option>

                @foreach($employees as $employee)

                    <option value="{{ $employee->id }}">
                        {{ $employee->name }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Status --}}
        <div class="lead-filter  col-2">

            <label>Status</label>

            <select id="lead_status"
                    class="form-control form-control-sm">

                <option value="">
                    Select Status
                </option>

                <option value="Opened">
                    Open
                </option>

                <option value="Follow Up">
                    Follow - Up
                </option>

                <option value="Won">
                    Won
                </option>

                <option value="Lost">
                    Lost
                </option>

            </select>

        </div>

    </div>
    <hr>

    {{-- ================= TABLE ================= --}}

    <div class="listing-sec">

        <table id="leadTable"
               class="table table-bordered table-striped w-100">

            <thead>

                <tr>

                    <th>Sl No.</th>

                    <th>Date and Time</th>

                    <th>Customer Type</th>

                    <th>Customer Name</th>

                    <th>Location</th>

                    <th>District</th>

                    <th>Status</th>

                    <th>Approval Status</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>
            </tbody>

        </table>

    </div>

</div>
<div class="modal fade" id="leadViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body" id="leadViewContent">

                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i>
                    Loading...
                </div>

            </div>

        </div>

    </div>
</div>
@endsection


@section('scripts')

<script>
$(document).on('click', '.viewLead', function (e) {
    e.preventDefault();
    let leadId = $(this).data('id');
    $('#leadViewContent').html(`
        <div class="text-center py-5">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <div class="mt-2">Loading...</div>
        </div>
    `);
    let modal = new bootstrap.Modal(
        document.getElementById('leadViewModal')
    );
    modal.show();
    $.ajax({
        url: "{{ route('lead.view', ':id') }}".replace(':id', leadId),
        type: 'GET',
        success: function (response) {
            console.log(response);
            let lead = response.data ?? response;
            let statusBadge = '';
            if (lead.status === 'Opened') {
                statusBadge = `
                    <span class="badge bg-success-subtle text-success">
                        Open
                    </span>
                `;
            } else if (lead.status === 'Follow Up') {
                statusBadge = `
                    <span class="badge bg-warning-subtle text-warning">
                        Follow Up
                    </span>
                `;
            } else if (lead.status === 'Won') {
                statusBadge = `
                    <span class="badge bg-success">
                        Won
                    </span>
                `;
            } else if (lead.status === 'Lost') {
                statusBadge = `
                    <span class="badge bg-danger-subtle text-danger">
                        Lost
                    </span>
                `;
            } else {
                statusBadge = `
                    <span class="badge bg-secondary">
                        ${lead.status ?? '-'}
                    </span>
                `;
            }

            $('#leadViewContent').html(`
                <div class="card border-0">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Customer Type
                                    </label>
                                    <div class="fw-normal">
                                        ${lead.customer_type?.name ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Phone
                                    </label>
                                    <div>
                                        ${lead.phone ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Location
                                    </label>
                                    <div>
                                        ${lead.location ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        District
                                    </label>
                                    <div>
                                        ${lead.district?.name ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Employee Name
                                    </label>
                                    <div>
                                        ${lead.created_by?.name ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Status
                                    </label>
                                    <div>
                                        ${statusBadge}
                                    </div>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Customer Name
                                    </label>
                                    <div>
                                        ${lead.customer_name ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        City
                                    </label>
                                    <div>
                                        ${lead.city ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Address
                                    </label>
                                    <div>
                                        ${lead.address ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Route
                                    </label>
                                    <div>
                                        ${lead.assign_route?.route_name ?? '-'} : ${lead.assign_route?.locations ?? '-'}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">
                                        Date and Time
                                    </label>
                                    <div>
                                        ${lead.created_at
                                            ? formatDateTime(lead.created_at)
                                            : '-'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            let followUpInformation = '';

            if (lead.status === 'Follow Up' || lead.status === 'Won') {

                followUpInformation = `
                    <div class="card border-0">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0">Follow-up Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- LEFT -->

                                <div class="col-md-6">

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Type of Visit
                                        </label>
                                        <div>
                                            ${lead.type_of_visit ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Stage of Construction
                                        </label>
                                        <div>
                                            ${lead.stage_of_construction ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Lead Source
                                        </label>
                                        <div>
                                            ${lead.lead_source ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Total Deal Volume
                                        </label>
                                        <div>
                                            ${lead.total_volume ?? '-'}
                                        </div>
                                    </div>

                                </div>


                                <!-- RIGHT -->

                                <div class="col-md-6">

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Construction Type
                                        </label>
                                        <div>
                                            ${lead.construction_type ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Lead Score
                                        </label>
                                        <div>
                                            ${lead.lead_score ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Source Name
                                        </label>
                                        <div>
                                            ${lead.source_name ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Total Quantity
                                        </label>
                                        <div>
                                            ${lead.total_quantity ?? '-'}
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                `;
            }
            let followUpUpdates = '';

            if (
                lead.status === 'Follow Up' &&
                lead.follow_ups &&
                lead.follow_ups.length > 0
            ) {

                let rows = '';

                lead.follow_ups.forEach(function (followUp) {

                    rows += `
                        <tr>

                            <td>
                                ${formatDate(followUp.follow_up_date)}
                            </td>

                            <td>
                                ${followUp.reason ?? '-'}
                            </td>

                            <td>
                                ${followUp.notification_status ?? '-'}
                            </td>

                        </tr>
                    `;

                });

                followUpUpdates = `
                    <div class="card border-0">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0">Follow-up Updates</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="table-responsive">

                                    <table class="table table-bordered table-striped w-100">

                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reason</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            ${rows}
                                        </tbody>

                                    </table>

                                </div>

                            </div>
                        </div>    
                    </div>

                `;
            }
            $('#leadViewContent').append(followUpInformation);
            $('#leadViewContent').append(followUpUpdates);
            let orderInformation = '';
            let productInformation = '';

            if (lead.status === 'Won') {

                /*
                |--------------------------------------------------------------------------
                | Order Information
                |--------------------------------------------------------------------------
                */

                let order = lead.orders?.[0] ?? null;

                orderInformation = `

                    <div class="card border-0 mt-2">

                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0">Order Information</h6>
                        </div>

                        <div class="card-body">

                            <div class="row">

                                <!-- LEFT -->

                                <div class="col-md-6">

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Dealer Code
                                        </label>

                                        <div>
                                            ${order?.dealer_code ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Buyer Type
                                        </label>

                                        <div>
                                            ${order?.buyer_type ?? '-'}
                                        </div>
                                    </div>

                                </div>


                                <!-- RIGHT -->

                                <div class="col-md-6">

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Dealer Name
                                        </label>

                                        <div>
                                            ${order?.dealer_name ?? '-'}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted d-block">
                                            Payment Type
                                        </label>

                                        <div>
                                            ${order?.payment_type ?? '-'}
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                `;


                /*
                |--------------------------------------------------------------------------
                | Product Type and Quantity
                |--------------------------------------------------------------------------
                */

                let productRows = '';

                if (lead.orders && lead.orders.length > 0) {

                    lead.orders.forEach(function(order) {

                        productRows += `

                            <tr>

                                <td>
                                    ${order.product_type ?? '-'}
                                </td>

                                <td>
                                    ${order.quantity ?? '-'}
                                </td>

                                <td>
                                    ${order.price
                                        ? Number(order.price).toLocaleString('en-IN', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        })
                                        : '-'}
                                </td>

                            </tr>

                        `;

                    });

                } else {

                    productRows = `

                        <tr>

                            <td colspan="3"
                                class="text-center text-muted">

                                No product information available

                            </td>

                        </tr>

                    `;

                }


                /*
                |--------------------------------------------------------------------------
                | Calculate Totals
                |--------------------------------------------------------------------------
                */

                let totalQuantity = 0;
                let totalAmount = 0;

                if (lead.orders && lead.orders.length > 0) {

                    lead.orders.forEach(function(order) {

                        totalQuantity += parseFloat(order.quantity) || 0;

                        totalAmount +=
                            (parseFloat(order.quantity) || 0) *
                            (parseFloat(order.price) || 0);

                    });

                }


                productInformation = `

                    <div class="card border-0 mt-2">

                        <div class="card-header bg-white border-bottom">

                            <h6 class="mb-0">
                                Product Type and Quantity
                            </h6>

                        </div>

                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-bordered table-sm mb-0">

                                    <thead>

                                        <tr>

                                            <th>Type</th>

                                            <th>Quantity</th>

                                            <th>Price</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        ${productRows}

                                    </tbody>

                                </table>

                            </div>


                            <!-- TOTALS -->

                            <div class="row g-0 mt-3">

                                <div class="col-md-6">

                                    <div class="p-3 bg-success-subtle">

                                        <label class="small text-muted d-block">
                                            Total Quantity
                                        </label>

                                        <strong>
                                            ${totalQuantity}
                                        </strong>

                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="p-3 bg-success-subtle">

                                        <label class="small text-muted d-block">
                                            Total Amount
                                        </label>

                                        <strong>
                                            ₹${totalAmount.toLocaleString('en-IN', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            })}
                                        </strong>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                `;
            }
            $('#leadViewContent').append(orderInformation);
            $('#leadViewContent').append(productInformation);
        },
        error: function (xhr) {

            console.log(xhr);

            $('#leadViewContent').html(`
                <div class="alert alert-danger">
                    Unable to load lead details.
                </div>
            `);

        }

    });

});
function formatDate(dateString) {

    if (!dateString) {
        return '-';
    }

    let date = new Date(dateString);

    let day = String(date.getDate()).padStart(2, '0');
    let month = String(date.getMonth() + 1).padStart(2, '0');
    let year = date.getFullYear();

    return `${day}-${month}-${year}`;
}


function formatDateTime(dateString) {

    if (!dateString) {
        return '-';
    }

    let date = new Date(dateString);

    let day = String(date.getDate()).padStart(2, '0');
    let month = String(date.getMonth() + 1).padStart(2, '0');
    let year = date.getFullYear();

    let hours = date.getHours();
    let minutes = String(date.getMinutes()).padStart(2, '0');

    let ampm = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    hours = hours || 12;

    return `${day}/${month}/${year}-${hours}:${minutes} ${ampm}`;
}
$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Lead DataTable
    |--------------------------------------------------------------------------
    */

    let leadTable = $('#leadTable').DataTable({

        processing: true,

        serverSide: true,

        ajax: {

            url: "{{ route('leads.list') }}",

            data: function (d) {

                d.from_date = $('#lead_from_date').val();

                d.to_date = $('#lead_to_date').val();

                d.district =
                    $('#lead_district').val();

                d.customer_type =
                    $('#customer_type').val();

                d.employee_id =
                    $('#employee').val();

                d.status =
                    $('#lead_status').val();

            }

        },


        columns: [

            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },

            {
                data: 'date_time',
                name: 'created_at'
            },

            {
                data: 'customer_type_name',
                name: 'customerType.name'
            },

            {
                data: 'customer_name_display',
                name: 'customer_name'
            },

            {
                data: 'location_display',
                name: 'location'
            },

            {
                data: 'district_name',
                name: 'district.name'
            },

            {
                data: 'status_display',
                name: 'status',
                orderable: false,
                searchable: false
            },

            {
                data: 'approval_status',
                name: 'approval_status',
                orderable: false,
                searchable: false
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }

        ],


        order: [
            [1, 'desc']
        ],


        pageLength: 12,


        lengthChange: false,


        searching: false,


        responsive: true

    });


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    $('#lead_from_date, #lead_to_date, #lead_district, #customer_type, #employee, #lead_status')
        .on('change', function () {

            leadTable.ajax.reload();

        });



});

</script>

@endsection