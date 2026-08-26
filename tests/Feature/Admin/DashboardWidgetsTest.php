<?php

use App\Filament\Widgets\BestSellerProducts;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\TemplatePopularityChart;
use App\Filament\Widgets\UsersChart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
    $this->admin = User::factory()->create(['role' => 'admin']);
});

function createSale(string $slug, int $quantity, string $status = 'paid', ?Product $product = null): Product
{
    $product ??= Product::factory()->create([
        'type' => 'addon',
        'slug' => $slug,
        'name' => $slug,
        'price' => 50000,
    ]);

    $order = Order::create([
        'user_id' => User::factory()->create()->id,
        'order_number' => 'ORD-TEST-'.uniqid(),
        'status' => $status,
        'total_amount' => 50000 * $quantity,
        'paid_at' => $status === 'paid' ? now() : null,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'item_type' => 'product',
        'item_id' => $product->id,
        'name' => $product->name,
        'price' => 50000,
        'quantity' => $quantity,
    ]);

    return $product;
}

function widgetPollingInterval(object $widget): ?string
{
    $method = new ReflectionMethod($widget, 'getPollingInterval');
    $method->setAccessible(true);

    return $method->invoke($widget);
}

function invokeWidgetMethod(object $widget, string $method): mixed
{
    $reflection = new ReflectionMethod($widget, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke($widget);
}

test('admin can open the dashboard', function () {
    $this->actingAs($this->admin)
        ->get('/admin')
        ->assertOk();
});

test('best seller ranking only counts paid orders', function () {
    createSale('prod-b', 10);
    createSale('prod-a', 3);
    createSale('prod-c', 99, status: 'pending');

    $rows = BestSellerProducts::bestSellersQuery()->get();

    expect($rows->pluck('slug')->all())->toBe(['prod-b', 'prod-a'])
        ->and((int) $rows->first()->selling_rank)->toBe(1)
        ->and((int) $rows->first()->total_sold)->toBe(10)
        ->and((int) $rows->last()->total_sold)->toBe(3);
});

test('best seller accumulates quantity across multiple paid orders', function () {
    $product = createSale('acc-prod', 2);
    createSale('acc-prod', 5, product: $product);
    createSale('single-big', 6);

    $rows = BestSellerProducts::bestSellersQuery()->get();

    expect($rows->pluck('slug')->all())->toBe(['acc-prod', 'single-big'])
        ->and((int) $rows->first()->total_sold)->toBe(7)
        ->and((float) $rows->first()->total_revenue)->toBe(350000.0);
});

test('best seller list is limited to five products', function () {
    foreach ([7, 6, 5, 4, 3, 2, 1] as $index => $quantity) {
        createSale('prod-'.($index + 1), $quantity);
    }

    $rows = BestSellerProducts::bestSellersQuery()->get();

    expect($rows)->toHaveCount(5)
        ->and($rows->pluck('selling_rank')->map(fn ($rank) => (int) $rank)->all())->toBe([1, 2, 3, 4, 5])
        ->and((int) $rows->first()->total_sold)->toBe(7)
        ->and((int) $rows->last()->total_sold)->toBe(3);
});

test('best seller widget renders ranked rows with polling enabled', function () {
    createSale('render-a', 4);
    createSale('render-b', 9);

    $component = Livewire::test(BestSellerProducts::class)
        ->call('loadTable');
    $html = $component->html();

    expect($html)->toContain('wire:poll.30s')
        ->and($html)->toContain('Best Seller Products')
        ->and($html)->toContain('render-b')
        ->and(str_contains($html, 'render-a'))->toBeTrue()
        ->and(strpos($html, 'render-b') < strpos($html, 'render-a'))->toBeTrue();
});

test('latest orders widget renders with polling enabled', function () {
    createSale('latest-prod', 1);

    $html = Livewire::test(LatestOrders::class)
        ->call('loadTable')
        ->html();

    expect($html)->toContain('wire:poll.15s')
        ->and($html)->toContain('Latest Orders');
});

test('dashboard widgets poll at expected intervals', function () {
    expect(widgetPollingInterval(new DashboardStatsOverview))->toBe('15s')
        ->and(widgetPollingInterval(new RevenueChart))->toBe('30s')
        ->and(widgetPollingInterval(new UsersChart))->toBe('30s')
        ->and(widgetPollingInterval(new TemplatePopularityChart))->toBe('30s');
});

test('stats overview caches calculated totals', function () {
    createSale('cached-a', 1);
    createSale('cached-b', 2);

    Livewire::test(DashboardStatsOverview::class)->assertSuccessful();

    expect(Cache::has('admin.dashboard.stats'))->toBeTrue();

    $stats = Cache::get('admin.dashboard.stats');

    expect((float) $stats['total_revenue'])->toBe(150000.0)
        ->and($stats['total_orders'])->toBe(2);
});

test('charts aggregate daily data through sql queries', function () {
    createSale('chart-prod', 2);

    $usersData = invokeWidgetMethod(new UsersChart, 'getUserData');
    $revenueData = invokeWidgetMethod(new RevenueChart, 'getRevenueData');
    $templatesData = invokeWidgetMethod(new TemplatePopularityChart, 'getData');

    $usersSeries = $usersData['users'];
    $revenueSeries = $revenueData['revenue'];
    $orderSeries = $revenueData['orders'];

    expect(count($usersData['labels']))->toBe(30)
        ->and(end($usersSeries))->toBeGreaterThanOrEqual(1)
        ->and(count($revenueData['labels']))->toBe(30)
        ->and((float) end($revenueSeries))->toBe(100000.0)
        ->and((int) end($orderSeries))->toBe(1)
        ->and($templatesData['labels'])->toBe([]);
});
