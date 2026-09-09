@extends('admin.layouts.app')

@section('content')
<div>
    <h5 class="mb-3">@lang('Dashboard')</h5>

    <div class="row">
        @if(userCan('view-users'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.donor.list') }}" icon="users-v1" :value="$widget['total_donors']" :title="__('Total Donors')" />
        </div>
        @endif
        
        @if(userCan('view-users'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.user.list') }}" icon="users-v1" :value="$widget['total_users']" :title="__('Total Members')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.members'))
            <div class="col-lg-3">
                <x-widgets.one link="{{ route('admin.member.list') }}" icon="users-v1" :value="$widget['total_members']" :title="__('Total Admins')" />
            </div>
        @endif

        @if(userCan('view-dashboard-widgets.users'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.user.new_list') }}" icon="users-v1" :value="$widget['new_users']" :title="__('New Members')" />
        </div>
        
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.user.mobile.unverfied') }}" icon="users-v1" :value="$widget['mobile_unverified_users']"
            :title="__('Mobile Unverified Members')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.payments'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.report.payment.list') }}" icon="wallet-cards" :value="amount(System::amountWithCurrency($widget['total_payment']))"
                :title="__('Total Payments')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.payments'))
        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.donation.approved') }}" 
                icon="hand-coins" 
                :value="amount(System::amountWithCurrency($widget['total_donations']))"
                :title="__('Total Donations')" 
            />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.payments'))
        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.donation.pending') }}" 
                icon="hand-coins" 
                :value="amount(System::amountWithCurrency($widget['pending_donations']))"
                :title="__('Pending Donations')" />
        </div>
        @endif

        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.expense.list') }}" 
                icon="dollar-sign" 
                :value="amount(System::amountWithCurrency($widget['total_expense']))"
                :title="__('Total Expense')" 
            />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.expense_category.list') }}" 
                icon="list" 
                :value="$widget['total_expense_category']"
                :title="__('Total Expense Category/Group')" />
        </div>

        @if(userCan('view-dashboard-widgets.blog-posts'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.blog_post.list') }}" icon="rss" :value="$widget['total_blog_posts']"
                :title="__('Total Blog Posts')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.blog-posts'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.blog_post.published') }}" icon="rss" :value="$widget['published_blog_posts']"
                :title="__('Published Blog Posts')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.projects'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.project.list') }}" icon="list" :value="$widget['total_projects']" :title="__('Total Projects')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.donation-categories'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.donation_category.list') }}" icon="list" :value="$widget['total_donation_categories']"
                :title="__('Total Donation Categories')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.galleries'))
        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.gallery.list') }}" icon="image" :value="$widget['total_gallery_items']" :title="__('Total Galleries')" />
        </div>
        @endif

        @if(userCan('view-dashboard-widgets.page-views'))
        <div class="col-lg-3">
            <x-widgets.one icon="image" :value="$widget['total_site_visits']" :title="__('Total Page Views')" />
        </div>
        @endif

        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.report.call_logs') }}" icon="phone" :value="$widget['total_call_logs']" :title="__('Total Call Logs')" />
        </div>

        <div class="col-lg-3">
            <x-widgets.one link="{{ route('admin.report.contact_submissions') }}" icon="contact" :value="$widget['contact_submissions']" :title="__('Contact Submissions')" />
        </div>

    </div>

    <div class="row mt-4 gy-4">
        @if(userCan('view-dashboard-charts.donations'))
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div id="donationChart" style="width: 100%; height: 400px;"></div>
                </div>
            </div>

        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3>@lang('Unique Site Visitor Count')</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="d-flex justify-content-between list-group-item">
                            <strong>@lang('Today')</strong>
                            <span>{{ $siteVisit['today']  }}</span>
                        </li>
                        
                        <li class="d-flex justify-content-between list-group-item">
                            <strong>@lang('This Week')</strong>
                            <span>{{ $siteVisit['this_week'] }}</span>
                        </li>

                        <li class="d-flex justify-content-between list-group-item">
                            <strong>@lang('This Month')</strong>
                            <span>{{ $siteVisit['this_month'] }}</span>
                        </li>

                        <li class="d-flex justify-content-between list-group-item">
                            <strong>@lang('This Year')</strong>
                            <span>{{ $siteVisit['this_year'] }}</span>
                        </li>

                        <li class="d-flex justify-content-between list-group-item">
                            <strong>@lang('Total')</strong>
                            <span>{{ $siteVisit['total'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
        
        @if(userCan('view-dashboard-charts.page-views'))
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div id="pageViewChart" style="width: 100%; height: 400px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div id="pageViewDonut" style="height: 400px;"></div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
    

<script src="{{ asset('assets/shared/js/echarts.min.js') }}"></script>

<script>
    function initCharts() {
        const donutDom = document.getElementById('pageViewDonut');
        if (!donutDom) return;

        const pageViewDonut = echarts.init(donutDom);
        pageViewDonut.setOption({
            title: {
                text: 'Page View Distribution',
                subtext: 'Top 5 Pages',
                left: 'center'
            },
            tooltip: { trigger: 'item' },
            legend: { bottom: 10, left: 'center' },
            series: [{
                name: 'Page Views',
                type: 'pie',
                radius: ['40%', '70%'],
                label: { show: false, position: 'center' },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '18',
                        fontWeight: 'bold'
                    }
                },
                labelLine: { show: false },
                data: {!! json_encode($pageViewDonut) !!}
            }]
        });

        const pageViewChart = echarts.init(document.getElementById('pageViewChart'));
        pageViewChart.setOption({
            title: { text: 'Top Page Views', left: 'center' },
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }},
            xAxis: {
                type: 'category',
                data: {!! json_encode($pageViewChart->pluck('slug')) !!},
                axisLabel: { rotate: 30 }
            },
            yAxis: { type: 'value', minInterval: 1 },
            series: [{
                name: 'Views',
                type: 'bar',
                data: {!! json_encode($pageViewChart->pluck('views')) !!},
                itemStyle: { color: 'rgba(255, 99, 132, 0.6)' }
            }]
        });

        const donationChart = echarts.init(document.getElementById('donationChart'));
        donationChart.setOption({
            title: { text: 'Total Donations by Category', left: 'center' },
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }},
            xAxis: {
                type: 'category',
                data: {!! json_encode($donationChart->pluck('label')) !!},
                axisLabel: { rotate: 30 }
            },
            yAxis: { type: 'value', minInterval: 1 },
            series: [{
                name: 'Total Donations',
                type: 'bar',
                data: {!! json_encode($donationChart->pluck('total')) !!},
                itemStyle: { color: 'rgba(75, 192, 192, 0.6)' }
            }]
        });

        window.addEventListener('resize', () => {
            pageViewDonut.resize();
            pageViewChart.resize();
            donationChart.resize();
        });
    }

    document.addEventListener("DOMContentLoaded", initCharts);
</script>

@endpush