<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>(SDF)Donations Report({{ now()->subDays(2)->format('d M') }} - {{ now()->format('d M, Y') }})</title>
    <style>
        @font-face { font-family: 'SolaimanLipi'; font-weight: normal; src: url('{{ resource_path('fonts/SolaimanLipi.ttf') }}'); }
        @font-face { font-family: 'SolaimanLipi'; font-weight: bold; src: url('{{ resource_path('fonts/SolaimanLipi_Bold.ttf') }}'); }
        body { font-family: 'SolaimanLipi', 'DejaVu Sans', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #fb6203; padding: 5px;}
        th { background: #fff; }

        .text-color{
            color: #F54927;
            font-size: 20px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<h3 align="center">
    <span class="text-color">
       Sanatani Development Foundation Donations Report ({{ now()->subDays(2)->format('d M') }} - {{ now()->format('d M, Y') }})
       <hr>
    </span>
</h3>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Phone(9dgt)</th>
            <th>Transaction No</th>
            <th>Channel</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Date</th>
            <th>Approved By</th>
        </tr>
    </thead>

    <tbody>
        @foreach($donations as $donation)
            <tr>
                <td>{{ $donation->user?->name ?? $donation->donor?->name }}</td>
                <td>
                {{ $donation->phone_number ? str($donation->phone_number)->limit(9, '') : 'N/A' }}
                </td>
                <td>{{ $donation->payment?->transaction_no ?? '-' }}</td>
                <td>{{ $donation->payment?->paymentGateway?->name ?? '-' }}</td>
                <td>{{ number_format($donation->amount, 2) }}</td>
                <td>{{ $donation->category->name }}</td>
                <td>{{ $donation->created_at->format('d M Y') }}</td>
                <td>{{ $donation->approvedBy?->name ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
