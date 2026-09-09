<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Donation Receipt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #555;
            background: #fff;
        }
        .container {
            padding: 40px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            max-width: 800px;
            margin: auto;
        }
        .header {
            background-color: #f7f7f7;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .header img {
            max-width: 200px;
            margin-bottom: 20px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-table .label {
            font-weight: bold;
            width: 180px;
        }
        .thank-you {
            margin-top: 40px;
            text-align: center;
            font-size: 1.2em;
            color: #333;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ generalSetting('site_title') }}</h1>
            <h2>Donation Receipt</h2>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Donor Name:</td>
                <td>{{ $donation->receiptDonorName() }}</td>
            </tr>
            <tr>
                <td class="label">Transaction No:</td>
                <td>{{ $donation->payment->transaction_no }}</td>
            </tr>
            <tr>
                <td class="label">Date:</td>
                <td>{{ System::getDateTime($donation->created_at) }}</td>
            </tr>
            <tr>
                <td class="label">Amount:</td>
                <td><strong>{{ System::amountWithCurrency($donation->amount) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Donation Category:</td>
                <td>{{ $donation->category->name }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $donation->email }}</td>
            </tr>
            <tr>
                <td class="label">Phone Number:</td>
                <td>{{ $donation->phone_number }}</td>
            </tr>
        </table>

        <p class="thank-you">Thank you for your generous donation!</p>

        <div class="footer">
            <p>This receipt is to acknowledge your donation. No goods or services were provided in exchange for this contribution.</p>
        </div>
    </div>
</body>
</html>