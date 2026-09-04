@extends('layouts.app')

@section('content')

<style>
    .route-tracking-wrapper {
        background: #f6f7fb;
        min-height: calc(100vh - 70px);
        padding: 20px;
    }

    .route-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }

    /* Filters */
    .filter-label {
        font-size: 12px;
        color: #555;
        margin-bottom: 6px;
        display: block;
    }

    .route-filter {
        height: 38px;
        font-size: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    /* Summary Cards */
    .summary-card {
        background: #fff;
        border-radius: 8px;
        padding: 12px 14px;
        min-height: 68px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
    }

    .summary-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .summary-label {
        font-size: 12px;
        color: #555;
        margin-bottom: 2px;
    }

    .summary-count {
        font-size: 15px;
        font-weight: 600;
    }

    .summary-leads .summary-icon {
        background: #e8f0ff;
        color: #2864dc;
    }

    .summary-leads .summary-count {
        color: #2864dc;
    }

    .summary-influencer .summary-icon {
        background: #ffe8fb;
        color: #df24b7;
    }

    .summary-influencer .summary-count {
        color: #df24b7;
    }

    .summary-dealer .summary-icon {
        background: #fff0e5;
        color: #f28b28;
    }

    .summary-dealer .summary-count {
        color: #f28b28;
    }

    .summary-orders .summary-icon {
        background: #e8f9ee;
        color: #39bd68;
    }

    .summary-orders .summary-count {
        color: #39bd68;
    }

    .summary-activities .summary-icon {
        background: #fbe9e9;
        color: #b73535;
    }

    .summary-activities .summary-count {
        color: #b73535;
    }

    .summary-commitments .summary-icon {
        background: #eeecff;
        color: #6655d9;
    }

    .summary-commitments .summary-count {
        color: #6655d9;
    }

    /* Main Area */
    .route-main-card {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
    }

    .map-container {
        height: 500px;
        width: 100%;
    }

    #routeMap {
        height: 100%;
        width: 100%;
    }

    /* Timeline */
    .timeline-panel {
        height: 500px;
        background: #fff;
        padding: 16px;
        overflow: hidden;
    }

    .attendance-summary {
        display: flex;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
        margin-bottom: 14px;
    }

    .attendance-item {
        flex: 1;
        padding: 0 10px;
        border-right: 1px solid #ddd;
    }

    .attendance-item:first-child {
        padding-left: 0;
    }

    .attendance-item:last-child {
        border-right: 0;
    }

    .attendance-label {
        font-size: 10px;
        color: #777;
        margin-bottom: 3px;
    }

    .attendance-value {
        font-size: 14px;
        font-weight: 600;
    }

    .punch-in {
        color: #36b36b;
    }

    .punch-out {
        color: #ed5151;
    }

    .working-hours {
        color: #2874d8;
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .timeline-title {
        font-size: 13px;
        font-weight: 600;
        color: #555;
        margin: 0;
    }

    .timeline-filter {
        width: 135px;
        height: 32px;
        font-size: 11px;
    }

    .timeline-scroll {
        height: 390px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .timeline {
        position: relative;
        padding: 0;
        margin: 0;
    }

    .timeline::before {
        content: "";
        position: absolute;
        top: 5px;
        bottom: 5px;
        left: 91px;
        width: 2px;
        background: #e1e1e1;
    }

    .timeline-item {
        display: flex;
        position: relative;
        min-height: 55px;
    }

    .timeline-time {
        width: 75px;
        font-size: 9px;
        color: #777;
        text-align: right;
        padding-right: 20px;
        padding-top: 2px;
    }

    .timeline-dot-wrapper {
        width: 32px;
        position: relative;
        display: flex;
        justify-content: center;
    }

    .timeline-dot {
        width: 8px;
        height: 8px;
        background: #2874d8;
        border-radius: 50%;
        position: relative;
        z-index: 2;
        margin-top: 4px;
    }

    .timeline-content {
        flex: 1;
        padding-left: 10px;
        padding-bottom: 12px;
    }

    .timeline-event {
        font-size: 10px;
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }

    .timeline-location {
        font-size: 9px;
        color: #777;
        line-height: 1.3;
    }

    /* Map Legend */
    .map-legend {
        position: absolute;
        left: 20px;
        bottom: 15px;
        z-index: 999;
        background: #fff;
        padding: 7px 12px;
        border-radius: 5px;
        box-shadow: 0 1px 5px rgba(0,0,0,.2);
        display: flex;
        gap: 14px;
        align-items: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        color: #555;
    }

    .legend-icon {
        width: 17px;
        height: 17px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        color: #fff;
    }

    .legend-lead {
        background: #2864dc;
    }

    .legend-influencer {
        background: #df24b7;
    }

    .legend-dealer {
        background: #f28b28;
    }

    .legend-order {
        background: #39bd68;
    }

    .legend-activity {
        background: #b73535;
    }

    .legend-commitment {
        background: #6655d9;
    }

    /* Map popup */
    .route-popup {
        min-width: 180px;
    }

    .route-popup-title {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .route-popup-text {
        font-size: 10px;
        color: #777;
    }

    /* Custom marker */
    .route-marker {
        width: 25px;
        height: 25px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,.3);
    }

    .route-marker i {
        transform: rotate(45deg);
        font-size: 10px;
    }

    .marker-lead {
        background: #2864dc;
    }

    .marker-influencer {
        background: #df24b7;
    }

    .marker-dealer {
        background: #f28b28;
    }

    .marker-order {
        background: #39bd68;
    }

    .marker-activity {
        background: #b73535;
    }

    .marker-commitment {
        background: #6655d9;
    }

    /* Scrollbar */
    .timeline-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .timeline-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }

    @media (max-width: 991px) {

        .summary-card {
            margin-bottom: 10px;
        }

        .map-container {
            height: 400px;
        }

        .timeline-panel {
            height: auto;
        }

        .timeline-scroll {
            height: 350px;
        }
    }
</style>


<div class="route-tracking-wrapper">

    {{-- PAGE HEADER --}}
    <div class="route-title">
        Route Overview
    </div>


    {{-- FILTERS --}}
    <div class="row g-3 mb-3">

        {{-- District --}}
        <div class="col-md-3 col-lg-2">

            <label class="filter-label">
                District
            </label>

            <select id="route_district"
                    class="form-select route-filter">
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


        {{-- Designation --}}
        <div class="col-md-3 col-lg-2">

            <label class="filter-label">
                Designation
            </label>

            <select id="route_designation"
                    class="form-select route-filter">

                <option value="">
                    Select Designation
                </option>

                @foreach($designations as $designation)
                    <option value="{{ $designation->id }}">
                        {{ $designation->type_name }}
                    </option>
                @endforeach

            </select>

        </div>


        {{-- Employee --}}
        <div class="col-md-3 col-lg-2">

            <label class="filter-label">
                Customer Type
            </label>
            <select id="route_customertype"
                    class="form-select route-filter">

                <option value="">
                    Select Type
                </option>

                @foreach($customertype as $type)

                    <option value="{{ $type->id }}">

                        {{ $type->name }}

                    </option>

                @endforeach

            </select>

        </div>

        {{-- Date --}}
        <div class="col-md-3 col-lg-2">
            <label class="filter-label" id="route_duration">
                Duration
            </label>
            <select name="duration" class="form-select route-filter">
                <option value="Today">Today</option>
                <option value="Yesterday">Yesterday</option>
                <option value="This Week">This Week</option>
                <option value="This Month">This Month</option>
                <option value="3 Month">3 Month</option>
            </select>
        </div>
    </div>


    {{-- MAP + TIMELINE --}}
    <div class="row g-0 route-main-card resultcard d-none">

        {{-- MAP --}}
        <div class="col-lg-8">

            <div class="map-container position-relative">

                <div id="routeMap"></div>


                

            </div>

        </div>


        
    </div>
</div>


<script>
$(document).ready(function () {
    
    $(".resultcard").addClass("d-none");

 
    $('.route-filter').on('change', function () {
        filterEmployees();
    });
    $('#route_date').on('change', function () {
        let date =
            $(this).val();
        if (!date) {
            hideResult();
            return;
        }
        if ($('#route_date').val() && $('#route_employee').val()) {
            loadRouteTracking();
        }
    });

    $('#route_employee').on('change', function () {
        if ($('#route_date').val() && $('#route_employee').val()) {
            loadRouteTracking();
        }
    });


});

</script>
@endsection