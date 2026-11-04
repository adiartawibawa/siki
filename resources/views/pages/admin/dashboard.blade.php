@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $first = (string) Str::of(auth()->user()?->name ?? 'there')->before(' ');

    $stats = [
        ['wallet', 'violet', '$15,340', 'Net worth across all.', 'Total balance'],
        ['circle-dollar-sign', 'orange', '$3,200', 'Total income this month', 'Monthly income'],
        ['receipt', 'pink', '$2,475', 'Total expenses.', 'Monthly expenses'],
        ['piggy-bank', 'sky', '$725', 'This month savings', 'Savings'],
    ];

    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $series = [
        [
            'name' => 'Earning',
            'tone' => 'brand',
            'data' => [8000, 15000, 20000, 22000, 22430, 21000, 18000, 14000, 10000, 6000, 3500, 2000],
        ],
        [
            'name' => 'Spending',
            'tone' => 'teal',
            'data' => [3000, 6000, 9000, 11000, 12000, 11500, 11000, 10500, 10500, 12000, 17000, 30000],
        ],
        [
            'name' => 'Savings',
            'tone' => 'mint',
            'data' => [2000, 5000, 7000, 9000, 9500, 9800, 10000, 11000, 15000, 20000, 28000, 33000],
        ],
    ];

    $rows = [
        ['hotel', 'Hotel Booking', 'INV_000076', '17 Apr, 2026', '03:45 PM', '$25,500', 'success', 'Success'],
        ['plane', 'Flight Ticket Booking', 'INV_000075', '15 Apr, 2026', '11:30 AM', '$32,750', 'danger', 'Pending'],
    ];

    $ctl =
        'inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-800 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2';
@endphp

<x-layouts::app title="Overview">
    {{-- Sapaan + ringkasan --}}
    <x-ui.card>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $greeting }},
                    {{ $first }}</h2>
                <p class="mt-1 text-sm text-slate-500">Here’s an overview of your financial health and recent activity.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.dropdown width="w-40" :trigger-class="$ctl">
                    <x-slot:trigger>This month <x-ui.icon name="chevron-down" size="sm" /></x-slot:trigger>
                    <x-ui.dropdown-item>This month</x-ui.dropdown-item>
                    <x-ui.dropdown-item>Last month</x-ui.dropdown-item>
                    <x-ui.dropdown-item>This year</x-ui.dropdown-item>
                </x-ui.dropdown>
                <x-ui.button icon="download">Export</x-ui.button>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as [$icon, $tone, $value, $caption, $label])
                <x-ui.stat-card :icon="$icon" :tone="$tone" :value="$value" :caption="$caption"
                    :label="$label">
                    <x-slot:actions>
                        <x-ui.dropdown-item>View details</x-ui.dropdown-item>
                        <x-ui.dropdown-item>Download report</x-ui.dropdown-item>
                    </x-slot:actions>
                </x-ui.stat-card>
            @endforeach
        </div>
    </x-ui.card>

    {{-- Grafik + kartu --}}
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-medium text-slate-800">Transactions overview</h2>
                    <p class="mt-2 flex items-center gap-2 text-3xl font-semibold tracking-tight text-slate-900">
                        $45,567<span class="text-slate-400">.00</span>
                        <x-ui.badge tone="success" :dot="false"><x-ui.icon name="arrow-up"
                                size="xs" />4.9%</x-ui.badge>
                    </p>
                </div>
                <x-ui.segmented :options="['monthly' => 'Monthly', 'yearly' => 'Yearly']" value="yearly" aria-label="Period" />
            </div>

            <x-ui.line-chart class="mt-2" :labels="$months" :series="$series" :highlight="4" />
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="font-semibold text-slate-900">My cards</h2>
                    <p class="text-xs text-slate-500">Total 3 cards</p>
                </div>
                <x-ui.button variant="ghost" size="icon-sm" aria-label="Card options"><x-ui.icon name="ellipsis"
                        size="sm" /></x-ui.button>
            </div>

            <x-ui.payment-card class="mt-4" last4="6782" exp="09/29" />

            <p class="mt-5 text-sm text-slate-600">Spending limit</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                $3,884.00 <span class="text-xs font-normal text-slate-500">used from $20,638.00</span>
            </p>
            <x-ui.progress class="mt-3" :value="3884" :max="20638" label="Spending limit used" />
        </x-ui.card>
    </div>

    {{-- Tabel --}}
    <x-ui.card>
        <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-slate-50 p-3">
            <span class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600">
                <x-ui.icon name="arrow-left-right" size="18" />
            </span>
            <h2 class="font-semibold text-slate-900">Recent transactions</h2>

            <div class="ml-auto flex w-full flex-wrap items-center gap-2 sm:w-auto">
                <x-ui.search outline placeholder="Search" class="w-full sm:w-52" />
                <x-ui.dropdown width="w-44" align="left" :trigger-class="$ctl">
                    <x-slot:trigger>All category <x-ui.icon name="chevron-down" size="sm" /></x-slot:trigger>
                    <x-ui.dropdown-item>Travel</x-ui.dropdown-item>
                    <x-ui.dropdown-item>Food</x-ui.dropdown-item>
                </x-ui.dropdown>
                <x-ui.button icon="list-filter" iconRight="">Filter</x-ui.button>
            </div>
        </div>

        <x-ui.table class="mt-3">
            <x-slot:head>
                <th scope="col" class="w-10"><input type="checkbox" class="size-4 rounded accent-brand-600"
                        aria-label="Select all"></th>
                <th scope="col">Activity</th>
                <th scope="col">Order ID</th>
                <th scope="col">Date</th>
                <th scope="col">Time</th>
                <th scope="col">Price</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </x-slot:head>

            @foreach ($rows as [$icon, $activity, $order, $date, $time, $price, $tone, $status])
                <tr>
                    <td><input type="checkbox" class="size-4 rounded accent-brand-600"
                            aria-label="Select {{ $order }}"></td>
                    <td>
                        <span class="flex items-center gap-3 font-medium text-slate-900">
                            <span
                                class="grid size-8 place-items-center rounded-lg bg-slate-100 text-slate-600"><x-ui.icon
                                    :name="$icon" size="sm" /></span>
                            {{ $activity }}
                        </span>
                    </td>
                    <td>{{ $order }}</td>
                    <td>{{ $date }}</td>
                    <td>{{ $time }}</td>
                    <td class="font-medium text-slate-900">{{ $price }}</td>
                    <td><x-ui.badge :tone="$tone">{{ $status }}</x-ui.badge></td>
                    <td class="text-right">
                        <x-ui.button variant="ghost" size="icon-sm" aria-label="Open {{ $order }}"><x-ui.icon
                                name="ellipsis" size="sm" /></x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
</x-layouts::app>
