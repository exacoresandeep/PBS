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

@endsection


@section('scripts')

<script>

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


    /*
    |--------------------------------------------------------------------------
    | View Lead
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.viewLead', function (e) {

        e.preventDefault();

        let url = $(this).attr('href');

        window.location.href = url;

    });

});

</script>

@endsection