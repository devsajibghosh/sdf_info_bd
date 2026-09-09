<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 30px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        header h1 {
            margin: 0;
            font-size: 20px;
        }

        header p {
            margin: 2px 0;
            font-size: 12px;
            color: #555;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 16px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #444;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <header>
        <h1>{{ generalSetting('site_title') }}</h1>
        <p>{{ generalSetting('site_description') }}</p>
        <p>Phone: {{ generalSetting('site_phone') }} | Email: {{ generalSetting('site_email') }}</p>
    </header>

    <h2>{{ $title }}</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Note</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Expense By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $index => $expense)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $expense->note }}</td>
                    <td>{{ number_format($expense->amount, 2) }}</td>
                    <td>{{ optional($expense->category)->name }}</td>
                    <td>{{ optional($expense->expenseBy)->name }}</td>
                    <td>{{ System::getDateTime($expense->created_at) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
