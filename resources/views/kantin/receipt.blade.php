<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk — Smart-Kantin ITEBA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none !important; border: none !important; }
        }
        .receipt { width: 300px; font-family: 'Courier New', monospace; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white rounded-xl shadow-lg p-6 receipt">
        <div class="text-center border-b-2 border-dashed border-gray-300 pb-3 mb-3">
            <p class="text-lg font-bold text-gray-800">🍽️ Smart-Kantin</p>
            <p class="text-xs text-gray-500">ITEBA</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stall['name'] }}</p>
        </div>

        <div class="text-xs text-gray-600 mb-3">
            <p>No: #{{ $order['id'] }}</p>
            <p>Tgl: {{ date('d/m/Y H:i') }}</p>
            <p>Ambil: {{ $order['pickup_time'] }}</p>
            <p>Pelanggan: {{ $order['user_name'] }}</p>
        </div>

        <div class="border-t border-b border-dashed border-gray-300 py-2 mb-3">
            <div class="flex justify-between text-[11px] font-semibold text-gray-700 mb-1">
                <span>Item</span>
                <span>Subtotal</span>
            </div>
            @foreach ($order['items'] as $item)
            <div class="flex justify-between text-[11px] text-gray-600">
                <span>{{ $item['name'] }} x{{ $item['qty'] }}</span>
                <span>Rp{{ number_format($item['qty'] * ($item['price'] ?? 0), 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>

        <div class="flex justify-between text-sm font-bold text-gray-800 mb-3">
            <span>TOTAL</span>
            <span>Rp{{ number_format($order['total'], 0, ',', '.') }}</span>
        </div>

        <div class="text-center border-t-2 border-dashed border-gray-300 pt-3">
            <p class="text-xs font-semibold text-emerald-600 mb-1">✅ LUNAS — QRIS</p>
            <p class="text-[10px] text-gray-400">Terima kasih telah memesan!</p>
        </div>

        <button onclick="window.print()" class="no-print mt-4 w-full bg-amber-500 hover:bg-amber-400 text-gray-950 font-semibold text-sm px-4 py-2 rounded-lg transition-all">
            🖨️ Cetak Struk
        </button>
        <button onclick="window.close()" class="no-print mt-2 w-full bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg transition-all">
            Tutup
        </button>
    </div>

</body>
</html>
