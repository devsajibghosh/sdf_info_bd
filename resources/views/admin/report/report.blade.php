@extends('admin.layouts.app')


@section('content')


<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-6">
<h3 class="fw-bold report-title">
    <span class="gradient-text">SDF Finance Report</span>
    <i class="fa-solid fa-calculator ms-2 icon-animated"></i>
</h3>
        </div>
    </div>

<div class="card mb-4 p-4 shadow-sm border-0">

    <div class="row g-3 align-items-end mb-4">
      <form action="{{ route('admin.report.report.summary') }}" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold text-secondary small">FROM DATE</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-calendar-days text-primary"></i></span>
                    <input type="date" name="from" id="fromDate" class="form-control" value="{{ $from }}">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-bold text-secondary small">TO DATE</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-calendar-check text-primary"></i></span>
                    <input type="date" name="to" id="toDate" class="form-control" value="{{ $to }}">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100 py-2">
                        <i class="fa-solid fa-filter me-2"></i>
                    </button>
                    <a href="{{ route('admin.report.report.summary') }}" class="btn btn-outline-warning w-100 py-2">
                        <i class="fa-solid fa-arrows-rotate me-2"></i>
                    </a>
                    <a href="{{ route('admin.report.report.pdf',['from' => $from, 'to' => $to])}}" class="btn btn-outline-danger w-100 py-2">
                        <i class="fa-solid fa-file-pdf me-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>
 </div>


    <div class="table-responsive table-container">
        <table class="table table-hover align-middle" id="accountTable">
            <thead class="table-light">
                <tr>
                    <th>Date & Month</th>
                    <th>Total Expense</th>
                    <th>Total Donation</th>
                    <th>General Doantion</th>
                    <th>MMF</th>
                    <th>SDF Goods</th>
                    <th>Collected Donation</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="table table-bordered border-secondary">
                <tr data-date="2024-05-15">
                    <td style="font-size: 10px;">{{ $monthDisplay }}</td>
                    <td class="fw-bold" style="font-size: 10px;">
                    BDT {{ number_format($totalExpense, 2) }}
                    </td>
                    <td class="fw-bold" style="font-size: 10px;" >BDT {{ number_format($totalDonation, 2) }}</td>
                    <td class="fw-bold" style="font-size: 10px;" >BDT {{ (number_format($totalGeneralDonation, 2)) }}</td>
                    <td class="fw-bold" style="font-size: 10px;" >BDT {{ number_format($memberMonthlyFees, 2) }}</td>
                    <td class="fw-bold" style="font-size: 10px;">BDT {{ number_format($sdfGoods, 2) }}</td>
                    <td class="fw-bold" style="font-size: 10px;">BDT {{ number_format($collectedDonation, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>



  
@endsection


@push('scripts')


<script>
    function filterData() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        const rows = document.querySelectorAll('#tableBody tr');

        rows.forEach(row => {
            const rowDate = row.getAttribute('data-date');
            if (from && to) {
                if (rowDate >= from && rowDate <= to) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        });
    }

function resetDates() {
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
}


</script>
  
@endpush


@push('styles')

<style>


.text{
    font-size: 8px;
}


/* text animation */


/* 1. Gradient Text Effect */
.gradient-text {
    background: linear-gradient(45deg, #ffffff, #ffffff, #ffffff);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shine 4s linear infinite;
}

/* 2. Shimmer Animation */
@keyframes shine {
    to {
        background-position: 200% center;
    }
}

/* 3. Floating Icon Animation */
.icon-animated {
    color: #a1c4fd;
    transition: all 0.3s ease;
    display: inline-block;
}

.report-title:hover .icon-animated {
    transform: scale(1.2) rotate(15deg);
    color: #ffffff;
    filter: drop-shadow(0 0 8px rgba(255, 0, 0, 0.6));
}

/* 4. Subtle Bottom Glow for the Title */
.report-title {
    letter-spacing: 0.5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #ffffff;
    padding-left: 15px;
}


</style>
    
@endpush