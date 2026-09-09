@extends('admin.layouts.app')

@section('content')
    <x-page-header page_title="Member Details" :back_route="route('admin.user.list')"> 
    </x-page-header>

    <div class="row"> 
        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.donation.list', ['search' => $user->phone_number]) }}"
                icon="hash" 
                :value="System::amountWithCurrency($widget['total_donation_amount'])"
                :title="__('Total Donation Amount')"
                class="h-100"
            />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.donation.list', ['search' => $user->phone_number]) }}"
                icon="hash" 
                :value="number_format($widget['number_of_donations'])"
                :title="__('Total Number Of Donations')"
                class="h-100"
                />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                icon="hand-coins" 
                :value="System::amountWithCurrency(amount($widget['last_donation_amount']))"
                class="h-100"
                :title="__('Last Donation Amount')"
                subtitle="{{ $widget['last_donation_date'] ? System::getDateTime($widget['last_donation_date']) : 'N/A' }}"
            />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                icon="hand-coins" 
                :value="System::amountWithCurrency(amount($widget['maximum_donation_amount']))"
                :title="__('Maximum Donation Amount')"
                subtitle="{{ $widget['max_donation_date'] ? System::getDateTime($widget['max_donation_date']) : 'N/A' }}"
            />
        </div>
    </div>
    
    <div class="row"> 
        <div class="col-lg-3">
            <x-widgets.one 
                icon="hash" 
                :value="System::amountWithCurrency($widget['total_collection_amount'])"
                :title="__('Total Collection Amount')"
                class="h-100"
            />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.donation.list', ['search' => $user->phone_number]) }}&collection=true"
                icon="hash" 
                :value="number_format($widget['total_collection_count'])"
                :title="__('Total Number Of Collections')"
                class="h-100"
                />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                icon="hand-coins" 
                :value="System::amountWithCurrency(amount($widget['last_collection_amount']))"
                class="h-100"
                :title="__('Last Collection Amount')"
                subtitle="{{ $widget['last_collection_date'] ? System::getDateTime($widget['last_collection_date']) : 'N/A' }}"
            />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                icon="hand-coins" 
                :value="System::amountWithCurrency(amount($widget['maximum_collection_amount']))"
                :title="__('Maximum Collection Amount')"
                subtitle="{{ $widget['max_collection_date'] ? System::getDateTime($widget['max_collection_date']) : 'N/A' }}"
            />
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="{{ $user->image_path ? asset('storage/' . $user->image_path) : 'https://placehold.co/150' }}" alt="User Image" class="img-thumbnail rounded-circle" width="100">
                    <h5 class="mt-3">{{ $user->first_name }} {{ $user->last_name }}</h5>
                    <span class="badge bg-{{ $user->status ? 'success' : 'secondary' }}">
                        {{ $user->status ? __('Active') : __('Inactive') }}
                    </span>
                    @if ($user->pc)
                        <span class="badge bg-info">@lang('Profile Completed')</span>
                    @endif 

                    <div class="text-center d-flex mt-2 flex-column gap-2">
                        <div class="border p-1 rounded">
                            <p>@lang('NID Front Image')</p>
                            <img class="img-fluid d-block" src="{{ imageSrc($user->nid_front) }}" />
                        </div>

                        <div class="border p-1 rounded">
                            <p>@lang('NID Back Image')</p>
                            <img class="img-fluid d-block" src="{{ imageSrc($user->nid_back) }}" />
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>@lang('Email'):</strong> {{ $user->email }} 
                            @if($user->email_verified_at)
                                <span class="badge bg-success ms-2">@lang('Verified')</span>
                            @else
                                <span class="badge bg-danger ms-2">@lang('Unverified')</span>
                            @endif
                        </li>
                        <li class="list-group-item"><strong>@lang('Phone'):</strong> {{ $user->phone_number }}
                            @if($user->phone_verified_at)
                                <span class="badge bg-success ms-2">@lang('Verified')</span>
                            @endif
                        </li>
                        <li class="list-group-item"><strong>@lang('Whatsapp'):</strong> {{ $user->whatsapp_number }}</li>
                        <li class="list-group-item"><strong>@lang('Facebook'):</strong> 
                            @if($user->facebook_id_link)
                                <a href="{{ $user->facebook_id_link }}" target="_blank">@lang('View Profile')</a>
                            @else
                                N/A
                            @endif
                        </li>
                        <li class="list-group-item"><strong>@lang('Preference'):</strong> {{ $user->preference }}</li>
                        <li class="list-group-item"><strong>@lang('Joined via'):</strong> {{ $user->joining_media ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>@lang('Reference'):</strong> {{ $user->reference ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>@lang('Political Relation'):</strong> {{ $user->political_relation ? 'Yes' : 'No' }}</li>
                        <li class="list-group-item"><strong>@lang('Post & Politics'):</strong> {{ $user->post_and_politics }}</li>
                        <li class="list-group-item"><strong>@lang('Joined At'):</strong> {{ diffForHumans($user->created_at) }}</li>
                        <li class="list-group-item"><strong>@lang('Last Updated'):</strong> {{ diffForHumans($user->updated_at) }}</li>
                        <li class="list-group-item"><strong>@lang('Present Address'):</strong> {{ $user->present_address }}</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>@lang('Full Name'):</strong> {{ $user->first_name }} {{ $user->last_name }}</li>
                        <li class="list-group-item"><strong>@lang('Father\'s Name'):</strong> {{ $user->father_name }}</li>
                        <li class="list-group-item"><strong>@lang('Mother\'s Name'):</strong> {{ $user->mother_name }}</li>
                        <li class="list-group-item"><strong>@lang('Date of Birth'):</strong> {{ $user->date_of_birth }}</li>
                        <li class="list-group-item"><strong>@lang('Age'):</strong> {{ $user->current_age }}</li>
                        <li class="list-group-item"><strong>@lang('Gender'):</strong> {{ ucfirst($user->gender) }}</li>
                        <li class="list-group-item"><strong>@lang('Blood Group'):</strong> {{ strtoupper($user->blood_group) }}</li>
                        <li class="list-group-item"><strong>@lang('Occupation'):</strong> {{ $user->occupation }}</li>
                        <li class="list-group-item"><strong>@lang('Marital Status'):</strong> {{ ucfirst($user->marital_status) }}</li>
                        <li class="list-group-item"><strong>@lang('ID Type'):</strong> {{ $user->id_type }}</li>
                        <li class="list-group-item"><strong>@lang('ID Number'):</strong> {{ $user->id_number }}</li>
                        <li class="list-group-item"><strong>@lang('Monthly Fee'):</strong> {{ System::amountWithCurrency($user->monthly_fee ?? 0) }}</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>@lang('Address'):</strong> {{ $user->address }}</li>
                        <li class="list-group-item"><strong>@lang('City'):</strong> {{ $user->city }}</li>
                        <li class="list-group-item"><strong>@lang('Zip Code'):</strong> {{ $user->zipcode }}</li>
                        <li class="list-group-item"><strong>@lang('Country'):</strong> {{ $user->country }}</li>
                        <li class="list-group-item"><strong>@lang('Division'):</strong> {{ $user->division }}</li>
                        <li class="list-group-item"><strong>@lang('District'):</strong> {{ $user->district }}</li>
                        <li class="list-group-item"><strong>@lang('Upazila')</strong> {{ $user->upazila }}</li>
                        <li class="list-group-item"><strong>@lang('Post Office'):</strong> {{ $user->post_office }}</li>
                        <li class="list-group-item"><strong>@lang('Ward')</strong> {{ $user->ward }}</li>
                        <li class="list-group-item"><strong>@lang('OTP Sent At')</strong> {{ $user->otp_sent_at ? diffForHumans($user->otp_sent_at) : 'N/A' }}</li>
                        <li class="list-group-item"><strong>@lang('OTP Code')</strong> {{ $user->otp_code ?? 'N/A' }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- call modal --}}
    <x-modal id="callModal" :title="__('Enter call log for this user')">
        <form action="{{ route('admin.call_log.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}" />

            <x-form.group>
                <x-form.label>@lang('Call Time')</x-form.label>
                <x-form.input type="datetime-local" value="{{ now() }}" name="call_time" required />
            </x-form.group>
            
            <x-form.group>
                <x-form.label>@lang('Call Status')</x-form.label>
                <select name="status" id="status" class="form-control" required>
                    <option value="">@lang('Select Call Status')</option>
                    <option value="{{ \App\Constants\Status::PHONE_OFF }}">@lang('Phone Off')</option>
                    <option value="{{ \App\Constants\Status::AFFIRMED_DONATE }}">@lang('Affirmed to Donate')</option>
                    <option value="{{ \App\Constants\Status::CALL_LATER }}">@lang('Call Later')</option>
                    <option value="{{ \App\Constants\Status::CALL_SPECIFIC_DATE }}">@lang('Call Specific Date')</option>
                    <option value="{{ \App\Constants\Status::CALL_DECLINED }}">@lang('Call Declined')</option>
                    <option value="{{ \App\Constants\Status::DECLINED_TO_DONATE }}">@lang('Declined to Donate')</option>
                </select>
            </x-form.group>


            <div class="dynamic-fields affirm-donate d-none">
                <x-form.group>
                    <x-form.label>@lang('Donation Amount')</x-form.label>
                    <x-form.input type="number" step="any" name="amount" />
                </x-form.group>
                <x-form.group>
                    <x-form.label>@lang('Approximate Date')</x-form.label>
                    <x-form.input type="date" name="approx_date" />
                </x-form.group>
            </div>

            <div class="dynamic-fields call-specific-date d-none">
                <x-form.group>
                    <x-form.label>@lang('Next Call Date')</x-form.label>
                    <x-form.input type="date" name="next_call_date" />
                </x-form.group>
            </div>
            
            <x-form.group>
                <x-form.label>@lang('Note')</x-form.label>
                <textarea class="form-control" name="note"></textarea>
            </x-form.group>

            <x-button type="submit" class="w-100">@lang('Submit')</x-button>
        </form>
    </x-modal>

    <x-card>
        <div class="w-100 mb-4 d-flex alig-items-center justify-content-between">
            <h4>@lang('Call Logs')</h4>
            <x-button class="callBtn">
                <x-icons.add />
                @lang('New Call')
            </x-button>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>@lang('Called By')</th>
                    <th>@lang('Called At')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Approx Date')</th>
                    <th>@lang('Next Call Date')</th>
                    <th>@lang('Note')</th>
                    <th class="text-end">@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($user->callLogs()->latest()->get() as $callLog)
                    <tr>
                        <td>{{ $callLog->admin->name }}</td>
                        <td>{{ System::getDateTime($callLog->call_log) }}</td>
                        <td>@php echo $callLog->statusBadge; @endphp</td>
                        <td>{{ $callLog->amount ? System::amountWithCurrency($callLog->amount) : 'N/A' }}</td>
                        <td>{{$callLog->approx_date ?? 'N/A'}}</td>
                        <td>{{$callLog->next_call_date ?? 'N/A'}}</td>
                        <td>{{$callLog->note ?? 'N/A'}}</td>
                        <td class="text-end">
                           <x-button confirmDelete class="btn-danger" href="{{ route('admin.call_log.delete', $callLog->id) }}">
                               <x-icons.delete-v2 />
                               @lang('Delete')</x-button> 
                        </td>
                    </tr>    
                @empty
                    <x-admin-empty-table :title="__('No call logs')" :message="__('Please make your first call by cliking the New Call button above')" />
                @endforelse
            </tbody>
        </table>

    </x-card>
@endsection

@push('scripts')
    <script>
        const affirmDonateStatus = "{{ \App\Constants\Status::AFFIRMED_DONATE }}";
        const callSpecifiCate = "{{ \App\Constants\Status::CALL_SPECIFIC_DATE }}";
        
        $('[name="status"]').on('change', function() {
            const status = $(this).val();

            $('.dynamic-fields').addClass('d-none');

            if(status == affirmDonateStatus) {
                $('.affirm-donate').removeClass('d-none');
            } else if(status == callSpecifiCate) {
                $('.call-specific-date').removeClass('d-none');
            }
        });
        
        $('.callBtn').on('click', function() {
            $('#callModal').modal('show');
        });
    </script>
@endpush