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

    .summary-comments .summary-icon {
        background: #eeecff;
        color: #6655d9;
    }

    .summary-comments .summary-count {
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

    .legend-comment {
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

    .marker-comment {
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
        Route Tracking
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
        Employee Name
    </label>

    <select id="route_employee"
            class="form-select route-filter">

        <option value="">
            Select Employee
        </option>

        @foreach($employees as $employee)

            <option value="{{ $employee->id }}"
                    data-district="{{ $employee->district_id }}"
                    data-designation="{{ $employee->employee_type_id }}">

                {{ $employee->name }}

            </option>

        @endforeach

    </select>

</div>


{{-- Date --}}
<div class="col-md-3 col-lg-2">

    <label class="filter-label">
        Date
    </label>

    <input type="date"
           id="route_date"
           class="form-control route-filter">

</div>

    </div>


    {{-- SUMMARY CARDS --}}
    <div class="row g-2 mb-3">

        {{-- Leads --}}
        <div class="col-6 col-md-4 col-lg">

            <div class="summary-card summary-leads">

                <div class="summary-icon">

                    <i class="fa fa-users"></i>

                </div>

                <div>

                    <div class="summary-label">
                        Leads
                    </div>

                    <div class="summary-count"
                         id="leadCount">

                        5

                    </div>

                </div>

            </div>

        </div>


        {{-- Influencer Visit --}}
        <div class="col-6 col-md-4 col-lg">

            <div class="summary-card summary-influencer">

                <div class="summary-icon">

                    <i class="fa fa-users"></i>

                </div>

                <div>

                    <div class="summary-label">
                        Influencer Visit
                    </div>

                    <div class="summary-count"
                         id="influencerCount">

                        5

                    </div>

                </div>

            </div>

        </div>


        {{-- Dealer Visit --}}
        <div class="col-6 col-md-4 col-lg">

            <div class="summary-card summary-dealer">

                <div class="summary-icon">

                    <i class="fa fa-building"></i>

                </div>

                <div>

                    <div class="summary-label">
                        Dealer Visit
                    </div>

                    <div class="summary-count"
                         id="dealerCount">

                        5

                    </div>

                </div>

            </div>

        </div>


        {{-- Orders --}}
        <div class="col-6 col-md-4 col-lg">

            <div class="summary-card summary-orders">

                <div class="summary-icon">

                    <i class="fa fa-shopping-bag"></i>

                </div>

                <div>

                    <div class="summary-label">
                        Orders
                    </div>

                    <div class="summary-count"
                         id="orderCount">

                        5

                    </div>

                </div>

            </div>

        </div>


        {{-- Activities --}}
        <div class="col-6 col-md-4 col-lg">

            <div class="summary-card summary-activities">

                <div class="summary-icon">

                    <i class="fa fa-clipboard"></i>

                </div>

                <div>

                    <div class="summary-label">
                        Activities
                    </div>

                    <div class="summary-count"
                         id="activityCount">

                        5

                    </div>

                </div>

            </div>

        </div>


        {{-- Comments --}}
        <div class="col-6 col-md-4 col-lg">

            <div class="summary-card summary-comments">

                <div class="summary-icon">

                    <i class="fa fa-comments"></i>

                </div>

                <div>

                    <div class="summary-label">
                        Comments
                    </div>

                    <div class="summary-count"
                         id="commentCount">

                        5

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MAP + TIMELINE --}}
    <div class="row g-0 route-main-card">

        {{-- MAP --}}
        <div class="col-lg-8">

            <div class="map-container position-relative">

                <div id="routeMap"></div>


                {{-- MAP LEGEND --}}
                <div class="map-legend">

                    <div class="legend-item">

                        <span class="legend-icon legend-lead">
                            <i class="fa fa-users"></i>
                        </span>

                        Leads

                    </div>


                    <div class="legend-item">

                        <span class="legend-icon legend-influencer">
                            <i class="fa fa-users"></i>
                        </span>

                        Influencer Visit

                    </div>


                    <div class="legend-item">

                        <span class="legend-icon legend-dealer">
                            <i class="fa fa-building"></i>
                        </span>

                        Dealer Visit

                    </div>


                    <div class="legend-item">

                        <span class="legend-icon legend-order">
                            <i class="fa fa-shopping-bag"></i>
                        </span>

                        Orders

                    </div>


                    <div class="legend-item">

                        <span class="legend-icon legend-activity">
                            <i class="fa fa-clipboard"></i>
                        </span>

                        Activities

                    </div>


                    <div class="legend-item">

                        <span class="legend-icon legend-comment">
                            <i class="fa fa-comments"></i>
                        </span>

                        Comments

                    </div>

                </div>

            </div>

        </div>


        {{-- TIMELINE --}}
        <div class="col-lg-4">

            <div class="timeline-panel">

                {{-- Attendance --}}
                <div class="attendance-summary">

                    <div class="attendance-item">

                        <div class="attendance-label">
                            Punch In
                        </div>

                        <div class="attendance-value punch-in">
                            09:05 AM
                        </div>

                    </div>


                    <div class="attendance-item">

                        <div class="attendance-label">
                            Punch Out
                        </div>

                        <div class="attendance-value punch-out">
                            06:05 PM
                        </div>

                    </div>


                    <div class="attendance-item">

                        <div class="attendance-label">
                            Working Hours
                        </div>

                        <div class="attendance-value working-hours">
                            09:00 Hours
                        </div>

                    </div>

                </div>


                {{-- Timeline Header --}}
                <div class="timeline-header">

                    <h6 class="timeline-title">
                        Timeline Overview
                    </h6>

                    <select id="timelineActivityFilter"
                            class="form-select timeline-filter">

                        <option value="all">
                            All Activities
                        </option>

                        <option value="lead">
                            Leads
                        </option>

                        <option value="influencer">
                            Influencer Visit
                        </option>

                        <option value="dealer">
                            Dealer Visit
                        </option>

                        <option value="order">
                            Orders
                        </option>

                        <option value="activity">
                            Activities
                        </option>

                        <option value="comment">
                            Comments
                        </option>

                    </select>

                </div>


                {{-- Timeline --}}
                <div class="timeline-scroll">

                    <div class="timeline">


                        {{-- Punch In --}}
                        <div class="timeline-item"
                             data-type="activity">

                            <div class="timeline-time">
                                09:00 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Punch In
                                </div>

                                <div class="timeline-location">
                                    Koratty, Thrissur
                                </div>

                            </div>

                        </div>


                        {{-- Lead --}}
                        <div class="timeline-item"
                             data-type="lead">

                            <div class="timeline-time">
                                09:20 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Lead Captured
                                </div>

                                <div class="timeline-location">
                                    ABC, Koratty, Thrissur
                                </div>

                            </div>

                        </div>


                        {{-- Influencer --}}
                        <div class="timeline-item"
                             data-type="influencer">

                            <div class="timeline-time">
                                09:50 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Influencer Visit
                                </div>

                                <div class="timeline-location">
                                    Steel Hub, Chingavanam, Thrissur
                                </div>

                            </div>

                        </div>


                        {{-- Influencer --}}
                        <div class="timeline-item"
                             data-type="influencer">

                            <div class="timeline-time">
                                10:10 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Influencer Visit
                                </div>

                                <div class="timeline-location">
                                    ABC Steels, Chingavanam, Thrissur
                                </div>

                            </div>

                        </div>


                        {{-- Order --}}
                        <div class="timeline-item"
                             data-type="order">

                            <div class="timeline-time">
                                11:10 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Order Taken
                                </div>

                                <div class="timeline-location">
                                    Steel Hub Pvt Limited, Pota, Thrissur
                                </div>

                            </div>

                        </div>
                        <div class="timeline-item"
                             data-type="order">

                            <div class="timeline-time">
                                11:10 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Order Taken
                                </div>

                                <div class="timeline-location">
                                    Steel Hub Pvt Limited, Pota, Thrissur
                                </div>

                            </div>

                        </div>
                        <div class="timeline-item"
                             data-type="order">

                            <div class="timeline-time">
                                11:10 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Order Taken
                                </div>

                                <div class="timeline-location">
                                    Steel Hub Pvt Limited, Pota, Thrissur
                                </div>

                            </div>

                        </div>


                        {{-- Comment --}}
                        <div class="timeline-item"
                             data-type="comment">

                            <div class="timeline-time">
                                11:30 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Comment
                                </div>

                                <div class="timeline-location">
                                    ABC Steels, Potta, Thrissur
                                </div>

                            </div>

                        </div>


                        {{-- Comment --}}
                        <div class="timeline-item"
                             data-type="comment">

                            <div class="timeline-time">
                                11:30 AM
                            </div>

                            <div class="timeline-dot-wrapper">

                                <div class="timeline-dot"></div>

                            </div>

                            <div class="timeline-content">

                                <div class="timeline-event">
                                    Comment
                                </div>

                                <div class="timeline-location">
                                    Steel Hub Pvt Limited, Pota, Thrissur
                                </div>

                            </div>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- Leaflet CSS --}}
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      crossorigin=""/>


