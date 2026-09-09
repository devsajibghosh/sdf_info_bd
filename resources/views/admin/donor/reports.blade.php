<!DOCTYPE html>
<html>
<head>
    <style>
        @font-face { font-family: 'SolaimanLipi'; font-weight: normal; src: url('{{ resource_path('fonts/SolaimanLipi.ttf') }}'); }
        @font-face { font-family: 'SolaimanLipi'; font-weight: bold; src: url('{{ resource_path('fonts/SolaimanLipi_Bold.ttf') }}'); }
        body { font-family: 'SolaimanLipi', sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ff9d00; padding: 8px; text-align: left; }
        th { background-color: #ffffff; }
        h2{
          font-size: 20px;
          text-align: center;
          font-weight: bold;
          color: #ff9d00;
        }
    </style>
</head>
<body>
    <h2>{{ __('Top 20 Donors Report(SDF)') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('Serial No.') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Phone Number') }}</th>
                <th>{{ __('District') }}</th>
                <th>{{ __('Total Donation Amount') }}</th>
                <th>{{ __('Number of Donations') }}</th>
                <th>{{ __('Last Donate') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($donors as $donor)
                <tr>
                  <td class="text-center">{{ $loop->iteration }}</td>
    
      <td>
            @php
                $matchedUser = $usersByPhone[$donor->phone_number] ?? null;
                $displayName = $donor->name ?: ($matchedUser ? $matchedUser->name : '-');
            @endphp
            {{ $displayName }}

        </td>
                    <td>{{ $donor->phone_number ?? '-' }}</td>

                <td>
                @php
                    $displayDistrict = $matchedUser ? $matchedUser->district : '-';
                @endphp
                  {{ $displayDistrict }}
               </td>
               
                    <td>BDT {{ number_format($donor->total_donations_amount, 2) }}</td>
                    <td>{{ $donor->total_donations_count ?? 0 }}</td>
                    <td>
                        @if($donor->lastDonation)
                            BDT {{ number_format($donor->lastDonation->amount, 2) }} <br>
                            {{ $donor->lastDonation->created_at->format('d M, Y') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>