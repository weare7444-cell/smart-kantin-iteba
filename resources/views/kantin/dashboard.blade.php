<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart-Kantin ITEBA</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            const authRole = '{{ $authUser->role }}';
            const authName = '{{ $authUser->name }}';
            const authStallId = {{ $authUser->stall_id ?? 'null' }};
            const myStallData = @json($myStall ?? null);
            const baseUrl = '{{ url('/') }}';

            Alpine.data('kantinApp', () => ({
                currentRole: authRole,
                userName: authName,
                selectedStall: authRole === 'penjual' ? (myStallData?.id ?? null) : null,
                baseUrl: baseUrl,

                stalls: @json($stalls),

                foods: [],

                orders: [],

                cart: [],
                pickupTimeInput: '',
                notification: false,
                notificationMessage: '',
                rejectDialog: false,
                rejectOrderId: null,
                showQris: false,
                showAddMenu: false,
                newMenuName: '',
                newMenuPrice: '',
                newMenuCategory: 'makanan',
                pendingBooking: null,
                _refreshTimer: null,

                init() {
                    if (this.currentRole === 'penjual' && this.selectedStall) {
                        this.fetchOrders();
                        this.fetchFoods();
                        this._refreshTimer = setInterval(() => this.fetchOrders(), 5000);
                    }
                },

                async fetchFoods() {
                    if (!this.selectedStall) return;
                    try {
                        const res = await fetch(this.baseUrl + `/api/foods/${this.selectedStall}`);
                        this.foods = await res.json();
                    } catch (e) {
                        console.error('Gagal ambil menu:', e);
                    }
                },

                async fetchOrders() {
                    if (!this.selectedStall) return;
                    try {
                        const res = await fetch(this.baseUrl + `/api/orders/${this.selectedStall}`);
                        const data = await res.json();
                        this.orders = data;
                    } catch (e) {
                        console.error('Gagal ambil orders:', e);
                    }
                },

                async refreshOrders() {
                    await this.fetchOrders();
                },

                get cartTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get cartCount() {
                    return this.cart.reduce((sum, item) => sum + item.qty, 0);
                },

                get currentStall() {
                    return this.stalls.find(s => s.id === this.selectedStall) || null;
                },

                get foodsByStall() {
                    return this.foods.filter(f => f.stall_id === this.selectedStall);
                },

                get stallOrders() {
                    return this.orders.filter(o => o.stall_id === this.selectedStall);
                },

                get ordersPending() {
                    return this.stallOrders.filter(o => o.status === 'pending');
                },

                get ordersProcessing() {
                    return this.stallOrders.filter(o => o.status === 'processing');
                },

                get ordersReady() {
                    return this.stallOrders.filter(o => o.status === 'ready');
                },

                get foodsAvailable() {
                    return this.foodsByStall.filter(f => f.is_ready);
                },

                selectStall(stallId) {
                    this.selectedStall = stallId;
                    this.cart = [];
                    this.fetchFoods();
                    if (this.currentRole === 'penjual') this.fetchOrders();
                },

                backToStalls() {
                    this.selectedStall = null;
                    this.cart = [];
                },

                async updateOrderStatus(orderId, newStatus) {
                    try {
                        const res = await fetch(this.baseUrl + `/api/order/${orderId}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                            body: JSON.stringify({ status: newStatus }),
                        });
                        if (res.ok) await this.fetchOrders();
                    } catch (e) {
                        console.error('Gagal update status:', e);
                    }
                },

                async toggleStock(foodId) {
                    try {
                        const res = await fetch(this.baseUrl + `/api/food/${foodId}/ready`, {
                            method: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                        });
                        if (res.ok) {
                            const updated = await res.json();
                            const idx = this.foods.findIndex(f => f.id === foodId);
                            if (idx !== -1) this.foods[idx] = updated;
                        }
                    } catch (e) {
                        console.error('Gagal toggle stok:', e);
                    }
                },

                addToCart(food) {
                    const existing = this.cart.find(item => item.id === food.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({ id: food.id, name: food.name, price: food.price, qty: 1 });
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                showRejectDialog(orderId) {
                    this.rejectOrderId = orderId;
                    this.rejectDialog = true;
                },

                async confirmReject() {
                    try {
                        const res = await fetch(this.baseUrl + `/api/order/${this.rejectOrderId}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                            body: JSON.stringify({ status: 'canceled' }),
                        });
                        if (res.ok) await this.fetchOrders();
                    } catch (e) {
                        console.error('Gagal tolak order:', e);
                    }
                    this.rejectDialog = false;
                    this.rejectOrderId = null;
                },

                cancelReject() {
                    this.rejectDialog = false;
                    this.rejectOrderId = null;
                },

                submitBooking() {
                    if (this.cart.length === 0) return;
                    if (!this.pickupTimeInput) return;

                    const items = this.cart.map(item => ({
                        name: item.name,
                        qty: item.qty,
                        price: item.price
                    }));

                    this.pendingBooking = {
                        user_name: this.userName,
                        total: this.cartTotal,
                        pickup_time: this.pickupTimeInput,
                        status: 'pending',
                        stall_id: this.selectedStall,
                        items: items
                    };

                    this.showQris = true;
                },

                async confirmPayment() {
                    try {
                        const res = await fetch(this.baseUrl + '/api/order', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                            body: JSON.stringify({
                                stall_id: this.pendingBooking.stall_id,
                                total: this.pendingBooking.total,
                                pickup_time: this.pendingBooking.pickup_time,
                                items: this.pendingBooking.items,
                            }),
                        });
                        if (res.ok) {
                            const order = await res.json();
                            this.orders.push(order);
                            this.notificationMessage = `Pesanan berhasil dibooking untuk jam ${order.pickup_time} di ${this.currentStall?.name}!`;
                        } else {
                            this.notificationMessage = 'Gagal membooking pesanan. Coba lagi.';
                        }
                    } catch (e) {
                        this.notificationMessage = 'Gagal terhubung ke server.';
                    }
                    this.notification = true;
                    this.showQris = false;
                    this.pendingBooking = null;
                    this.cart = [];
                    this.pickupTimeInput = '';

                    setTimeout(() => { this.notification = false; }, 6000);
                },

                cancelPayment() {
                    this.showQris = false;
                    this.pendingBooking = null;
                },

                printReceiptPenjual(order) {
                    if (!order || !this.currentStall) return;
                    const stall = this.currentStall;
                    const d = new Date();
                    const dateStr = d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    const win = window.open('', '_blank');
                    const html = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #${order.id}</title>
    <script src="https://cdn.tailwindcss.com"><\/script>
    <style>
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none !important; border: none !important; }
        }
        .receipt { width: 300px; font-family: 'Courier New', monospace; }
    <\/style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-xl shadow-lg p-6 receipt">
        <div class="text-center border-b-2 border-dashed border-gray-300 pb-3 mb-3">
            <p class="text-lg font-bold text-gray-800">🍽️ Smart-Kantin</p>
            <p class="text-xs text-gray-500">ITEBA</p>
            <p class="text-xs text-gray-500 mt-1">${stall.name}</p>
        </div>
        <div class="text-xs text-gray-600 mb-3">
            <p>No: #${order.id}</p>
            <p>Tgl: ${dateStr}</p>
            <p>Ambil: ${order.pickup_time}</p>
            <p>Pelanggan: ${order.user_name}</p>
        </div>
        <div class="border-t border-b border-dashed border-gray-300 py-2 mb-3">
            <div class="flex justify-between text-[11px] font-semibold text-gray-700 mb-1">
                <span>Item</span>
                <span>Subtotal</span>
            </div>
            ${order.items.map(item => `
                <div class="flex justify-between text-[11px] text-gray-600">
                    <span>${item.name} x${item.qty}</span>
                    <span>Rp${(item.qty * (item.price || 0)).toLocaleString('id-ID')}</span>
                </div>
            `).join('')}
        </div>
        <div class="flex justify-between text-sm font-bold text-gray-800 mb-3">
            <span>TOTAL</span>
            <span>Rp${order.total.toLocaleString('id-ID')}</span>
        </div>
        <div class="text-center border-t-2 border-dashed border-gray-300 pt-3">
            <p class="text-xs font-semibold text-emerald-600 mb-1">✅ LUNAS — QRIS</p>
            <p class="text-[10px] text-gray-400">Terima kasih telah memesan!</p>
        </div>
        <button onclick="window.print()" class="no-print mt-4 w-full bg-amber-500 hover:bg-amber-400 text-gray-950 font-semibold text-sm px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]">🖨️ Cetak Struk</button>
        <button onclick="window.close()" class="no-print mt-2 w-full bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]">Tutup</button>
    </div>