{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        crossorigin=""></script>


<script>
  $(document).ready(function () {

    const employeeSelect = document.getElementById('route_employee');

    const employeeOptions = Array.from(
        employeeSelect.options
    );


    function filterEmployees() {

        const selectedDistrict =
            document.getElementById('route_district').value;

        const selectedDesignation =
            document.getElementById('route_designation').value;


        employeeOptions.forEach(function (option) {

            // Default "Select Employee"
            if (option.value === '') {

                option.hidden = false;

                return;
            }


            const employeeDistrict =
                option.getAttribute('data-district');

            const employeeDesignation =
                option.getAttribute('data-designation');


            /*
            |--------------------------------------------------------------------------
            | No filters selected
            |--------------------------------------------------------------------------
            */

            if (
                selectedDistrict === '' &&
                selectedDesignation === ''
            ) {

                option.hidden = false;

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | District + Designation
            |--------------------------------------------------------------------------
            */

            if (
                selectedDistrict !== '' &&
                selectedDesignation !== ''
            ) {

                option.hidden = !(
                    String(employeeDistrict) === String(selectedDistrict) &&
                    String(employeeDesignation) === String(selectedDesignation)
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | District only
            |--------------------------------------------------------------------------
            */

            if (selectedDistrict !== '') {

                option.hidden = !(
                    String(employeeDistrict) === String(selectedDistrict)
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Designation only
            |--------------------------------------------------------------------------
            */

            if (selectedDesignation !== '') {

                option.hidden = !(
                    String(employeeDesignation) === String(selectedDesignation)
                );

            }

        });


        // Reset employee selection
        employeeSelect.value = '';

    }


    /*
    |--------------------------------------------------------------------------
    | District Change
    |--------------------------------------------------------------------------
    */

    $('#route_district').on('change', function () {

        filterEmployees();

    });


    /*
    |--------------------------------------------------------------------------
    | Designation Change
    |--------------------------------------------------------------------------
    */

    $('#route_designation').on('change', function () {

        filterEmployees();

    });

});
$(document).ready(function () {
    let routeMap = L.map('routeMap', {
        zoomControl: true
    }).setView(
        [10.5276, 76.2144],
        11
    );


    /*
    |--------------------------------------------------------------------------
    | OpenStreetMap
    |--------------------------------------------------------------------------
    */

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution:
                '&copy; OpenStreetMap contributors'
        }
    ).addTo(routeMap);


    /*
    |--------------------------------------------------------------------------
    | Marker Helper
    |--------------------------------------------------------------------------
    */

    function createMarkerIcon(type) {

        let className = 'marker-lead';

        let icon = 'fa-users';


        switch (type) {

            case 'influencer':

                className = 'marker-influencer';
                icon = 'fa-users';

                break;


            case 'dealer':

                className = 'marker-dealer';
                icon = 'fa-building';

                break;


            case 'order':

                className = 'marker-order';
                icon = 'fa-shopping-bag';

                break;


            case 'activity':

                className = 'marker-activity';
                icon = 'fa-clipboard';

                break;


            case 'comment':

                className = 'marker-comment';
                icon = 'fa-comments';

                break;

        }


        return L.divIcon({

            className: '',

            html: `
                <div class="route-marker ${className}">
                    <i class="fa ${icon}"></i>
                </div>
            `,

            iconSize: [25, 25],

            iconAnchor: [12, 25],

            popupAnchor: [0, -25]

        });
    }


    /*
    |--------------------------------------------------------------------------
    | Demo Route Data
    |--------------------------------------------------------------------------
    */

    let routeData = [

        {
            lat: 10.5700,
            lng: 76.1900,
            type: 'lead',
            title: 'Lead',
            description: 'ABC, Koratty, Thrissur'
        },

        {
            lat: 10.5500,
            lng: 76.2000,
            type: 'influencer',
            title: 'Influencer Visit',
            description: 'Steel Hub, Chingavanam, Thrissur'
        },

        {
            lat: 10.5350,
            lng: 76.2100,
            type: 'dealer',
            title: 'Dealer Visit',
            description: 'ABC Steels, Chingavanam'
        },

        {
            lat: 10.5150,
            lng: 76.2200,
            type: 'order',
            title: 'Order Taken',
            description: 'Steel Hub Pvt Limited, Pota'
        },
        {
            lat: 10.5150,
            lng: 78.2200,
            type: 'order',
            title: 'Order Taken',
            description: 'Steel Hub Pvt Limited, Pota'
        },
        {
            lat: 10.5150,
            lng: 73.2200,
            type: 'order',
            title: 'Order Taken',
            description: 'Steel Hub Pvt Limited, Pota'
        },

        {
            lat: 10.4950,
            lng: 76.2300,
            type: 'lead',
            title: 'Lead',
            description: 'ABC Steels, Potta'
        },

        {
            lat: 10.4700,
            lng: 76.2400,
            type: 'order',
            title: 'Order',
            description: 'Steel Hub Pvt Limited'
        },

        {
            lat: 10.4400,
            lng: 76.2500,
            type: 'comment',
            title: 'Comment',
            description: 'ABC Steels, Potta'
        }

    ];


    /*
    |--------------------------------------------------------------------------
    | Add Route Line
    |--------------------------------------------------------------------------
    */

    let routeCoordinates = routeData.map(function (item) {

        return [
            item.lat,
            item.lng
        ];

    });


    L.polyline(
        routeCoordinates,
        {
            color: '#2874d8',
            weight: 4,
            opacity: 0.8
        }
    ).addTo(routeMap);


    /*
    |--------------------------------------------------------------------------
    | Add Markers
    |--------------------------------------------------------------------------
    */

    routeData.forEach(function (item) {

        L.marker(
            [item.lat, item.lng],
            {
                icon: createMarkerIcon(item.type)
            }
        )
        .addTo(routeMap)
        .bindPopup(`

            <div class="route-popup">

                <div class="route-popup-title">
                    ${item.title}
                </div>

                <div class="route-popup-text">
                    ${item.description}
                </div>

            </div>

        `);

    });


    /*
    |--------------------------------------------------------------------------
    | Fit Map
    |--------------------------------------------------------------------------
    */

    if (routeCoordinates.length) {

        routeMap.fitBounds(
            routeCoordinates,
            {
                padding: [30, 30]
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Timeline Filter
    |--------------------------------------------------------------------------
    */

    $('#timelineActivityFilter').on(
        'change',
        function () {

            let selected =
                $(this).val();


            $('.timeline-item').each(
                function () {

                    let type =
                        $(this).data('type');


                    if (
                        selected === 'all' ||
                        selected === type
                    ) {

                        $(this).show();

                    } else {

                        $(this).hide();

                    }

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Filter Change
    |--------------------------------------------------------------------------
    */

    $('#route_district, #route_designation, #route_employee, #route_date')
        .on('change', function () {

            /*
            | Call your API here
            |
            | Example:
            |
            | loadRouteTracking();
            */

        });


});

</script>

@endsection