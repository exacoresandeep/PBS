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
            <select id="route_employee"
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
            <label class="filter-label">
                Duration
            </label>
            <select name="duration" class="form-select">
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

                        <span class="legend-icon legend-commitment">
                            <i class="fa fa-handshake-o"></i>
                        </span>

                        commitments

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
                <div class="no-attendance d-none">
                    <p>No attendance has been recorded for this person on the selected date.</p>
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

                        <option value="commitment">
                            commitments
                        </option>

                    </select>

                </div>


                {{-- Timeline --}}
                <div class="timeline-scroll">

                    <div class="timeline" id="routeTimeline">



                    </div>
                </div>
                
            </div>
            
        </div>
        
    </div>
</div>
<div class="modal fade"
     id="routeSummaryModal"
     tabindex="-1"
     aria-labelledby="routeSummaryModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title"
                    id="routeSummaryModalLabel">
                    Details
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>


            <div class="modal-body">

                <div id="routeSummaryModalContent">

                    <div class="text-center py-4">
                        Loading...
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
    function renderSummaryModal(type, data) {

    let container =
        $('#routeSummaryModalContent');

    if (!Array.isArray(data) || data.length === 0) {

        container.html(`
            <div class="text-center text-muted py-4">
                No records found for the selected date.
            </div>
        `);

        return;
    }


    let html = `
        <div class="table-responsive">

            <table class="table table-sm table-bordered table-hover">

                <thead>
                    <tr>
    `;


    if (type === 'lead') {

        html += `
            <th>#</th>
            <th>Time</th>
            <th>Customer</th>
            <th>Customer Type</th>
        `;

    }

    else if (type === 'influencer') {

        html += `
            <th>#</th>
            <th>Time</th>
            <th>Influencer Name </th>
        `;

    }        

    else if (type === 'dealer') {

        html += `
            <th>#</th>
            <th>Time</th>
            <th>Dealer</th>
        `;

    }

    else if (type === 'order') {

        html += `
            <th>#</th>
            <th>Time</th>
            <th>Dealer</th>
            <th>Order ID</th>
        `;

    }

    else if (type === 'activity') {

        html += `
            <th>#</th>
            <th>Time</th>
            <th>Dealer</th>
            <th>Activity</th>
        `;

    }

    else if (type === 'commitment') {

        html += `
            <th>#</th>
            <th>Committed Date</th>
            <th>Dealer</th>
        `;

    }


    html += `
                    </tr>
                </thead>

                <tbody>
    `;


    data.forEach(function (item, index) {

        html += `<tr>`;

        html += `
            <td>${index + 1}</td>
        `;


        if (type === 'lead') {

            html += `
                <td>${item.time ?? '-'}</td>
                <td>${item.customer_name ?? '-'}</td>
                <td>${item.customer_type ?? '-'}</td>
            `;

        }

        else if (type === 'influencer') {

            html += `
            <td>${item.time ?? '-'}</td>
            <td>${item.influencer_name ?? '-'}</td>
            `;

        }

        else if (type === 'dealer') {

            html += `
                <td>${item.time ?? '-'}</td>
                <td>${item.dealer_name ?? '-'}</td>
            `;

        }

        else if (type === 'order') {

            html += `
                <td>${item.time ?? '-'}</td>
                <td>${item.dealer_name ?? '-'}</td>
                <td>${item.order_number ?? '-'}</td>
            `;

        }

        else if (type === 'activity') {

            html += `
                <td>${item.time ?? '-'}</td>
                <td>${item.dealer ?? '-'}</td>
                <td>${item.activity_name ?? '-'}</td>
            `;

        }

        else if (type === 'commitment') {

            html += `
            <td>${item.time ?? '-'}</td>
                <td>${item.dealer ?? '-'}</td>
            `;

        }


        html += `</tr>`;

    });


    html += `
                </tbody>

            </table>

        </div>
    `;


    container.html(html);
}
    $(document).on('click', '.summary-card', function () {

        let type = $(this).data('type');

        let titles = {
            lead: 'Lead Details',
            influencer: 'Influencer Visit Details',
            dealer: 'Dealer Visit Details',
            order: 'Order Details',
            activity: 'Activity Details',
            commitment: 'Commitment Details'
        };

        let title = titles[type] || 'Details';

        $('#routeSummaryModalLabel').text(title);

        $('#routeSummaryModalContent').html(`
            <div class="text-center py-4">
                <i class="fa fa-spinner fa-spin"></i>
                Loading...
            </div>
        `);


        // Get current filters
        let district =
            $('#route_district').val();

        let designation =
            $('#route_designation').val();

        let employee =
            $('#route_employee').val();

        let date =
            $('#route_date').val();


        // Open modal
        $('#routeSummaryModal').modal('show');


        $.ajax({

            url: "{{ url('/tracking/trackingDetails') }}",

            type: "GET",

            data: {
                type: type,
                district_id: district,
                designation_id: designation,
                employee_id: employee,
                date: date
            },

            success: function (response) {

                if (!response.success) {

                    $('#routeSummaryModalContent').html(`
                        <div class="alert alert-warning">
                            No details found.
                        </div>
                    `);

                    return;
                }

                renderSummaryModal(
                    type,
                    response.data || []
                );
            },

            error: function (xhr) {

                console.error(
                    'Summary Details Error:',
                    xhr.responseText
                );

                $('#routeSummaryModalContent').html(`
                    <div class="alert alert-danger">
                        Unable to load details.
                    </div>
                `);
            }

        });

    });
    function renderTimeline(timelineData) {

        let timeline = $('#routeTimeline');

        timeline.empty();

        if (!Array.isArray(timelineData) || timelineData.length === 0) {

            timeline.html(`
                <div class="text-center text-muted"
                    style="font-size:11px;padding:30px 10px;">
                    No activities found for the selected date.
                </div>
            `);

            return;
        }

        timelineData.forEach(function (item) {

            let type = item.type || 'activity';

            let time = item.time || '';

            let title = item.title || '';

            let location = item.location || '';

            let description = item.description || '';

            timeline.append(`

                <div class="timeline-item"
                    data-type="${type}">

                    <div class="timeline-time">
                        ${time}
                    </div>

                    <div class="timeline-dot-wrapper">

                        <div class="timeline-dot"></div>

                    </div>

                    <div class="timeline-content">

                        <div class="timeline-event">
                            ${title}
                        </div>

                        <div class="timeline-location">
                            ${location}
                        </div>

                        ${
                            description
                                ? `
                                    <div class="timeline-location">
                                        ${description}
                                    </div>
                                `
                                : ''
                        }

                    </div>

                </div>

            `);

        });

        applyTimelineFilter();

    }

    $(".resultcard").addClass("d-none");

    let routeMarkers = [];
    let routePolyline = null;
    let routeMap = null;

    routeMap = L.map('routeMap', {
        zoomControl: true
    }).setView([10.5276, 76.2144], 11);


    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(routeMap);

    const employeeSelect =
        document.getElementById('route_employee');

    const employeeOptions =
        Array.from(employeeSelect.options);


    function filterEmployees() {

        const selectedDistrict =
            $('#route_district').val();

        const selectedDesignation =
            $('#route_designation').val();


        employeeOptions.forEach(function (option) {

            if (option.value === '') {

                option.hidden = false;

                return;
            }


            const employeeDistrict =
                option.getAttribute('data-district');

            const employeeDesignation =
                option.getAttribute('data-designation');

            if (
                selectedDistrict === '' &&
                selectedDesignation === ''
            ) {

                option.hidden = false;

                return;
            }

            if (
                selectedDistrict !== '' &&
                selectedDesignation !== ''
            ) {

                option.hidden = !(
                    String(employeeDistrict) ===
                    String(selectedDistrict)

                    &&

                    String(employeeDesignation) ===
                    String(selectedDesignation)
                );

                return;
            }

            if (selectedDistrict !== '') {

                option.hidden = !(
                    String(employeeDistrict) ===
                    String(selectedDistrict)
                );

                return;
            }

            if (selectedDesignation !== '') {

                option.hidden = !(
                    String(employeeDesignation) ===
                    String(selectedDesignation)
                );

                return;
            }

        });

        employeeSelect.value = '';
    }

    $('#route_district').on('change', function () {
        filterEmployees();

        if ($('#route_date').val() && $('#route_employee').val()) {
            loadRouteTracking();
        }
    });

    $('#route_designation').on('change', function () {
        filterEmployees();

        if ($('#route_date').val() && $('#route_employee').val()) {
            loadRouteTracking();
        }
    });

    function clearRouteMap() {

        routeMarkers.forEach(function (marker) {
            if (routeMap.hasLayer(marker)) {
                routeMap.removeLayer(marker);
            }
        });

        routeMarkers = [];

        if (routePolyline) {
            if (routeMap.hasLayer(routePolyline)) {
                routeMap.removeLayer(routePolyline);
            }
            routePolyline = null;
        }
    }

    function createMarkerIcon(type) {
        let className = 'marker-lead';
        let icon = 'fa-users';
        switch (type) {
            case 'lead':
                className = 'marker-lead';
                icon = 'fa-users';
                break;
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
            case 'commitment':
                className = 'marker-commitment';
                icon = 'fa-handshake-o';
                break;
            default:
                className = 'marker-lead';
                icon = 'fa-map-marker';
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

    function updateSummaryCounts(counts) {
        
        counts = counts || {};
        $('#leadCount').text(
            counts.leads ?? 0
        );
        $('#influencerCount').text(
            counts.influencers ?? 0
        );
        $('#dealerCount').text(
            counts.dealers ?? 0
        );
        $('#orderCount').text(
            counts.orders ?? 0
        );
        $('#activityCount').text(
            counts.activities ?? 0
        );
        $('#commitmentCount').text(
            counts.commitments ?? 0
        );
        $('.punch-in').text(
            counts.punch_in ?? 0
        );
        $('.punch-out').text(
            counts.punch_out ?? 0
        );
        $('.working-hours').text(
            counts.total_active_hours ?? 0
        );
        if(counts.punch_in == ""){
            $(".no-attendance").removeClass("d-none");
        }
       
    }

    function hideResult() {
        $(".resultcard")
            .addClass("d-none");
        clearRouteMap();
        updateSummaryCounts({});
    }

    function showResult() {
        $(".resultcard")
            .removeClass("d-none");
    }

    function loadRouteTracking() {
        let district =
            $('#route_district').val();
        let designation =
            $('#route_designation').val();
        let employee =
            $('#route_employee').val();
        let date =
            $('#route_date').val();

        if (!date) {
            hideResult();
            return;
        }
        showResult();

        clearRouteMap();

        $.ajax({
            url: "{{ url('/tracking/route-tracking-data') }}",
            type: "GET",
            data: {
                district_id: district,
                designation_id: designation,
                employee_id: employee,
                date: date
            },
            beforeSend: function () {
                console.log(
                    'Loading route data...'
                );
            },
            success: function (response) {
                $(".no-attendance").addClass("d-none");
                console.log(
                    'Route Tracking Response:',
                    response
                );
                updateSummaryCounts(
                    response.counts
                );
                renderTimeline(
                    response.timeline || []
                );

                let routeData =
                    response.routes || [];

                if (
                    !Array.isArray(routeData) ||
                    routeData.length === 0
                ) {
                    console.log(
                        'No route data found for:',
                        date
                    );
                    return;
                }
                let routeCoordinates = [];

                routeData.forEach(function (item) {
                    let lat =
                        parseFloat(item.lat);

                    let lng =
                        parseFloat(item.lng);

                    if (isNaN(lat) || isNaN(lng)) {
                        return;
                    }
                    if (lat === 0 || lng === 0) {
                        return;
                    }
                    routeCoordinates.push([
                        lat,
                        lng
                    ]);

                    let marker =
                        L.marker(
                            [lat, lng],
                            {
                                icon: createMarkerIcon(item.type)
                            }
                        );

                    marker.routeType = item.type;

                    marker.addTo(routeMap);

                    marker.bindPopup(`
                        <div class="route-popup">
                            <div class="route-popup-title">
                                ${item.title ?? ''}
                            </div>

                            <div class="route-popup-text">
                                ${item.description ?? ''}
                            </div>
                        </div>
                    `);

                    routeMarkers.push(marker);
                });

                if (routeCoordinates.length === 0) {
                    console.log(
                        'Route data exists but no valid coordinates found.'
                    );
                    return;
                }

                if (routeCoordinates.length > 1) {
                    routePolyline =
                        L.polyline(
                            routeCoordinates,
                            {
                                color: '#2874d8',
                                weight: 4,
                                opacity: 0.8
                            }
                        )
                        .addTo(routeMap);
                }

                routeMap.fitBounds(
                    routeCoordinates,
                    {
                        padding: [30, 30]
                    }
                );

                setTimeout(function () {
                    routeMap.invalidateSize();
                }, 200);
            },

            error: function (xhr) {
                console.error(
                    'Route Tracking Error:',
                    xhr.status,
                    xhr.responseText
                );
                clearRouteMap();
                updateSummaryCounts({});
            }
        });
    }
 
    $('#route_district,#route_designation').on('change', function () {
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

   
    function applyTimelineFilterold() {

        let selected =
            $('#timelineActivityFilter').val();

        $('#routeTimeline .timeline-item').each(function () {

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

        });

    }

    function applyTimelineFilter() {

        let selected =
            $('#timelineActivityFilter').val();

        // -----------------------------------
        // Filter Timeline
        // -----------------------------------
        $('#routeTimeline .timeline-item').each(function () {

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

        });


        // -----------------------------------
        // Filter Map Markers
        // -----------------------------------
        routeMarkers.forEach(function (marker) {

            let markerType =
                marker.routeType;

            if (
                selected === 'all' ||
                selected === markerType
            ) {

                if (!routeMap.hasLayer(marker)) {
                    marker.addTo(routeMap);
                }

            } else {

                if (routeMap.hasLayer(marker)) {
                    routeMap.removeLayer(marker);
                }

            }

        });


        // -----------------------------------
        // Refit map based on visible markers
        // -----------------------------------
        let visibleCoordinates = [];

        routeMarkers.forEach(function (marker) {

            if (routeMap.hasLayer(marker)) {

                let latLng =
                    marker.getLatLng();

                visibleCoordinates.push([
                    latLng.lat,
                    latLng.lng
                ]);

            }

        });


        // Remove existing polyline
        if (routePolyline) {

            if (routeMap.hasLayer(routePolyline)) {
                routeMap.removeLayer(routePolyline);
            }

            routePolyline = null;
        }


        // Draw filtered route
        if (visibleCoordinates.length > 1) {

            routePolyline =
                L.polyline(
                    visibleCoordinates,
                    {
                        color: '#2874d8',
                        weight: 4,
                        opacity: 0.8
                    }
                )
                .addTo(routeMap);

        }


        // Fit map to visible markers
        if (visibleCoordinates.length > 0) {

            routeMap.fitBounds(
                visibleCoordinates,
                {
                    padding: [30, 30]
                }
            );

        }

        setTimeout(function () {
            routeMap.invalidateSize();
        }, 200);
    }
    $('#timelineActivityFilter').on(
        'change',
        function () {

            applyTimelineFilter();

        }
    );
    filterEmployees();

    setTimeout(function () {
       routeMap.invalidateSize();
    }, 300);
});

</script>
@endsection