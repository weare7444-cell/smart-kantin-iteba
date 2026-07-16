<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan — {{ $stall['name'] ?? 'Stand' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; color: #222; padding: 40px; font-size: 12px; }
        h1 { font-size: 22px; text-align: center; margin-bottom: 2px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        .header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 12px; margin-bottom: 20px; }
        .stats { display: flex; gap: 8px; margin-bottom: 24px; }
        .stat-box { border: 1px solid #ccc; padding: 12px 16px; text-align: center; flex: 1; }
        .stat-label { font-size: 10px; text-transform: uppercase; color: #888; }
        .stat-value { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .stat-green { color: #059669; }
        .stat-amber { color: #d97706; }
        .stat-blue { color: #0284c7; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 8px 10px; text-align: left; font-size: 11px; }
        th { background: #eee; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #888; }
        .footer { text-align: center; margin-top: 28px; font-size: 10px; color: #aaa; border-top: 1px solid #ddd; padding-top: 12px; }
        .badge { font-size: 9px; padding: 2px 8px; border: 1px solid #999; }
        .no-print { display: block; text-align: center; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 8px 20px; font-size: 13px; border: 1px solid #ccc; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #e5e5e5; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0.5in; }
            th { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stat-green { color: #059669 !important; }
            .stat-amber { color: #d97706 !important; }
            .stat-blue { color: #0284c7 !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <p style="margin-bottom:10px;">Halaman ini akan otomatis membuka dialog print.</p>
        <button class="btn" onclick="window.print()">🖨️ Cetak / Print</button>
        <button class="btn" onclick="window.close()">✕ Tutup</button>
    </div>

    <div class="header">
        <h1>📊 LAPORAN PENJUALAN</h1>
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
        <div class="stat-box">
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value stat-green">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Pesanan</div>
            <div class="stat-value stat-amber">{{ $totalCount }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Rata-rata / Pesanan</div>
            <div class="stat-value stat-blue">Rp {{ number_format($avgOrder, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
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
                    @foreach ($order->items as $item)
                        {{ $item['qty'] }}x {{ $item['name'] }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td class="text-right text-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                <td class="text-center">{{ $order->pickup_time }}</td>
                <td class="text-center">
                    <span class="badge">{{ $order->status }}</span>
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
