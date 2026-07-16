<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan — {{ $myStall['name'] ?? 'Penjual' }} — Smart-Kantin ITEBA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen">

    <div x-data="laporanApp()" class="max-w-5xl mx-auto px-4 py-8">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-xl font-bold text-white">
                    📊 Laporan Penjualan
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $myStall['icon'] ?? '' }} {{ $myStall['name'] ?? 'Stand Anda' }}
                </p>
            </div>
            <div class="flex items-center gap-3 no-print">
                <a href="{{ route('laporan.penjual.print') }}"
                   target="_blank"
                   class="text-sm bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 font-medium px-4 py-2 rounded-lg transition-all">
                    🖨️ Cetak / PDF
                </a>
                <a href="{{ route('dashboard.penjual') }}"
                   class="text-sm text-gray-400 hover:text-amber-400 transition-colors">
                    ← Kembali ke Dashboard
                </a>
            </div>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Pendapatan</p>
                <p class="text-2xl font-bold text-emerald-400 mt-1">
                    Rp <span x-text="totalRevenue.toLocaleString('id-ID')"></span>
                </p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Pesanan</p>
                <p class="text-2xl font-bold text-amber-400 mt-1">
                    <span x-text="allOrders.length"></span>
                </p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Rata-rata Per Pesanan</p>
                <p class="text-2xl font-bold text-sky-400 mt-1">
                    Rp <span x-text="averageOrder.toLocaleString('id-ID')"></span>
                </p>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-gray-900/60 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h2 class="text-sm font-semibold text-white">Riwayat Pesanan</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-800/50">
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-5 py-3 font-medium">Pelanggan</th>
                        <th class="px-5 py-3 font-medium">Items</th>
                        <th class="px-5 py-3 font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Jam Ambil</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <template x-for="(order, i) in allOrders" :key="order.id">
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-gray-500" x-text="order.id"></td>
                            <td class="px-5 py-3 text-white font-medium" x-text="order.user_name"></td>
                            <td class="px-5 py-3">
                                <div class="text-xs text-gray-400 space-y-0.5">
                                    <template x-for="item in order.items" :key="item.name">
                                        <span x-text="`${item.qty}x ${item.name}`" class="block"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-amber-400 font-semibold">
                                Rp <span x-text="order.total.toLocaleString('id-ID')"></span>
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs" x-text="order.pickup_time"></td>
                            <td class="px-5 py-3">
                                <span
                                    class="text-[10px] font-medium px-2 py-0.5 rounded-full uppercase"
                                    :class="{
                                        'bg-amber-500/15 text-amber-400': order.status === 'pending',
                                        'bg-sky-500/15 text-sky-400': order.status === 'processing',
                                        'bg-emerald-500/15 text-emerald-400': order.status === 'ready',
                                        'bg-gray-500/15 text-gray-400': order.status === 'completed',
                                        'bg-red-500/15 text-red-400': order.status === 'canceled'
                                    }"
                                    x-text="order.status"
                                ></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="allOrders.length === 0" class="text-center py-10">
                <p class="text-gray-600 text-sm">Belum ada riwayat pesanan</p>
            </div>
        </div>

    </div>

    <script>
        const baseUrl = '{{ url('/') }}';
        document.addEventListener('alpine:init', () => {
            Alpine.data('laporanApp', () => ({
                baseUrl: baseUrl,
                stallId: {{ $myStall['id'] ?? 'null' }},

                allOrders: [],

                async init() {
                    if (this.stallId) {
                        try {
                            const res = await fetch(this.baseUrl + `/api/orders/${this.stallId}`);
                            const data = await res.json();
                            this.allOrders = data;
                        } catch (e) {
                            console.error('Gagal ambil data laporan:', e);
                        }
                    }
                },

                get totalRevenue() {
                    return this.allOrders
                        .filter(o => o.status !== 'canceled')
                        .reduce((sum, o) => sum + parseFloat(o.total), 0);
                },

                get averageOrder() {
                    const paid = this.allOrders.filter(o => o.status !== 'canceled');
                    return paid.length ? Math.round(this.totalRevenue / paid.length) : 0;
                }
            }));
        });
    </script>

    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</body>
</html>
