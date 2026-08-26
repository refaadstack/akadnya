<?php

namespace App\Filament\Widgets;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $stats = Cache::remember(
            'admin.dashboard.stats',
            now()->addSeconds(30),
            fn (): array => $this->calculateStats(),
        );

        return [
            Stat::make('Total Revenue', 'Rp '.number_format($stats['total_revenue'], 0, ',', '.'))
                ->description(sprintf('%s%% %s', number_format(abs($stats['revenue_increase']), 1), $stats['revenue_increase'] >= 0 ? 'increase' : 'decrease'))
                ->descriptionIcon($stats['revenue_increase'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($stats['revenue_increase'] >= 0 ? 'success' : 'danger')
                ->chart([
                    $stats['revenue_last_month'],
                    $stats['revenue_this_month'],
                ]),

            Stat::make('Total Users', number_format($stats['total_users']))
                ->description(sprintf('+%d new this month', $stats['users_this_month']))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart(array_fill(0, 7, $stats['total_users'])),

            Stat::make('Total Orders', number_format($stats['total_orders']))
                ->description(sprintf('%s%% conversion rate', number_format($stats['conversion_rate'], 1)))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning')
                ->chart([
                    $stats['total_orders'] - $stats['orders_this_month'],
                    $stats['total_orders'],
                ]),

            Stat::make('Active Invitations', number_format($stats['active_invitations']))
                ->description(sprintf('%s%% published', number_format($stats['publish_rate'], 1)))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart([
                    $stats['total_invitations'] - $stats['active_invitations'],
                    $stats['active_invitations'],
                ]),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function calculateStats(): array
    {
        // Total Revenue
        $totalRevenue = Order::where('status', 'paid')->sum('total_amount');
        $revenueThisMonth = Order::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');
        $revenueLastMonth = Order::where('status', 'paid')
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('total_amount');
        $revenueIncrease = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : 0;

        // Total Users
        $totalUsers = User::where('role', 'user')->count();
        $usersThisMonth = User::where('role', 'user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Total Orders
        $totalOrders = Order::count();
        $ordersThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $paidOrders = Order::where('status', 'paid')->count();
        $conversionRate = $totalOrders > 0 ? ($paidOrders / $totalOrders) * 100 : 0;

        // Active Invitations
        $activeInvitations = Invitation::where('status', 'published')->count();
        $totalInvitations = Invitation::count();
        $publishRate = $totalInvitations > 0 ? ($activeInvitations / $totalInvitations) * 100 : 0;

        return [
            'total_revenue' => (float) $totalRevenue,
            'revenue_this_month' => (float) $revenueThisMonth,
            'revenue_last_month' => (float) $revenueLastMonth,
            'revenue_increase' => $revenueIncrease,
            'total_users' => $totalUsers,
            'users_this_month' => $usersThisMonth,
            'total_orders' => $totalOrders,
            'orders_this_month' => $ordersThisMonth,
            'conversion_rate' => $conversionRate,
            'active_invitations' => $activeInvitations,
            'total_invitations' => $totalInvitations,
            'publish_rate' => $publishRate,
        ];
    }
}
