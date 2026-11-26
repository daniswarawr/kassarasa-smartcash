<x-filament-widgets::widget>
    <style>
        @keyframes krFloatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        @keyframes krPulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.45; transform: scale(1.35); }
        }
        @keyframes krReceiptSlide {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-4px); }
        }
        @keyframes krBarGrow1 {
            0%, 100% { transform: scaleY(0.75); }
            50% { transform: scaleY(1); }
        }
        @keyframes krBarGrow2 {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(0.7); }
        }
        @keyframes krFlowArrow {
            0% { opacity: 0.3; transform: translateX(-3px); }
            50% { opacity: 1; transform: translateX(3px); }
            100% { opacity: 0.3; transform: translateX(-3px); }
        }
        @keyframes krParticle {
            0% { transform: translateY(0px) scale(1); opacity: 0.25; }
            50% { transform: translateY(-16px) scale(1.2); opacity: 0.65; }
            100% { transform: translateY(0px) scale(1); opacity: 0.25; }
        }
        .kr-hero {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            background: linear-gradient(135deg, #09352d 0%, #0E4D42 55%, #146354 100%);
            color: #ffffff;
            padding: 24px 28px;
            box-shadow: 0 10px 25px -5px rgba(14, 77, 66, 0.35);
        }
        .kr-hero-grid {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            align-items: center;
        }
        @media (min-width: 1024px) {
            .kr-hero-grid {
                grid-template-columns: 1.05fr 1.35fr;
            }
        }
        .kr-roles-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            align-items: stretch;
        }
        @media (max-width: 640px) {
            .kr-roles-row {
                grid-template-columns: 1fr;
            }
        }
        .kr-role-card {
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            border-radius: 14px;
            padding: 14px 12px;
            text-align: center;
            transition: transform 0.25s ease, background 0.25s ease;
        }
        .kr-role-card:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.16);
        }
        .kr-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 15px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }
        .kr-btn:hover { transform: translateY(-1px); opacity: 0.95; }
        .kr-btn-lime { background: #A3C639; color: #09352d; }
        .kr-btn-glass { background: rgba(255,255,255,0.14); color: #ffffff; border: 1px solid rgba(255,255,255,0.28); }
    </style>

    <div class="kr-hero">
        {{-- Ambient floating particles (like underwater/lively hero background) --}}
        <div style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:1;">
            <span style="position:absolute;top:18%;left:8%;width:10px;height:10px;border-radius:999px;background:#A3C639;animation:krParticle 5s ease-in-out infinite;"></span>
            <span style="position:absolute;top:72%;left:22%;width:6px;height:6px;border-radius:999px;background:#ffffff;animation:krParticle 4.2s ease-in-out infinite 1s;"></span>
            <span style="position:absolute;top:25%;right:14%;width:12px;height:12px;border-radius:999px;background:#A3C639;animation:krParticle 6s ease-in-out infinite 0.6s;"></span>
            <span style="position:absolute;bottom:14%;right:32%;width:8px;height:8px;border-radius:999px;background:#ffffff;animation:krParticle 4.8s ease-in-out infinite 1.5s;"></span>
            <div style="position:absolute;right:-60px;top:-60px;width:240px;height:240px;border-radius:999px;background:radial-gradient(circle, rgba(163,198,57,0.22) 0%, rgba(163,198,57,0) 70%);"></div>
        </div>

        <div class="kr-hero-grid">
            {{-- Kiri: Sambutan & Aksi Cepat --}}
            <div>
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);padding:5px 12px;border-radius:9999px;font-size:11px;font-weight:700;letter-spacing:0.06em;">
                    <span style="width:8px;height:8px;border-radius:9999px;background:#A3C639;display:inline-block;animation:krPulseDot 1.8s infinite;"></span>
                    OPERASIONAL LIVE • {{ strtoupper($this->isOwner() ? 'OWNER / PEMILIK USAHA' : 'KASIR AKTIF') }}
                </div>

                <h2 style="margin-top:14px;font-size:24px;font-weight:800;line-height:1.25;letter-spacing:-0.01em;">
                    Halo, {{ $this->getUserName() }}!
                </h2>
                <p style="margin-top:8px;font-size:13.5px;line-height:1.55;color:rgba(255,255,255,0.86);max-width:460px;">
                    Satu ekosistem <strong>KassaRasa SmartCash</strong> yang menghubungkan kerja <strong>Kasir</strong>, <strong>Admin &amp; Stok</strong>, hingga <strong>Pemilik Usaha</strong> secara real-time.
                </p>

                <div style="margin-top:18px;display:flex;flex-wrap:wrap;gap:10px;">
                    <a href="{{ url('/admin/kasir-pos') }}" class="kr-btn kr-btn-lime">
                        🧾 Buka Kasir POS
                    </a>
                    <a href="{{ url('/admin/kitchen-display') }}" class="kr-btn kr-btn-glass">
                        🍳 Antrean Dapur
                    </a>
                    @if($this->isOwner())
                        <a href="{{ url('/admin/business-assistant') }}" class="kr-btn kr-btn-glass">
                            ✨ Business Assistant
                        </a>
                    @endif
                </div>
            </div>

            {{-- Kanan: Animasi 3 Peran Sedang Beroperasi --}}
            <div class="kr-roles-row">
                {{-- 1. KASIR --}}
                <div class="kr-role-card" style="animation: krFloatSlow 4.2s ease-in-out infinite;">
                    <div style="display:flex;justify-content:center;margin-bottom:8px;">
                        <svg width="84" height="72" viewBox="0 0 84 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Person Kasir -->
                            <circle cx="28" cy="20" r="10" fill="#FDE68A"/>
                            <path d="M18 18C18 12.5 22.5 9 28 9C33.5 9 38 12.5 38 18" stroke="#09352d" stroke-width="3" stroke-linecap="round"/>
                            <path d="M13 50C13 40.6 19.7 34 28 34C36.3 34 43 40.6 43 50V56H13V50Z" fill="#A3C639"/>
                            <!-- POS Screen -->
                            <rect x="44" y="26" width="28" height="22" rx="4" fill="#ffffff" stroke="#09352d" stroke-width="2"/>
                            <rect x="48" y="30" width="20" height="10" rx="2" fill="#0E4D42"/>
                            <rect x="54" y="48" width="8" height="8" fill="#D1FAE5"/>
                            <!-- Animated Receipt popping out -->
                            <g style="animation: krReceiptSlide 2.2s ease-in-out infinite;">
                                <rect x="52" y="13" width="12" height="14" rx="2" fill="#ffffff"/>
                                <line x1="55" y1="17" x2="61" y2="17" stroke="#0E4D42" stroke-width="1.5"/>
                                <line x1="55" y1="21" x2="61" y2="21" stroke="#A3C639" stroke-width="1.5"/>
                            </g>
                            <!-- Desk -->
                            <rect x="6" y="56" width="72" height="6" rx="3" fill="#ffffff" fill-opacity="0.3"/>
                        </svg>
                    </div>
                    <div style="font-size:12.5px;font-weight:800;color:#A3C639;">1. Kasir (POS)</div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.85);margin-top:3px;line-height:1.35;">
                        Melayani pesanan, hitung total &amp; cetak struk
                    </div>
                    <div style="position:absolute;right:-11px;top:46%;font-weight:900;color:#A3C639;font-size:14px;animation:krFlowArrow 1.8s infinite;">➔</div>
                </div>

                {{-- 2. ADMIN & DAPUR --}}
                <div class="kr-role-card" style="animation: krFloatSlow 4.2s ease-in-out infinite 0.7s;">
                    <div style="display:flex;justify-content:center;margin-bottom:8px;">
                        <svg width="84" height="72" viewBox="0 0 84 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Person Admin/Chef -->
                            <circle cx="30" cy="21" r="10" fill="#FDE68A"/>
                            <path d="M20 14C20 9.5 24.5 7 30 7C35.5 7 40 9.5 40 14H20Z" fill="#ffffff"/>
                            <path d="M15 50C15 40.6 21.7 34 30 34C38.3 34 45 40.6 45 50V56H15V50Z" fill="#34D399"/>
                            <!-- Inventory Box & Checklist -->
                            <rect x="48" y="30" width="24" height="24" rx="4" fill="#FEF3C7" stroke="#09352d" stroke-width="2"/>
                            <path d="M54 42L58 46L66 37" stroke="#0E4D42" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="67" cy="21" r="7" fill="#A3C639" style="animation: krPulseDot 2.2s infinite;"/>
                            <path d="M64 21H70M67 18V24" stroke="#09352d" stroke-width="2" stroke-linecap="round"/>
                            <rect x="6" y="56" width="72" height="6" rx="3" fill="#ffffff" fill-opacity="0.3"/>
                        </svg>
                    </div>
                    <div style="font-size:12.5px;font-weight:800;color:#A3C639;">2. Admin &amp; Stok</div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.85);margin-top:3px;line-height:1.35;">
                        Atur menu, resep HPP &amp; potong stok otomatis
                    </div>
                    <div style="position:absolute;right:-11px;top:46%;font-weight:900;color:#A3C639;font-size:14px;animation:krFlowArrow 1.8s infinite 0.4s;">➔</div>
                </div>

                {{-- 3. PEMILIK USAHA --}}
                <div class="kr-role-card" style="animation: krFloatSlow 4.2s ease-in-out infinite 1.4s;">
                    <div style="display:flex;justify-content:center;margin-bottom:8px;">
                        <svg width="84" height="72" viewBox="0 0 84 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Person Pemilik Usaha -->
                            <circle cx="28" cy="20" r="10" fill="#FDE68A"/>
                            <path d="M18 18C18 12.5 22.5 9 28 9C33.5 9 38 12.5 38 18" stroke="#1F2937" stroke-width="3" stroke-linecap="round"/>
                            <path d="M13 50C13 40.6 19.7 34 28 34C36.3 34 43 40.6 43 50V56H13V50Z" fill="#ffffff"/>
                            <!-- Laptop & Live Chart -->
                            <rect x="44" y="24" width="32" height="24" rx="4" fill="#09352d" stroke="#A3C639" stroke-width="2"/>
                            <rect x="49" y="36" width="5" height="9" rx="1" fill="#A3C639" style="transform-origin:bottom;animation:krBarGrow1 2s infinite;"/>
                            <rect x="57" y="31" width="5" height="14" rx="1" fill="#34D399" style="transform-origin:bottom;animation:krBarGrow2 2s infinite;"/>
                            <rect x="65" y="28" width="5" height="17" rx="1" fill="#A3C639" style="transform-origin:bottom;animation:krBarGrow1 2s infinite 0.5s;"/>
                            <rect x="40" y="48" width="38" height="4" rx="2" fill="#E5E7EB"/>
                            <rect x="6" y="56" width="72" height="6" rx="3" fill="#ffffff" fill-opacity="0.3"/>
                        </svg>
                    </div>
                    <div style="font-size:12.5px;font-weight:800;color:#A3C639;">3. Pemilik Usaha</div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.85);margin-top:3px;line-height:1.35;">
                        Pantau omzet, laba bersih &amp; insight bisnis
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
