@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Catatan Pemasukan dan Pengeluaran</h2>

    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('laporan.transaksi.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block font-medium text-sm mb-1">Jenis Transaksi</label>
                <select id="jenis-transaksi" name="jenis" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
                    <option value="">-- Pilih --</option>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-sm mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
            </div>
        </div>

        <div class="mb-4">
            <label class="block font-medium text-sm mb-1">Deskripsi</label>
            <input type="text" id="deskripsi-input" name="deskripsi" placeholder="Pilih jenis transaksi terlebih dahulu" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400" required>
        </div>

        <div id="produk-section" class="mb-4 hidden">
            <label class="block font-medium text-sm mb-1">Daftar Produk</label>
            <div id="produk-container" class="space-y-3"></div>
            <button type="button" id="tambah-produk" class="mt-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">+ Tambah Produk</button>
        </div>

        <div class="mb-4 hidden" id="total-section">
            <label class="block font-medium text-lg mb-1">Total Pembayaran</label>
            <input type="text" id="total-harga" readonly class="w-full text-2xl font-bold text-right bg-gray-100 border px-3 py-2 rounded">
        </div>

        <div class="mb-4 hidden" id="uang-bayar-section">
            <label class="block font-medium text-sm mb-1">Uang Dibayar</label>
            <input type="number" id="uang-bayar" name="uang_bayar" min="0" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
        </div>

        <div class="mb-4 hidden" id="kembalian-section">
            <label class="block font-medium text-sm mb-1">Kembalian</label>
            <input type="text" id="kembalian" readonly class="w-full border border-gray-100 bg-gray-100 text-green-700 font-semibold px-3 py-2 rounded">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-3 rounded text-lg">Simpan & Bayar</button>
        </div>
    </form>

    <script>
        const produkData = @json($produks);

        function formatRupiah(angka) {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function hitungTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(sub => {
                total += parseInt(sub.getAttribute('data-value') || 0);
            });
            document.getElementById('total-harga').value = formatRupiah(total);
            updateKembalian();
        }

        function updateDeskripsi() {
            const deskripsiInput = document.getElementById('deskripsi-input');
            const jenis = document.getElementById('jenis-transaksi').value;
            const barisProduk = document.querySelectorAll('#produk-container > div');
            let isi = [];

            barisProduk.forEach(baris => {
                const nama = baris.querySelector('.produk-select')?.selectedOptions[0]?.text || '';
                const jumlah = baris.querySelector('.jumlah-beli').value;
                const satuan = baris.querySelector('.satuan-select').value;

                if (nama && jumlah > 0) {
                    isi.push(`${nama} ${jumlah} ${satuan}`);
                }
            });

            if (isi.length > 0) {
                deskripsiInput.value = (jenis === 'pengeluaran' ? 'Pembelian: ' : 'Penjualan: ') + isi.join(', ');
            }
        }

        function hitungSubtotalBaris(container) {
            const select = container.querySelector('.produk-select');
            const jenis = document.getElementById('jenis-transaksi').value;
            const harga = parseInt(select.selectedOptions[0].getAttribute(jenis === 'pemasukan' ? 'data-harga-jual' : 'data-harga-beli') || 0);
            const qty = parseFloat(container.querySelector('.jumlah-beli').value || 0);
            const subtotalInput = container.querySelector('.subtotal');
            const subtotal = harga * qty;

            subtotalInput.value = formatRupiah(subtotal);
            subtotalInput.setAttribute('data-value', subtotal);
        }

        function buatBarisProduk() {
            const container = document.createElement('div');
            container.className = 'flex flex-wrap items-end w-full gap-x-4 gap-y-2';
            container.innerHTML = `
                <select class="produk-select border rounded px-2 py-1 w-48" name="produk_id[]" required>
                    <option value="">Pilih Produk</option>
                    ${produkData.map(p => 
                        `<option value="${p.id}" data-harga-jual="${p.harga_jual}" data-harga-beli="${p.harga_beli}" data-nama="${p.nama.toLowerCase()}">${p.nama}</option>`
                    ).join('')}
                </select>
                <input type="text" class="harga-satuan border rounded px-2 py-1 bg-gray-100 text-right w-32" placeholder="Harga" readonly>
                <input type="number" step="any" name="qty[]" class="jumlah-beli border rounded px-2 py-1 text-right w-24" min="0.1" placeholder="Qty" required>
                <select class="satuan-select border rounded px-2 py-1 w-24" name="satuan[]">
                    <option value="pcs">pcs</option>
                    <option value="kg">kg</option>
                    <option value="butir">butir</option>
                    <option value="tabung">tabung</option>
                </select>
                <input type="text" class="subtotal border rounded px-2 py-1 bg-gray-100 text-right w-32" placeholder="Subtotal" readonly data-value="0">
                <button type="button" class="hapus-produk text-red-600 hover:text-red-800 font-bold">X</button>
            `;

            document.getElementById('produk-container').appendChild(container);

            const select = container.querySelector('.produk-select');
            const hargaInput = container.querySelector('.harga-satuan');
            const jumlahInput = container.querySelector('.jumlah-beli');
            const satuanSelect = container.querySelector('.satuan-select');
            const hapusBtn = container.querySelector('.hapus-produk');

            select.addEventListener('change', () => {
                const currentJenis = document.getElementById('jenis-transaksi').value;
                const harga = select.selectedOptions[0].getAttribute(
                    currentJenis === 'pemasukan' ? 'data-harga-jual' : 'data-harga-beli'
                );
                hargaInput.value = harga ? formatRupiah(harga) : '';
                hitungSubtotalBaris(container);
                updateDeskripsi();
                hitungTotal();
                updateSatuanOptions(container);
            });

            jumlahInput.addEventListener('input', () => {
                hitungSubtotalBaris(container);
                updateDeskripsi();
                hitungTotal();
            });

            satuanSelect.addEventListener('change', () => {
                hitungSubtotalBaris(container);
                updateDeskripsi();
                hitungTotal();
            });

            hapusBtn.addEventListener('click', () => {
                container.remove();
                updateDeskripsi();
                hitungTotal();
            });
        }

        function updateSatuanOptions(container) {
            const produkSelect = container.querySelector('.produk-select');
            const satuanSelect = container.querySelector('.satuan-select');
            const namaProduk = produkSelect?.selectedOptions[0]?.getAttribute('data-nama') || '';

            satuanSelect.innerHTML = ''; // Kosongkan dulu

            if (namaProduk.includes('tepung') || namaProduk.includes('beras') || namaProduk.includes('telur')) {
                satuanSelect.innerHTML = '<option value="kg">kg</option>';
            } else if (namaProduk.includes('gas')) {
                satuanSelect.innerHTML = '<option value="tabung">tabung</option>';
            } else {
                satuanSelect.innerHTML = '<option value="pcs">pcs</option>';
            }
        }

        function updateKembalian() {
            const total = parseInt(document.getElementById('total-harga').value.replace(/[^0-9]/g, '') || 0);
            const bayar = parseInt(document.getElementById('uang-bayar').value || 0);
            const kembali = bayar - total;
            document.getElementById('kembalian').value = kembali >= 0 ? formatRupiah(kembali) : 'Rp 0';
        }

        document.getElementById('uang-bayar').addEventListener('input', updateKembalian);
        document.getElementById('tambah-produk').addEventListener('click', buatBarisProduk);

        document.getElementById('jenis-transaksi').addEventListener('change', function () {
            const jenis = this.value;
            const produkSection = document.getElementById('produk-section');
            const totalSection = document.getElementById('total-section');
            const deskripsiInput = document.getElementById('deskripsi-input');
            const bayarSection = document.getElementById('uang-bayar-section');
            const kembaliSection = document.getElementById('kembalian-section');

            if (jenis === 'pemasukan' || jenis === 'pengeluaran') {
                produkSection.classList.remove('hidden');
                totalSection.classList.remove('hidden');

                if (jenis === 'pemasukan') {
                    bayarSection.classList.remove('hidden');
                    kembaliSection.classList.remove('hidden');
                } else {
                    bayarSection.classList.add('hidden');
                    kembaliSection.classList.add('hidden');
                }

                deskripsiInput.placeholder = jenis === 'pemasukan' ? 'Contoh: Penjualan Produk' : 'Contoh: Pembelian Barang';

                // Tambahkan baris pertama jika belum ada
                if (document.querySelectorAll('#produk-container > div').length === 0) {
                    buatBarisProduk();
                }
            }
        });
    </script>
</div>
@endsection