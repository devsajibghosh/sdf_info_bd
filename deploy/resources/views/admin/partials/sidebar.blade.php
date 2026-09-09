<div id="sidebar-wrapper">
    <ul class="sidebar-nav">
        <li class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ System::logo() }}" class="logo" alt="@lang('Logo')" />
            </a>
        </li>
        <li>
            <a href="{{ route('admin.dashboard') }}" class="{{ activeClass('admin.dashboard') }}">
                <x-icons.dashboard-v1 />
                <span>@lang('Dashboard')</span>
            </a>
        </li>

        @if (userCan('view-projects'))
            <li>
                <a href="{{ route('admin.project.list') }}" class="{{ activeClass('admin.project*') }}">
                    <x-icons.list />
                    <span>@lang('Projects')</span>
                </a>
            </li>
        @endif

        @if (userCan('view-blog-posts'))
            <li>
                <a href="{{ route('admin.blog_post.list') }}" class="{{ activeClass('admin.blog_post.*') }}">
                    <x-icons.rss />
                    <span>@lang('Blog Posts')</span>
                </a>
            </li>
        @endif

        <li>
            <a href="{{ route('admin.notice.list') }}" class="{{ activeClass('admin.notice.*') }}">
                <x-icons.newspaper />
                <span>@lang('Notices')</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.donor.list') }}" class="{{ activeClass('admin.donor.*') }}">
                <x-icons.users-v1 />
                <span>@lang('Donors')</span>
            </a>
        </li>

        @if (userCan('view-gallery'))
            <li class="has-submenu {{ activeClass('admin.galler*') }}">
                <a href="javascript:void(0);" class="submenu-toggle">
                    <x-icons.image />
                    <span>@lang('Gallery')</span>
                    <span class="arrow">▾</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.gallery_category.list') }}"
                            class="{{ activeClass('admin.gallery_category.list') }}">
                            @lang('Gallery Categories')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.gallery.list') }}" class="{{ activeClass('admin.gallery.list') }}">
                            @lang('Galleries')
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if (userCan('view-donation'))
            <li class="has-submenu {{ activeClass('admin.donation*') }}">
                <a href="javascript:void(0);" class="submenu-toggle">
                    <x-icons.hand-coins />
                    <span>@lang('Donation')</span>
                    <span class="arrow">▾</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.donation.manual') }}"
                            class="{{ activeClass('admin.donation.manual') }}">
                            @lang('Manual Donation')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.donation_category.list') }}"
                            class="{{ activeClass('admin.donation_category.list') }}">
                            @lang('Donation Categories')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.donation.list') }}" class="{{ activeClass('admin.donation.list') }}">
                            @lang('All Donations')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.donation.pending') }}" class="{{ activeClass('admin.donation.pending') }}">
                            @lang('Pending Donations')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.donation.approved') }}" class="{{ activeClass('admin.donation.approved') }}">
                            @lang('Approved Donations')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.donation.rejected') }}" class="{{ activeClass('admin.donation.rejected') }}">
                            @lang('Rejected Donations')
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if (userCan('view-members'))
            <li class="has-submenu {{ activeClass('admin.member.*') }}">
                <a href="javascript:void(0);" class="submenu-toggle">
                    <x-icons.users-v1 />
                    <span>@lang('Admins')</span>
                    <span class="arrow">▾</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.member.list') }}" class="{{ activeClass('admin.member.list') }}">
                            @lang('All Admins')
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        <li class="has-submenu {{ activeClass('admin.expense*') }}">
            <a href="javascript:void(0);" class="submenu-toggle">
                <x-icons.dollar-sign />
                <span>@lang('Expense')</span>
                <span class="arrow">▾</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ route('admin.expense_category.list') }}"
                        class="{{ activeClass('admin.expense_category.list') }}">
                        @lang('Expense Categories')
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.expense.list') }}" class="{{ activeClass('admin.expense.list') }}">
                        @lang('Expenses')
                    </a>
                </li>
            </ul>
        </li>

        @if (userCan('view-users'))
            <li class="has-submenu {{ activeClass('admin.user.*') }}">
                <a href="javascript:void(0);" class="submenu-toggle">
                    <x-icons.users-v1 />
                    <span>@lang('Members')</span>
                    <span class="arrow">▾</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.user.list') }}" class="{{ activeClass('admin.user.list') }}">
                            @lang('All Members')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.user.new_list') }}" class="{{ activeClass('admin.user.new_list') }}">
                            @lang('New Member')
                            @if ($newUsers > 0)
                                <span class="count">{{ min(99, $newUsers) }}{{ $newUsers > 99 ? '+' : '' }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        <li class="has-submenu {{ activeClass('admin.payment_gateway*') }}">
            <a href="javascript:void(0);" class="submenu-toggle">
                <x-icons.wallet-cards />
                <span>@lang('Payment Gateways')</span>
                <span class="arrow">▾</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ route('admin.payment_gateway.list') }}"
                        class="{{ activeClass('admin.payment_gateway.list') }}">
                        @lang('Automatic Gateways')
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.payment_gateway.manual.list') }}"
                        class="{{ activeClass('admin.payment_gateway.manual.list') }}">
                        @lang('Manual Gateways')
                    </a>
                </li>
            </ul>
        </li>

        <li class="has-submenu {{ activeClass('admin.website.*') }}">
            <a href="javascript:void(0);" class="submenu-toggle">
                <x-icons.globe />
                <span>@lang('Website')</span>
                <span class="arrow">▾</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ route('admin.website.section.list') }}"
                        class="{{ activeClass('admin.website.section.list') }}">
                        @lang('Sections')
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.website.page.list') }}"
                        class="{{ activeClass('admin.website.page.list') }}">
                        @lang('Pages')
                    </a>
                </li>
            </ul>
        </li>

        <li class="has-submenu {{ activeClass('admin.report.*') }}">
            <a href="javascript:void(0);" class="submenu-toggle">
                <x-icons.scroll-text />
                <span>@lang('Reports')</span>
                <span class="arrow">▾</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ route('admin.report.notifications') }}"
                        class="{{ activeClass('admin.report.notifications') }}">
                        @lang('Notifications')
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.report.payment.list') }}"
                        class="{{ activeClass('admin.report.payment.list') }}">
                        <span>@lang('Payments')</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.report.admin_login') }}"
                        class="{{ activeClass('admin.report.admin_login') }}">
                        <span>@lang('Admin Logins')</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.report.call_logs') }}"
                        class="{{ activeClass('admin.report.call_logs') }}">
                        <span>@lang('Call Logs')</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.report.contact_submissions') }}"
                        class="{{ activeClass('admin.report.contact_submissions') }}">
                        <span>@lang('Contact Submissions')</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.report.user_by_area') }}"
                        class="{{ activeClass('admin.report.user_by_area') }}">
                        <span>@lang('Users By Area')</span>
                    </a>
                </li>
            </ul>
        </li>

        @if (userCan('view-acl'))
            <li class="has-submenu {{ activeClass('admin.acl.*') }}">
                <a href="javascript:void(0);" class="submenu-toggle">
                    <x-icons.shield />
                    <span>@lang('ACL')</span>
                    <span class="arrow">▾</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.acl.role.list') }}"
                            class="{{ activeClass('admin.acl.role.list') }}">
                            @lang('Roles')
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        <li>
            <a href="{{ route('admin.setting.general') }}" class="{{ activeClass('admin.setting.*') }}">
                <x-icons.setting />
                <span>@lang('Settings')</span>
            </a>
        </li>
    </ul>
</div>