</body>
</html>`;
                    win.document.write(html);
                    win.document.close();
                },

                showAddMenuModal() {
                    this.newMenuName = '';
                    this.newMenuPrice = '';
                    this.newMenuCategory = 'makanan';
                    this.showAddMenu = true;
                },

                hideAddMenu() {
                    this.showAddMenu = false;
                },

                async submitAddMenu() {
                    if (!this.newMenuName.trim() || !this.newMenuPrice) return;
                    try {
                        const res = await fetch(this.baseUrl + '/api/food', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                            body: JSON.stringify({
                                stall_id: this.selectedStall,
                                name: this.newMenuName.trim(),
                                price: parseInt(this.newMenuPrice),
                                category: this.newMenuCategory,
                            }),
                        });
                        if (res.ok) {
                            const food = await res.json();
                            this.foods.push(food);
                            this.showAddMenu = false;
                            this.notificationMessage = 'Menu berhasil ditambahkan!';
                            this.notification = true;
                            setTimeout(() => { this.notification = false; }, 4000);
                        }
                    } catch (e) {
                        console.error('Gagal tambah menu:', e);
                    }
                },

                async deleteMenu(foodId) {
                    if (!confirm('Hapus menu ini?')) return;
                    try {
                        const res = await fetch(this.baseUrl + `/api/food/${foodId}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                        });
                        if (res.ok) {
                            this.foods = this.foods.filter(f => f.id !== foodId);
                            this.notificationMessage = 'Menu dihapus';
                            this.notification = true;
                            setTimeout(() => { this.notification = false; }, 4000);
                        }
                    } catch (e) {
                        console.error('Gagal hapus menu:', e);
                    }
                },
            }));
        });
    </script>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased">

    <div x-data="kantinApp()" class="min-h-screen flex flex-col">

        {{-- HEADER / ROLE SWITCHER --}}
        <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between sticky top-0 z-50">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🍽️</span>
                <h1 class="text-xl font-bold text-white tracking-tight">
                    Smart<span class="text-amber-400">-Kantin</span>
                    <span class="text-sm font-normal text-gray-500">ITEBA</span>
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden sm:flex items-center gap-2 text-sm text-gray-400">
                    <span class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-[10px] font-semibold text-white">
                        {{ substr($authUser->name, 0, 1) }}
                    </span>
                    <span x-text="userName" class="text-gray-300"></span>
                </span>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-red-400 text-sm px-2 py-1 transition-all duration-300 ease-out hover:scale-[1.02]" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        {{-- ROLE BADGE --}}
        <div class="px-6 pt-4 pb-2">
            <div
                x-show="currentRole === 'mahasiswa'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 text-amber-400 text-xs font-medium"
            >
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span x-text="selectedStall ? 'Mode Mahasiswa — Pesan di ' + currentStall?.name : 'Mode Mahasiswa — Pilih stand kantin'"></span>
            </div>
            <div
                x-show="currentRole === 'penjual'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-medium"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span x-text="'Mode Penjual — ' + currentStall?.name"></span>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 px-6 pb-8">

            {{-- ===================== STALLS GRID (when no stall selected) ===================== --}}
            <section x-show="!selectedStall && currentRole === 'mahasiswa'" x-cloak>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-white">Pilih Stand Kantin</h2>
                    <span class="text-xs text-gray-500">{{ count($stalls) }} stand tersedia</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <template x-for="stall in stalls" :key="stall.id">
                        <div
                            @click="selectStall(stall.id)"
                            class="bg-gray-900 border border-gray-800 rounded-xl p-5 cursor-pointer transition-all duration-300 ease-out hover:scale-[1.02] hover:border-amber-500/40 hover:shadow-lg hover:shadow-amber-500/5 hover:-translate-y-0.5"
                        >
                            <div class="text-3xl mb-3" x-text="stall.icon"></div>
                            <h3 class="text-sm font-semibold text-white" x-text="stall.name"></h3>
                            <p class="text-[11px] text-gray-500 mt-1" x-text="stall.specialty"></p>
                            <div class="mt-3 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] text-emerald-400 font-medium">
                                    <span x-text="foods.filter(f => f.stall_id === stall.id && f.is_ready).length"></span> menu tersedia
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            {{-- ===================== DETAIL STAND VIEW (when stall is selected) ===================== --}}
            <section x-show="selectedStall" x-cloak>

                {{-- BACK BUTTON (only for mahasiswa) --}}
                <button
                    x-show="currentRole === 'mahasiswa'"
                    @click="backToStalls()"
                    class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-amber-400 transition-all duration-300 ease-out hover:scale-[1.02]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke daftar stand
                </button>

                {{-- STALL HEADER --}}
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl" x-text="currentStall?.icon"></span>
                    <div>
                        <h2 class="text-lg font-semibold text-white" x-text="currentStall?.name"></h2>
                        <p class="text-xs text-gray-500" x-text="currentStall?.specialty"></p>
                    </div>
                </div>

                {{-- ============ MAHASISWA VIEW (within stall) ============ --}}
                <div x-show="currentRole === 'mahasiswa'" class="lg:grid lg:grid-cols-3 gap-6">

                    {{-- NOTIFICATION BANNER --}}
                    <div
                        x-show="notification"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="col-span-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 px-5 py-3 rounded-xl flex items-center gap-3 text-sm font-medium"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="notificationMessage"></span>
                    </div>

                    {{-- LEFT COLUMN — FOOD GRID --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-white">Menu Tersedia</h2>
                            <span class="text-xs text-gray-500" x-text="`${foodsAvailable.length} item`"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <template x-for="food in foodsByStall" :key="food.id">
                                <div
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-4"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="relative bg-gray-900 rounded-xl border overflow-hidden transition-all duration-300 ease-out hover:scale-[1.02]"
                                    :class="food.is_ready
                                        ? 'border-gray-800 hover:border-amber-500/40 hover:shadow-lg hover:shadow-amber-500/5'
                                        : 'border-gray-800/50 opacity-60'"
                                >
                                    <div
                                        x-show="!food.is_ready"
                                        class="absolute inset-0 z-10 flex items-center justify-center"
                                    >
                                        <span class="bg-red-500/20 text-red-400 text-xs font-bold px-3 py-1 rounded-full border border-red-500/30 uppercase tracking-wider">
                                            Stok Habis
                                        </span>
                                    </div>

                                    <div class="px-4 pt-3 flex items-center justify-between">
                                        <span
                                            class="text-[10px] font-medium uppercase tracking-widest px-2 py-0.5 rounded-full"
                                            :class="food.category === 'makanan'
                                                ? 'bg-amber-500/10 text-amber-400'
                                                : 'bg-sky-500/10 text-sky-400'"
                                            x-text="food.category"
                                        ></span>
                                        <span
                                            x-show="food.is_ready"
                                            class="w-2 h-2 rounded-full bg-emerald-500"
                                        ></span>
                                    </div>

                                    <div class="px-4 pt-2 pb-4">
                                        <h3 class="text-sm font-semibold text-white leading-snug line-clamp-2" x-text="food.name"></h3>
                                        <p class="mt-1.5 text-lg font-bold text-amber-400">
                                            Rp <span x-text="food.price.toLocaleString('id-ID')"></span>
                                        </p>

                                        <button
                                            x-show="food.is_ready"
                                            @click="addToCart(food)"
                                            class="mt-3 w-full text-sm font-medium px-3 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                            :class="cart.some(c => c.id === food.id)
                                                ? 'bg-amber-500 text-gray-950 hover:bg-amber-400'
                                                : 'bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white'"
                                        >
                                            <span x-text="cart.some(c => c.id === food.id) ? 'Tambah Lagi +' : '+ Tambah ke Keranjang'"></span>
                                        </button>

                                        <button
                                            x-show="!food.is_ready"
                                            disabled
                                            class="mt-3 w-full text-sm font-medium px-3 py-2 rounded-lg bg-gray-800/50 text-gray-600 cursor-not-allowed"
                                        >
                                            Stok Habis
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN — CART --}}
                    <div class="lg:col-span-1">
                        <div class="sticky top-24 bg-gray-900 border border-gray-800 rounded-xl p-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-sm font-semibold text-white">Pesanan Saya</h2>
                                <span
                                    class="text-xs font-medium px-2 py-0.5 rounded-full"
                                    :class="cartCount > 0
                                        ? 'bg-amber-500/15 text-amber-400'
                                        : 'bg-gray-800 text-gray-500'"
                                    x-text="`${cartCount} item`"
                                ></span>
                            </div>

                            <div class="max-h-[300px] overflow-y-auto scroll-smooth space-y-0">
                            <div x-show="cart.length === 0" class="text-center py-6">
                                <p class="text-gray-600 text-sm">Keranjang masih kosong</p>
                                <p class="text-gray-700 text-xs mt-1">Pilih menu di samping</p>
                            </div>

                            <template x-for="(item, index) in cart" :key="item.id">
                                <div
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-3"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="flex items-center justify-between py-2 border-b border-gray-800 last:border-b-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-200 truncate" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500">
                                            <span x-text="item.qty"></span> x Rp <span x-text="item.price.toLocaleString('id-ID')"></span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-3">
                                        <span class="text-sm font-semibold text-amber-400 whitespace-nowrap">
                                            Rp <span x-text="(item.price * item.qty).toLocaleString('id-ID')"></span>
                                        </span>
                                        <button
                                            @click="removeFromCart(index)"
                                            class="text-gray-600 hover:text-red-400 transition-all duration-300 ease-out hover:scale-110 p-1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            </div>

                            <div x-show="cart.length > 0" class="pt-2 border-t border-gray-700">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Total</span>
                                    <span class="text-lg font-bold text-amber-400">
                                        Rp <span x-text="cartTotal.toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                            </div>

                            <div x-show="cart.length > 0" class="space-y-3 pt-1">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1.5">Jam Pengambilan</label>
                                    <input
                                        type="time"
                                        x-model="pickupTimeInput"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                                    >
                                </div>
                                <button
                                    @click="submitBooking"
                                    class="w-full bg-amber-500 hover:bg-amber-400 text-gray-950 font-semibold text-sm px-4 py-2.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02] flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Kirim Booking Antrean
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ============ PENJUAL VIEW (within stall) ============ --}}
                <div x-show="currentRole === 'penjual'" class="space-y-8">

                    {{-- PENJUAL HEADER --}}
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-lg font-semibold text-white">👨‍🍳 Panel Penjual</h2>
                        <a href="{{ route('laporan.penjual') }}"
                           class="text-sm bg-gray-800 hover:bg-gray-700 text-amber-400 font-medium px-4 py-2 rounded-lg transition-all">
                            📊 Laporan Penjualan
                        </a>
                    </div>

                    {{-- KANBAN BOARD --}}
                    <div>
                        <h2 class="text-lg font-semibold text-white mb-4">📋 Antrean Masuk — <span x-text="currentStall?.name" class="text-amber-400"></span></h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            {{-- COLUMN 1: PENDING --}}
                            <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 overflow-y-auto max-h-[580px] scroll-smooth">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-gray-300 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                        Pending
                                    </h3>
                                    <span class="text-xs bg-amber-500/10 text-amber-400 px-2 py-0.5 rounded-full font-medium" x-text="ordersPending.length"></span>
                                </div>
                                <div class="space-y-3">
                                    <template x-for="order in ordersPending" :key="order.id">
                                        <div
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 translate-y-4"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            class="bg-gray-800/50 border border-gray-700/50 rounded-lg p-3.5 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-white" x-text="order.user_name"></span>
                                                <span class="text-[10px] text-gray-500 font-mono">#<span x-text="order.id"></span></span>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <span>⏱️ Ambil jam </span>
                                                <span class="text-amber-400 font-medium" x-text="order.pickup_time"></span>
                                            </div>
                                            <div class="text-xs text-gray-500 space-y-0.5">
                                                <template x-for="item in order.items" :key="item.name">
                                                    <div class="flex justify-between">
                                                        <span x-text="`${item.qty}x ${item.name}`"></span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-1.5 pt-1">
                                                <span class="text-xs font-semibold text-amber-400 mr-auto">Rp <span x-text="order.total.toLocaleString('id-ID')"></span></span>
                                                <button
                                                    @click="showRejectDialog(order.id)"
                                                    class="text-xs font-medium bg-red-500/15 text-red-400 hover:bg-red-500/25 px-2.5 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                                >
                                                    Tolak
                                                </button>
                                                <button
                                                    @click="updateOrderStatus(order.id, 'processing')"
                                                    class="text-xs font-medium bg-amber-500/15 text-amber-400 hover:bg-amber-500/25 px-2.5 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                                >
                                                    Terima &amp; Proses
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="ordersPending.length === 0" class="text-center py-6">
                                        <p class="text-gray-600 text-sm">Tidak ada antrean pending</p>
                                    </div>
                                </div>
                            </div>

                            {{-- COLUMN 2: PROCESSING --}}
                            <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 overflow-y-auto max-h-[580px] scroll-smooth">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-gray-300 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                                        Diproses
                                    </h3>
                                    <span class="text-xs bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded-full font-medium" x-text="ordersProcessing.length"></span>
                                </div>
                                <div class="space-y-3">
                                    <template x-for="order in ordersProcessing" :key="order.id">
                                        <div
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 translate-y-4"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            class="bg-gray-800/50 border border-sky-500/20 rounded-lg p-3.5 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-white" x-text="order.user_name"></span>
                                                <span class="text-[10px] text-gray-500 font-mono">#<span x-text="order.id"></span></span>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <span>⏱️ Ambil jam </span>
                                                <span class="text-amber-400 font-medium" x-text="order.pickup_time"></span>
                                            </div>
                                            <div class="text-xs text-gray-500 space-y-0.5">
                                                <template x-for="item in order.items" :key="item.name">
                                                    <div class="flex justify-between">
                                                        <span x-text="`${item.qty}x ${item.name}`"></span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex items-center justify-between pt-1">
                                                <span class="text-xs text-gray-500">Sedang dimasak...</span>
                                                <button
                                                    @click="updateOrderStatus(order.id, 'ready')"
                                                    class="text-xs font-medium bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 px-3 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                                >
                                                    Makanan Siap
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="ordersProcessing.length === 0" class="text-center py-6">
                                        <p class="text-gray-600 text-sm">Tidak ada pesanan diproses</p>
                                    </div>
                                </div>
                            </div>

                            {{-- COLUMN 3: READY --}}
                            <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 overflow-y-auto max-h-[580px] scroll-smooth">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-gray-300 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        Siap Diambil
                                    </h3>
                                    <span class="text-xs bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full font-medium" x-text="ordersReady.length"></span>
                                </div>
                                <div class="space-y-3">
                                    <template x-for="order in ordersReady" :key="order.id">
                                        <div
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 translate-y-4"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            class="bg-gray-800/50 border-2 rounded-lg p-3.5 space-y-2 animate-pulse-emerald"
                                            :class="order.status === 'ready' ? 'border-emerald-500/50' : 'border-emerald-500/20'"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-white" x-text="order.user_name"></span>
                                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <span>⏱️ Ambil jam </span>
                                                <span class="text-emerald-400 font-medium" x-text="order.pickup_time"></span>
                                            </div>
                                            <div class="text-xs text-gray-500 space-y-0.5">
                                                <template x-for="item in order.items" :key="item.name">
                                                    <div class="flex justify-between">
                                                        <span x-text="`${item.qty}x ${item.name}`"></span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="pt-1 space-y-1.5">
                                                <button
                                                    @click="updateOrderStatus(order.id, 'completed')"
                                                    class="w-full text-xs font-medium bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 px-3 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                                >
                                                    ✅ Selesai Diambil
                                                </button>
                                                <button
                                                    @click="printReceiptPenjual(order)"
                                                    class="w-full text-xs font-medium bg-amber-500/15 text-amber-400 hover:bg-amber-500/25 px-3 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                                >
                                                    🖨️ Cetak Struk
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="ordersReady.length === 0" class="text-center py-6">
                                        <p class="text-gray-600 text-sm">Belum ada makanan siap</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- STOK SWITCHER --}}
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-white">📦 Stok Switcher — <span x-text="currentStall?.name" class="text-amber-400"></span></h2>
                            <button
                                @click="showAddMenuModal()"
                                class="text-xs font-medium bg-amber-500/15 text-amber-400 hover:bg-amber-500/25 px-3 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                            >
                                + Tambah Menu
                            </button>
                        </div>
                        <div class="bg-gray-900/60 border border-gray-800 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-800/50">
                                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                                        <th class="px-5 py-3 font-medium">Menu</th>
                                        <th class="px-5 py-3 font-medium">Kategori</th>
                                        <th class="px-5 py-3 font-medium">Harga</th>
                                        <th class="px-5 py-3 font-medium">Status</th>
                                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    <template x-for="food in foodsByStall" :key="food.id">
                                        <tr class="transition-colors hover:bg-gray-800/30">
                                            <td class="px-5 py-3.5">
                                                <span class="text-white font-medium" x-text="food.name"></span>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <span
                                                    class="text-[10px] font-medium uppercase tracking-widest px-2 py-0.5 rounded-full"
                                                    :class="food.category === 'makanan'
                                                        ? 'bg-amber-500/10 text-amber-400'
                                                        : 'bg-sky-500/10 text-sky-400'"
                                                    x-text="food.category"
                                                ></span>
                                            </td>
                                            <td class="px-5 py-3.5 text-gray-400">
                                                Rp <span x-text="food.price.toLocaleString('id-ID')"></span>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <span
                                                    class="inline-flex items-center gap-1.5 text-xs font-medium"
                                                    :class="food.is_ready
                                                        ? 'text-emerald-400'
                                                        : 'text-red-400'"
                                                >
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full"
                                                        :class="food.is_ready ? 'bg-emerald-400' : 'bg-red-400'"
                                                    ></span>
                                                    <span x-text="food.is_ready ? 'Tersedia' : 'Habis'"></span>
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5 text-right space-x-2">
                                                <button
                                                    @click="toggleStock(food.id)"
                                                    class="text-xs font-medium px-3 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                                    :class="food.is_ready
                                                        ? 'bg-red-500/15 text-red-400 hover:bg-red-500/25'
                                                        : 'bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25'"
                                                    x-text="food.is_ready ? 'Tandai Habis' : 'Tandai Tersedia'"
                                                ></button>
                                                <button
                                                    @click="deleteMenu(food.id)"
                                                    class="text-xs font-medium bg-red-500/15 text-red-400 hover:bg-red-500/25 px-3 py-1.5 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                                                >
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </section>

        </main>

        {{-- REJECT CONFIRMATION MODAL --}}
        <div
            x-show="rejectDialog"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            @click.self="cancelReject"
        >
            <div
                x-show="rejectDialog"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-gray-900 border border-gray-700 rounded-xl p-6 max-w-sm w-full mx-4 shadow-2xl"
            >
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-500/15 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-white mb-1">Tolak Pesanan</h3>
                    <p class="text-sm text-gray-400 mb-1">
                        Yakin ingin menolak pesanan
                        <template x-for="order in orders.filter(o => o.id === rejectOrderId)" :key="order.id">
                            <span class="text-white font-medium">#<span x-text="order.id"></span> dari <span x-text="order.user_name"></span>?</span>
                        </template>
                    </p>
                    <p class="text-xs text-gray-500 mb-5">Pesanan akan dibatalkan dan dihapus dari antrean.</p>
                    <div class="flex gap-3">
                        <button
                            @click="cancelReject"
                            class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                        >
                            Batal
                        </button>
                        <button
                            @click="confirmReject"
                            class="flex-1 bg-red-500 hover:bg-red-400 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                        >
                            Ya, Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- QRIS PAYMENT MODAL --}}
        <div
            x-show="showQris"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            @click.self="cancelPayment"
        >
            <div
                x-show="showQris"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-gray-900 border border-gray-700 rounded-xl p-6 max-w-sm w-full mx-4 shadow-2xl text-center"
            >
                <div class="w-20 h-20 mx-auto mb-4 bg-white rounded-xl flex items-center justify-center p-2">
                    <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                        <rect x="10" y="10" width="35" height="35" rx="4" fill="#111" />
                        <rect x="55" y="10" width="35" height="35" rx="4" fill="#111" />
                        <rect x="10" y="55" width="35" height="35" rx="4" fill="#111" />
                        <rect x="55" y="55" width="35" height="35" rx="4" fill="#111" />
                        <circle cx="27.5" cy="27.5" r="8" fill="#22c55e" />
                        <circle cx="72.5" cy="27.5" r="8" fill="#22c55e" />
                        <circle cx="27.5" cy="72.5" r="8" fill="#22c55e" />
                        <circle cx="72.5" cy="72.5" r="8" fill="#22c55e" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-white mb-1">Pembayaran QRIS</h3>
                <p class="text-sm text-gray-400 mb-1">Scan kode QRIS di stand untuk membayar</p>
                <p class="text-lg font-bold text-emerald-400 mb-5">
                    Rp <span x-text="pendingBooking?.total.toLocaleString('id-ID')"></span>
                </p>

                <div class="bg-gray-800 rounded-lg px-4 py-3 mb-5 text-left text-xs text-gray-400 space-y-1">
                    <p><span class="text-gray-500">Stand:</span> <span class="text-white" x-text="currentStall?.name"></span></p>
                    <p><span class="text-gray-500">Ambil jam:</span> <span class="text-white" x-text="pendingBooking?.pickup_time"></span></p>
                    <p><span class="text-gray-500">Pesanan:</span></p>
                    <template x-for="item in pendingBooking?.items" :key="item.name">
                        <p class="pl-3">- <span x-text="`${item.qty}x ${item.name}`"></span></p>
                    </template>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="cancelPayment"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmPayment"
                        class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-gray-950 text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                    >
                        ✅ Bayar
                    </button>
                </div>
            </div>
        </div>

        {{-- TAMBAH MENU MODAL --}}
        <div
            x-show="showAddMenu"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            @click.self="hideAddMenu"
        >
            <div
                x-show="showAddMenu"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-gray-900 border border-gray-700 rounded-xl p-6 max-w-sm w-full mx-4 shadow-2xl"
            >
                <h3 class="text-base font-semibold text-white mb-4">Tambah Menu Baru</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nama Menu</label>
                        <input
                            type="text"
                            x-model="newMenuName"
                            placeholder="Cth: Nasi Goreng"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                        >
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Harga (Rp)</label>
                        <input
                            type="number"
                            x-model="newMenuPrice"
                            placeholder="15000"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                        >
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                        <select
                            x-model="newMenuCategory"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                        >
                            <option value="makanan">Makanan</option>
                            <option value="minuman">Minuman</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button
                        @click="hideAddMenu"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                    >
                        Batal
                    </button>
                    <button
                        @click="submitAddMenu"
                        class="flex-1 bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-300 ease-out hover:scale-[1.02]"
                    >
                        Simpan
                    </button>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <footer class="bg-gray-900 border-t border-gray-800 px-6 py-3 text-center text-xs text-gray-600">
            Smart-Kantin ITEBA &mdash; Tugas UAS Pemrograman Web &middot; Laravel + Alpine.js + Tailwind CSS
        </footer>

    </div>

    <style>
        [x-cloak] { display: none !important; }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }

        button:active, .btn:active {
            transform: scale(0.97) !important;
            transition-duration: 75ms !important;
        }

        @keyframes pulse-emerald {
            0%, 100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.15); }
            50% { box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); }
        }
        .animate-pulse-emerald {
            animation: pulse-emerald 2s ease-in-out infinite;
        }
    </style>
</body>
</html>
