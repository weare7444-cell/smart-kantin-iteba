<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan &mdash; {{ $stall['name'] ?? 'Stand' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; color: #222; padding: 30px; font-size: 12px; }
        h1 { font-size: 20px; text-align: center; margin-bottom: 2px; }
        .subtitle { text-align: center; color: #888; font-size: 11px; margin-bottom: 18px; }
        .header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 10px; margin-bottom: 16px; }
        .stats { margin-bottom: 20px; }
        .stats table { width: 100%; border-collapse: collapse; }
        .stats td { border: 1px solid #ccc; padding: 10px 14px; text-align: center; width: 33.33%; }
        .stat-label { font-size: 9px; text-transform: uppercase; color: #888; }
        .stat-value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .stat-green { color: #059669; }
        .stat-amber { color: #d97706; }
        .stat-blue { color: #0284c7; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; font-size: 10px; }
        table.data th { background: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #888; }
        .footer { text-align: center; margin-top: 24px; font-size: 9px; color: #aaa; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge { font-size: 8px; padding: 1px 6px; border: 1px solid #ccc; }
        .badge-pending { border-color: #d97706; color: #d97706; }
        .badge-processing { border-color: #0284c7; color: #0284c7; }
        .badge-ready { border-color: #059669; color: #059669; }
        .badge-completed { border-color: #6b7280; color: #6b7280; }
        .badge-canceled { border-color: #dc2626; color: #dc2626; }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <p class="subtitle">
            {{ $stall['name'] ?? 'Stand' }} &mdash; Smart-Kantin ITEBA<br>
            Dicetak: {{ $date }}
        </p>
    </div>

    @php
        $paid = $orders->filter(function($o) { return $o->status !== 'canceled'; });
        $totalRevenue = $paid->sum('total');
        $totalCount = count($orders);
        $avgOrder = $paid->count() > 0 ? round($totalRevenue / $paid->count()) : 0;
    @endphp

    <div class="stats">
        <table>
            <tr>
                <td>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value stat-green">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="stat-label">Total Pesanan</div>
                    <div class="stat-value stat-amber">{{ $totalCount }}</div>
                </td>
                <td>
                    <div class="stat-label">Rata-rata / Pesanan</div>
                    <div class="stat-value stat-blue">Rp {{ number_format($avgOrder, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Pelanggan</th>
                <th>Items</th>
                <th class="text-right">Total</th>
                <th class="text-center">Jam</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td class="text-muted">{{ $order->id }}</td>
                <td class="text-bold">{{ $order->user->name ?? '-' }}</td>
                <td>
                    @foreach ($order->items as $idx => $item)
                        {{ $item['qty'] }}x {{ $item['name'] }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td class="text-right text-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                <td class="text-center">{{ $order->pickup_time }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $order->status }}">{{ $order->status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">Belum ada riwayat pesanan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Smart-Kantin ITEBA &mdash; Laporan digenerate otomatis {{ $date }}
    </div>

</body>
</html>
