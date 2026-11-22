@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e8e8e8;
}
.page-header h4 { margin: 0; font-size: 1.5rem; }
.filters-box {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}
.stat-box {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}
.stat-box i { font-size: 1.8rem; margin-bottom: .5rem; }
.stat-box h3 { font-size: 2rem; margin: .5rem 0; }
.stat-box.primary i { color:#5a67d8 }
.stat-box.success i { color:#48bb78 }
.stat-box.warning i { color:#ed8936 }
.stat-box.info i { color:#4299e1 }
.stat-box.danger i { color:#f56565 }
.stat-box.secondary i { color:#718096 }

.dashboard-grid {
    display: grid;
    gap: 1.5rem;
}

/* Admin cards: 5 columns on large screens */
.dashboard-grid.admin {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    margin-bottom: 20px;
}

/* User cards: 4 columns */
.dashboard-grid.user {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

@media (min-width: 1200px) {
    .dashboard-grid.admin {
        grid-template-columns: repeat(5, 1fr);
    }
    .dashboard-grid.user {
        grid-template-columns: repeat(4, 1fr);
    }
}

</style>
@endpush

@section('content')

{{-- HEADER --}}
<div class="page-header">
    <div>
        <h4>Dashboard</h4>
        <small>{{ Auth::user()->name }}</small>
    </div>
    <div class="context-info">
        <small class="text-muted">Loading...</small>
    </div>
</div>

{{-- FILTERS --}}
<div class="filters-box" id="filtersRow" style="display:none;">
    <div class="row">

        {{-- BRANCH (ADMIN ONLY) --}}
        @if(auth()->user()->user_type == 1)
        <div class="col-md-6 mb-3">
            <label class="form-label">Branch</label>
            <select id="branch" class="form-select select2">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- DEPARTMENT (ADMIN + USER) --}}
        <div class="col-md-6 mb-3">
            <label class="form-label">Department</label>
            <select id="department" class="form-select select2">
                <option value="">All Departments</option>
            </select>
        </div>

    </div>
</div>


{{-- ADMIN DASHBOARD --}}
{{-- <div id="adminDashboard" style="display:none;">
    <div class="dashboard-grid admin">
        @php
            $adminCards = [
                ['label'=>'Branches','id'=>'branches','color'=>'success','icon'=>'code-fork'],
                ['label'=>'Departments','id'=>'departments','color'=>'info','icon'=>'sitemap'],
                ['label'=>'Employees','id'=>'employees','color'=>'warning','icon'=>'users'],
                ['label'=>'Users','id'=>'users','color'=>'danger','icon'=>'user'],
                ['label'=>'Files','id'=>'files','color'=>'secondary','icon'=>'folder'],
            ];
        @endphp

        @foreach($adminCards as $card)
            <div class="stat-box {{ $card['color'] }}">
                <i class="fa fa-{{ $card['icon'] }}"></i>
                <h3 id="{{ $card['id'] }}">0</h3>
                <p>{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>
</div> --}}


{{-- USER / FILE STATS --}}
<div id="userDashboard" style="display:none;">
    <div class="dashboard-grid user">
        @php
            $userCards = [
                ['label'=>'Total Files','id'=>'total_files','color'=>'primary','icon'=>'file-text'],
                ['label'=>'Closed Files','id'=>'closed_files','color'=>'success','icon'=>'check-circle'],
                ['label'=>'Reopened Files','id'=>'reopened_files','color'=>'warning','icon'=>'folder-open'],
                ['label'=>'File Movements','id'=>'movements','color'=>'info','icon'=>'exchange'],
            ];
        @endphp

        @foreach($userCards as $card)
            <div class="stat-box {{ $card['color'] }}">
                <i class="fa fa-{{ $card['icon'] }}"></i>
                <h3 id="{{ $card['id'] }}">0</h3>
                <p>{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>
</div>


@endsection

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
@endpush
@push('custom-scripts')
<script>
$(document).ready(function () {

    $('.select2').select2({ width: '100%' });

    // Initial load
    loadDashboard();

    @if(auth()->user()->user_type == 1)

        // ================= ADMIN =================
        // Branch change → reload departments + dashboard
        $('#branch').on('change', function () {
            let branchId = $(this).val();

            $('#department').val('').trigger('change');

            if (branchId) {
                loadDepartments(branchId);
            } else {
                $('#department')
                    .html('<option value="">All Departments</option>')
                    .prop('disabled', false);
            }

            loadDashboard();
        });

    @else

        // ================= USER =================
        // Auto-load departments for user's branch
        loadDepartments();

    @endif

    // Department change → reload dashboard
    $('#department').on('change', function () {
        loadDashboard();
    });
});

/**
 * Load dashboard counts
 */
function loadDashboard() {

    $('.context-info').html('<small class="text-muted">Loading...</small>');

    $.ajax({
        url: "{{ route('dashboard.data') }}",
        type: "GET",
        dataType: "json",
        data: {
            branch_id: $('#branch').length ? $('#branch').val() : null,
            department_id: $('#department').val()
        },
        success: function (data) {

            $('#filtersRow').show();

            if (data.mode === 'admin') {

                // ADMIN sees both dashboards
                $('#adminDashboard').show();
                $('#userDashboard').show();

                // Master stats
               //

                // File stats
                $('#total_files').text(data.total_files ?? 0);
                $('#closed_files').text(data.closed_files ?? 0);
                $('#reopened_files').text(data.reopened_files ?? 0);
                $('#movements').text(data.movements ?? 0);

                $('.context-info').html('<small class="text-muted">Admin View</small>');

            } else {

                // USER sees only file stats
                $('#adminDashboard').hide();
                $('#userDashboard').show();

                $('#total_files').text(data.total_files ?? 0);
                $('#closed_files').text(data.closed_files ?? 0);
                $('#reopened_files').text(data.reopened_files ?? 0);
                $('#movements').text(data.movements ?? 0);

                updateContextInfo();
            }
        },
        error: function () {
            $('.context-info').html('<small class="text-danger">Failed to load data</small>');
        }
    });
}

/**
 * Load departments
 * - Admin → uses selected branch
 * - User → backend forces branch from employee
 */
function loadDepartments(branchId = null) {

    $('#department')
        .prop('disabled', true)
        .html('<option value="">Loading...</option>');

    let url = branchId
        ? "{{ url('get-departments') }}/" + branchId
        : "{{ url('get-departments') }}";

    $.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        success: function (data) {
            let options = '<option value="">All Departments</option>';
            $.each(data, function (id, name) {
                options += `<option value="${id}">${name}</option>`;
            });
            $('#department')
                .html(options)
                .prop('disabled', false);
        }
    });
}

/**
 * Context info (top right text)
 */
function updateContextInfo() {

    @if(auth()->user()->user_type == 1)

        let context = $('#branch').val()
            ? $('#branch option:selected').text()
            : 'All Branches';

    @else

        let context = '{{ optional(auth()->user()->employee->branch)->name ?? "My Branch" }}';

    @endif

    $('.context-info').html(`<small class="text-muted">${context}</small>`);
}
</script>
@endpush
