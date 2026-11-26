<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->transaction_code }} - KassaRasa SmartCash</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f3f4f6;
            color: #111827;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .actions {
            margin-bottom: 16px;
            display: flex;
            gap: 8px;
        }
        .btn {
            font-family: system-ui, -apple-system, sans-serif;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary { background: #0E4D42; color: #fff; }
        .btn-wa { background: #16a34a; color: #fff; }
        .btn-back { background: #e5e7eb; color: #1f2937; }
        .receipt {
            width: 300px; /* ~80mm thermal */
            background: #fff;
            padding: 16px 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            font-size: 12px;
            line-height: 1.45;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .title { font-size: 15px; font-weight: bold; letter-spacing: 0.5px; }
        .muted { font-size: 11px; color: #4b5563; }
        .divider {
            border-top: 1px dashed #9ca3af;
            margin: 8px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .item { margin-bottom: 5px; }
        .item-name { font-weight: bold; }
        .total-row {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .actions { display: none !important; }
            .receipt { box-shadow: none; width: 100%; max-width: 78mm; padding: 4px; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak Struk</button>
        <a class="btn btn-wa" href="{{ $transaction->whatsapp_url }}" target="_blank">💬 Kirim WhatsApp</a>
        <a class="btn btn-back" href="{{ url('/admin/kasir-pos') }}">← Kembali</a>
    </div>

    <div class="receipt">
        <div class="center">
            <img src="{{ asset('images/logo-kassarasa.jpg') }}" alt="KassaRasa" style="width:48px;height:48px;border-radius:8px;margin-bottom:4px;object-fit:cover;">
            <div class="title">KASSARASA</div>
            <div class="muted bold">SMARTCASH UMKM KULINER</div>
            <div class="muted">Jl. Kuliner Nusantara No. 1</div>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span>No. Struk</span>
            <span class="bold">{{ $transaction->transaction_code }}</span>
        </div>
        <div class="row">
            <span>Tanggal</span>
            <span>{{ $transaction->transaction_date?->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Kasir</span>
            <span>{{ $transaction->user->name ?? 'Admin' }}</span>
        </div>
        @if($transaction->customer_name)
        <div class="row">
            <span>Pelanggan</span>
            <span>{{ $transaction->customer_name }}</span>
        </div>
        @endif

        <div class="divider"></div>

        @foreach($transaction->transactionDetails as $detail)
            <div class="item">
                <div class="item-name">{{ $detail->product->name ?? 'Menu' }}</div>
                <div class="row">
                    <span>{{ $detail->quantity }} x Rp {{ number_format((float)$detail->price, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format((float)$detail->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach

        <div class="divider"></div>

        <div class="row total-row">
            <span>TOTAL</span>
            <span>Rp {{ number_format((float)$transaction->total_price, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Metode</span>
            <span class="bold">{{ strtoupper($transaction->payment_method) }}</span>
        </div>
        @if((float)$transaction->cash_received > 0)
        <div class="row">
            <span>Tunai</span>
            <span>Rp {{ number_format((float)$transaction->cash_received, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Kembali</span>
            <span>Rp {{ number_format((float)$transaction->change_amount, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="divider"></div>

        <div class="center muted" style="margin-top: 8px;">
            Terima kasih atas kunjungan Anda!<br>
            Selamat menikmati hidangan kami.<br>
            Powered by KassaRasa • SmartCash
        </div>
    </div>

    <script>
        // Auto-buka dialog print saat halaman dibuka dengan parameter ?auto=1
        if (new URLSearchParams(window.location.search).get('auto') === '1') {
            window.addEventListener('load', () => setTimeout(() => window.print(), 300));
        }
    </script>
</body>
</html>
