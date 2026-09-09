<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Models\DonationCategory;
use App\Models\PageView;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\BlogPost;
use App\Models\Donation;
use App\Models\Gallery;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;

class AdminHome extends Component
{
    public function render()
    {
        $title                               = __('Dashboard');
        $widget['total_users']               = User::count();
        $widget['total_payment']             = Payment::sum('amount');
        $widget['new_users']                 = User::new()->count();
        $widget['email_unverified_users']    = User::emailUnverified()->count();
        $widget['mobile_unverified_users']   = User::phoneNumberUnverified()->count();
        $widget['total_blog_posts']          = BlogPost::count();
        $widget['published_blog_posts']      = BlogPost::published()->count();
        $widget['total_projects']            = Project::count();
        $widget['total_donations']           = Donation::sum('amount');
        $widget['total_donation_categories'] = DonationCategory::count();
        $widget['total_gallery_items']       = Gallery::active()->count();
        $widget['total_site_visits']         = PageView::sum('views');
        $widget['total_members']             = Admin::count();


        $pageViewDonut = PageView::orderBy('views', 'desc')
            ->take(5)
            ->get(['slug', 'views'])
            ->map(function ($item) {
                return [
                    'name' => $item->slug,
                    'value' => $item->views
                ];
            });

        $pageViewChart = PageView::orderBy('views', 'desc')
            ->take(10)
            ->get(['slug', 'views']);

        $donationTotalsByCategory = DB::table('donations')
            ->select('donation_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('donation_category_id')
            ->get();

        $categoryNames = DonationCategory::whereIn('id', $donationTotalsByCategory->pluck('donation_category_id'))
            ->pluck('name', 'id');

        $donationChart = $donationTotalsByCategory->map(function ($row) use ($categoryNames) {
            return [
                'label' => $categoryNames[$row->donation_category_id] ?? 'Category ' . $row->donation_category_id,
                'total' => $row->total,
            ];
        });

        return view('admin.dashboard', compact('title', 'donationChart', 'pageViewChart', 'widget', 'pageViewDonut'));
    }
}
