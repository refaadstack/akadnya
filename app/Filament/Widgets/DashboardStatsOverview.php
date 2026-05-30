<?php

namespace App\Filament\Widgets;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
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
        $usersLastMonth = User::where('role', 'user')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $userIncrease = $usersLastMonth > 0
            ? (($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100
            : ($usersThisMonth > 0 ? 100 : 0);

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
            Stat::make('Total Revenue', 'Rp '.number_format($totalRevenue, 0, ',', '.'))
                ->description(sprintf('%s%% %s', number_format(abs($revenueIncrease), 1), $revenueIncrease >= 0 ? 'increase' : 'decrease'))
                ->descriptionIcon($revenueIncrease >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueIncrease >= 0 ? 'success' : 'danger')
                ->chart([
                    $revenueLastMonth,
                    $revenueThisMonth,
                ]),

            Stat::make('Total Users', number_format($totalUsers))
                ->description(sprintf('+%d new this month', $usersThisMonth))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart(array_fill(0, 7, $totalUsers)),

            Stat::make('Total Orders', number_format($totalOrders))
                ->description(sprintf('%s%% conversion rate', number_format($conversionRate, 1)))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning')
                ->chart([
                    $totalOrders - $ordersThisMonth,
                    $totalOrders,
                ]),

            Stat::make('Active Invitations', number_format($activeInvitations))
                ->description(sprintf('%s%% published', number_format($publishRate, 1)))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart([
                    $totalInvitations - $activeInvitations,
                    $activeInvitations,
                ]),
        ];
    }
}
