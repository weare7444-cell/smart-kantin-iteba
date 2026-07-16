<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public static function stalls(): array
    {
        return [
            ['id' => 1,  'name' => 'Stand Pak Gembus',       'icon' => '🍗', 'specialty' => 'Ayam Penyet & Gorengan',        'color' => 'amber'],
            ['id' => 2,  'name' => 'Es Teh Puncak',           'icon' => '🧊', 'specialty' => 'Es Teh & Minuman Segar',         'color' => 'sky'],
            ['id' => 3,  'name' => 'Nasi Goreng Mang Udin',   'icon' => '🍳', 'specialty' => 'Nasi Goreng Spesial',           'color' => 'orange'],
            ['id' => 4,  'name' => 'Mie Kocok Bandung',       'icon' => '🍜', 'specialty' => 'Mie Kocok & Bakso',             'color' => 'red'],
            ['id' => 5,  'name' => 'Sate Padang Ajo',         'icon' => '🍢', 'specialty' => 'Sate Padang & Lontong',         'color' => 'lime'],
            ['id' => 6,  'name' => 'Martabak Bang Jago',      'icon' => '🫓', 'specialty' => 'Martabak Telur & Manis',        'color' => 'yellow'],
            ['id' => 7,  'name' => 'Es Campur Nyak',          'icon' => '🥤', 'specialty' => 'Es Campur & Minuman Manis',      'color' => 'teal'],
            ['id' => 8,  'name' => 'Pisang Goreng Ibu',       'icon' => '🍌', 'specialty' => 'Pisang Goreng & Cemilan',       'color' => 'stone'],
            ['id' => 9,  'name' => 'Soto Betawi Haji',        'icon' => '🥣', 'specialty' => 'Soto Betawi & Lontong',         'color' => 'rose'],
            ['id' => 10, 'name' => 'Kopi Senja',              'icon' => '☕', 'specialty' => 'Kopi & Minuman Hangat',         'color' => 'violet'],
        ];
    }

    public function mahasiswa()
    {
        return view('kantin.dashboard', [
            'authUser' => Auth::user(),
            'roleView' => 'mahasiswa',
            'stalls' => self::stalls(),
        ]);
    }

    public function penjual()
    {
        $user = Auth::user();
        $stalls = self::stalls();
        $myStall = null;

        if ($user->stall_id) {
            foreach ($stalls as $s) {
                if ($s['id'] === $user->stall_id) {
                    $myStall = $s;
                    break;
                }
            }
        }

        return view('kantin.dashboard', [
            'authUser' => $user,
            'roleView' => 'penjual',
            'stalls' => $stalls,
            'myStall' => $myStall,
        ]);
    }

    public function laporan()
    {
        $user = Auth::user();
        $stalls = self::stalls();
        $myStall = null;

        if ($user->stall_id) {
            foreach ($stalls as $s) {
                if ($s['id'] === $user->stall_id) {
                    $myStall = $s;
                    break;
                }
            }
        }

        return view('kantin.sales-report', [
            'authUser' => $user,
            'myStall' => $myStall,
        ]);
    }

    public function laporanPrint()
    {
        $user = Auth::user();
        $orders = Order::with('user')
            ->where('stall_id', $user->stall_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $stalls = self::stalls();
        $myStall = null;
        if ($user->stall_id) {
            foreach ($stalls as $s) {
                if ($s['id'] === $user->stall_id) {
                    $myStall = $s;
                    break;
                }
            }
        }

        return view('kantin.laporan-print', [
            'orders' => $orders,
            'stall'  => $myStall,
            'date'   => date('d/m/Y H:i'),
        ]);
    }
}
