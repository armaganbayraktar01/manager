<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Stratejik Tesis ve Lojistik Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <!-- Üst Bilgi Barı -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded shadow-sm">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="fas fa-industry text-primary me-2"></i>Tesis & Lojistik Merkezi</h2>
                <p class="text-muted mb-0">Ana Depo: <span class="fw-bold text-dark"><i class="fas fa-warehouse text-danger me-1"></i>İzmir (Merkez)</span></p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fs-5 fw-bold text-success bg-light px-3 py-2 rounded border">
                    <i class="fas fa-wallet me-2"></i><?= number_format($player['money'] ?? 0, 2); ?> TL
                </span>
                <button class="btn btn-primary fw-bold px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#newBuildingModal">
                    <i class="fas fa-plus-circle me-2"></i>Yeni Tesis Kur
                </button>
            </div>
        </div>

        <!-- Mevcut Tesisler Listesi -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-bold text-dark">
                <i class="fas fa-list me-2 text-secondary"></i>Aktif Tesislerim (<?= count($my_buildings); ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Tesis Adı</th>
                                <th>Kategori</th>
                                <th>Kurulduğu İl</th>
                                <th>Seviye</th>
                                <th>Üretilen Ürün</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($my_buildings)): ?>
                                <?php foreach ($my_buildings as$ub): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= base_url('public/uploads/buildings/' . ($ub['building_image'] ?? 'factory-default.png')); ?>" width="35" height="35" class="rounded border me-2 object-fit-contain bg-white">
                                                <span><?= htmlspecialchars($ub['building_name']); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($ub['category']); ?></span></td>
                                        <td><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($ub['province_name'] ?? 'Bilinmiyor'); ?></td>
                                        <td><span class="badge bg-info text-dark">Seviye <?= $ub['level']; ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= base_url('public/uploads/products/' . ($ub['product_image'] ?? 'default.png')); ?>" width="30" height="30" class="rounded border me-2 object-fit-contain bg-white">
                                                <span><?= htmlspecialchars($ub['product_name'] ?? 'Genel'); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-success">Aktif</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Henüz kurulmuş bir tesisiniz bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Tesis Kurulum Modalı -->
    <div class="modal fade" id="newBuildingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="buildingForm" onsubmit="submitBuilding(event)">
                    <div class="modal-header bg-dark text-white px-4 py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-truck-loading text-warning me-2"></i>Stratejik Tesis Kurulum ve Fizibilite Merkezi
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body bg-light p-4">
                        
                        <!-- HATA GÖSTERİCİ ALAN -->
                        <div id="errorAlert" class="alert alert-danger d-none mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><span id="errorMessage">İşlem sırasında bir hata oluştu.</span>
                        </div>

                        <div class="row g-4">
                            <!-- SOL TARAF: TESİS SEÇİMİ -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-success text-white fw-semibold">
                                        <i class="fas fa-cubes me-2"></i>1. Tesis ve Üretim Planı
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-secondary">Tesis Şablonu</label>
                                            <select name="building_type_id" id="buildingSelect" class="form-select form-select-lg" required onchange="onBuildingChange(this)">
                                                <option value="" disabled selected>-- Tesis Türünü Seçiniz --</option>
                                                <?php foreach ($building_types as$bt): ?>
                                                    <?php $pCost =$bt['product_base_cost'] ?? 1000.00; ?>
                                                    <option value="<?= $bt['id']; ?>"
                                                        data-cost="<?= number_format($bt['base_cost'], 2, '.', ''); ?>"
                                                        data-productcost="<?= number_format($pCost, 2, '.', ''); ?>"
                                                        data-category="<?= htmlspecialchars($bt['category'] ?? 'Genel'); ?>"
                                                        data-rate="<?= (float)($bt['production_rate'] ?? 10); ?>"
                                                        data-laborreq="<?= (int)($bt['labor_required'] ?? 5); ?>"
                                                        data-prodname="<?= htmlspecialchars($bt['product_name'] ?? 'Ürün Yok'); ?>"
                                                        data-prodimg="<?= htmlspecialchars($bt['product_image'] ?? 'default.png'); ?>"
                                                        data-img="<?= htmlspecialchars($bt['image'] ?? 'factory-default.png'); ?>">
                                                        <?= htmlspecialchars($bt['name']); ?> (<?= htmlspecialchars($bt['product_name'] ?? 'Genel'); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="p-3 bg-white border rounded shadow-sm text-center mt-3">
                                            <div class="row align-items-center">
                                                <div class="col-6 border-end">
                                                    <small class="text-muted d-block mb-1">Tesis Görseli</small>
                                                    <img id="buildingImage" src="<?= base_url('public/uploads/buildings/factory-default.png'); ?>" class="img-fluid rounded border p-1" style="max-height: 80px; object-fit: contain;">
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Çıktı Ürün</small>
                                                    <img id="productImage" src="<?= base_url('public/uploads/products/default.png'); ?>" class="rounded border mb-1" style="width: 35px; height: 35px; object-fit: contain;">
                                                    <div id="productNameLabel" class="fw-bold small text-dark">-</div>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-2 border-top text-start">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Taban Üretim Hızı:</span>
                                                    <span id="lblBaseRate" class="fw-bold text-success">0 / döngü</span>
                                                </div>
                                                <div class="d-flex justify-content-between small">
                                                    <span class="text-muted">Gerekli İşçi:</span>
                                                    <span id="lblLaborReq" class="fw-bold text-primary">0 İşçi</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SAĞ TARAF: BÖLGE VE FİZİBİLİTE -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-primary text-white fw-semibold">
                                        <i class="fas fa-chart-line me-2"></i>2. Lokasyon, Kurulum ve Ürün Fizibilitesi
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-secondary">Kurulacak İl (Bölge)</label>
                                            <select name="province_id" id="provinceSelect" class="form-select form-select-lg" required disabled onchange="calculateFeasibility()">
                                                <option value="" disabled selected>-- Önce Tesis Seçmelisiniz --</option>
                                                <?php foreach ($provinces as$prov): ?>
                                                    <option value="<?= $prov['id']; ?>" 
                                                        data-lat="<?= (float)($prov['latitude'] ?? 39.0); ?>"
                                                        data-lon="<?= (float)($prov['longitude'] ?? 35.0); ?>"
                                                        data-pop="<?= (float)($prov['population'] ?? 1000000); ?>"
                                                        data-agri="<?= (float)($prov['agri_multiplier'] ?? 1.0); ?>"
                                                        data-ind="<?= (float)($prov['ind_multiplier'] ?? 1.0); ?>"
                                                        data-mine="<?= (float)($prov['mine_multiplier'] ?? 1.0); ?>"
                                                        data-labor="<?= (float)($prov['labor_cost_multiplier'] ?? 1.0); ?>">
                                                        <?= htmlspecialchars($prov['name']); ?> (Nüfus: <?= number_format($prov['population']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small id="provinceHelpText" class="text-muted">Sektörel verimliliğe göre iller renklendirilmiştir.</small>
                                        </div>

                                        <!-- KUTU 1: TESİS KURULUM ANALİZİ -->
                                        <div class="p-3 bg-white border rounded shadow-sm mb-3">
                                            <h6 class="text-dark fw-bold border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-building text-primary me-1"></i>Tesis Kurulum Maliyeti</span>
                                                <span id="lblTotalCost" class="text-danger fw-bold fs-5">0.00 TL</span>
                                            </h6>
                                            
                                            <div class="row g-1 small text-muted mb-2">
                                                <div class="col-6">İzmir Mesafe: <span id="lblDistance" class="fw-bold text-dark">0 km</span></div>
                                                <div class="col-6">Sektör Çarpanı: <span id="lblRegionalMult" class="fw-bold text-dark">1.00x</span></div>
                                                <div class="col-6">İşçi Katsayısı: <span id="lblLaborMult" class="fw-bold text-dark">1.00x</span></div>
                                                <div class="col-6">Nüfus Etkisi: <span id="lblPopEffect" class="fw-bold text-dark">1.00x</span></div>
                                            </div>

                                            <div class="bg-light p-2 rounded small text-muted" id="formulaLogText" style="font-size: 0.75rem;">
                                                Ayrıntılı kurulum maliyeti dökümü bekleniyor...
                                            </div>
                                        </div>

                                        <!-- KUTU 2: ÜRÜN BİRİM MALİYET VE FİYAT PERFORMANS ANALİZİ -->
                                        <div class="p-3 bg-white border rounded shadow-sm">
                                            <h6 class="text-dark fw-bold border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-boxes text-success me-1"></i>Ürün Birim Maliyet & Satış</span>
                                                <span id="lblUnitCost" class="text-success fw-bold">0.00 TL / birim</span>
                                            </h6>

                                            <div class="row g-2 small mb-2">
                                                <div class="col-6">
                                                    <span class="text-muted d-block">Öngörülen Satış Fiyatı:</span>
                                                    <span id="lblSellingPrice" class="fw-bold text-primary fs-6">0.00 TL</span>
                                                </div>
                                                <div class="col-6">
                                                    <span class="text-muted d-block">Birim Başına Net Kâr:</span>
                                                    <span id="lblNetProfit" class="fw-bold text-success fs-6">0.00 TL</span>
                                                </div>
                                            </div>

                                            <div class="bg-light p-2 rounded small text-muted" id="productFormulaLog" style="font-size: 0.75rem;">
                                                Maliyet analizi bekleniyor...
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-white px-4 py-3">
                        <div class="me-auto">
                            <span class="text-muted">Yatırım Tutarı: </span>
                            <span id="lblFooterCost" class="fs-5 fw-bold text-danger">0.00 TL</span>
                        </div>
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-success fw-bold px-5 shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Yatırımı Onayla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const warehouseIzmir = {
        lat: <?= $warehouse_izmir['lat']; ?>,
        lon: <?= $warehouse_izmir['lon']; ?>
    };

    const profitMargin = <?= (float)($game_settings['profit_margin'] ?? 0.35); ?>;
    const transportUnitPrice = <?= (float)($game_settings['transport_unit_price'] ?? 25.00); ?>;

    let currentCategory = '';
    let baseBuildingCost = 0;
    let baseProductCost = 1000.00;

    function onBuildingChange(select) {
        const option = select.options[select.selectedIndex];
        
        baseBuildingCost = parseFloat(option.getAttribute('data-cost') || 0);
        baseProductCost = parseFloat(option.getAttribute('data-productcost') || 1000.00);
        currentCategory = (option.getAttribute('data-category') || '').toLowerCase();
        
        document.getElementById('buildingImage').src = '<?= base_url("public/uploads/buildings/"); ?>' + (option.getAttribute('data-img') || 'factory-default.png');
        document.getElementById('productImage').src = '<?= base_url("public/uploads/products/"); ?>' + (option.getAttribute('data-prodimg') || 'default.png');
        document.getElementById('productNameLabel').innerText = option.getAttribute('data-prodname') || 'Bilinmiyor';
        document.getElementById('lblBaseRate').innerText = (option.getAttribute('data-rate') || 10) + ' / döngü';
        document.getElementById('lblLaborReq').innerText = (option.getAttribute('data-laborreq') || 5) + ' İşçi';

        const provSelect = document.getElementById('provinceSelect');
        provSelect.removeAttribute('disabled');

        colorProvincesByCategory(currentCategory);
        calculateFeasibility();
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; 
        const dLat = deg2rad(lat2 - lat1);
        const dLon = deg2rad(lon2 - lon1);
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function deg2rad(deg) {
        return deg * (Math.PI / 180);
    }

    function colorProvincesByCategory(category) {
        const provSelect = document.getElementById('provinceSelect');
        for (let i = 1; i < provSelect.options.length; i++) {
            const opt = provSelect.options[i];
            let multiplier = 1.0;

            if (category.includes('agri') || category.includes('tarım') || category.includes('tarim')) {
                multiplier = parseFloat(opt.getAttribute('data-agri') || 1.0);
            } else if (category.includes('ind') || category.includes('sanayi') || category.includes('fabrika')) {
                multiplier = parseFloat(opt.getAttribute('data-ind') || 1.0);
            } else if (category.includes('mine') || category.includes('maden')) {
                multiplier = parseFloat(opt.getAttribute('data-mine') || 1.0);
            }

            if (multiplier >= 1.6) {
                opt.style.backgroundColor = '#d1e7dd';
                opt.style.color = '#0f5132';
            } else if (multiplier >= 1.2) {
                opt.style.backgroundColor = '#fff3cd';
                opt.style.color = '#664d03';
            } else {
                opt.style.backgroundColor = '#f8d7da';
                opt.style.color = '#842029';
            }
        }
    }

    function calculateFeasibility() {
        const provSelect = document.getElementById('provinceSelect');
        if (provSelect.selectedIndex <= 0) return;

        const opt = provSelect.options[provSelect.selectedIndex];
        
        let sectorMultiplier = 1.0;
        if (currentCategory.includes('agri') || currentCategory.includes('tarım') || currentCategory.includes('tarim')) {
            sectorMultiplier = parseFloat(opt.getAttribute('data-agri') || 1.0);
        } else if (currentCategory.includes('ind') || currentCategory.includes('sanayi') || currentCategory.includes('fabrika')) {
            sectorMultiplier = parseFloat(opt.getAttribute('data-ind') || 1.0);
        } else if (currentCategory.includes('mine') || currentCategory.includes('maden')) {
            sectorMultiplier = parseFloat(opt.getAttribute('data-mine') || 1.0);
        }
        if (sectorMultiplier <= 0) sectorMultiplier = 1.0;

        const laborMultiplier = parseFloat(opt.getAttribute('data-labor') || 1.0);
        const population = parseFloat(opt.getAttribute('data-pop') || 1000000);
        const lat = parseFloat(opt.getAttribute('data-lat') || 39.0);
        const lon = parseFloat(opt.getAttribute('data-lon') || 35.0);

        const distanceKm = calculateDistance(warehouseIzmir.lat, warehouseIzmir.lon, lat, lon);

        const popPower = Math.pow(population / 1000000.0, 0.3);
        const laborSectorRatio = laborMultiplier / sectorMultiplier;
        const distanceFactor = 1.0 + (distanceKm / 1000.0);
        const kurulumMaliyeti = baseBuildingCost * popPower * laborSectorRatio * distanceFactor;

        const popPowerUnit = Math.pow(population / 1000000.0, 0.2);
        const uretimMaliyetiBirim = baseProductCost * popPowerUnit * laborSectorRatio;
        const lojistikBirimYuku = (distanceKm * transportUnitPrice) / 100; 
        const toplamBirimMaliyet = uretimMaliyetiBirim + lojistikBirimYuku;

        const satisFiyati = toplamBirimMaliyet * (1 + profitMargin);
        const birimKar = satisFiyati - toplamBirimMaliyet;

        document.getElementById('lblDistance').innerText = distanceKm.toFixed(0) + ' km';
        document.getElementById('lblRegionalMult').innerText = sectorMultiplier.toFixed(2) + 'x';
        document.getElementById('lblLaborMult').innerText = laborMultiplier.toFixed(2) + 'x';
        document.getElementById('lblPopEffect').innerText = popPower.toFixed(3) + 'x';
        document.getElementById('lblTotalCost').innerText = kurulumMaliyeti.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
        document.getElementById('lblFooterCost').innerText = kurulumMaliyeti.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';

        document.getElementById('formulaLogText').innerHTML = 
            `<div class="d-flex justify-content-between border-bottom pb-1 mb-1"><span>Taban Tesis Maliyeti:</span><b>${baseBuildingCost.toLocaleString('tr-TR', {minimumFractionDigits:2})} TL</b></div>` +
            `<div class="d-flex justify-content-between border-bottom pb-1 mb-1"><span>Nüfus Katsayısı:</span><b>${popPower.toFixed(3)}</b></div>` +
            `<div class="d-flex justify-content-between border-bottom pb-1 mb-1"><span>İşçi / Sektör Oranı:</span><b>${laborSectorRatio.toFixed(3)}</b></div>` +
            `<div class="d-flex justify-content-between"><span>Lojistik Mesafe Çarpanı:</span><b>${distanceFactor.toFixed(3)}</b></div>`;

        document.getElementById('lblUnitCost').innerText = toplamBirimMaliyet.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL / birim';
        document.getElementById('lblSellingPrice').innerText = satisFiyati.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
        document.getElementById('lblNetProfit').innerText = birimKar.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';

        document.getElementById('productFormulaLog').innerHTML = 
            `<div class="d-flex justify-content-between border-bottom pb-1 mb-1"><span>Tarla/Tesis Üretim Maliyeti:</span><b>${uretimMaliyetiBirim.toLocaleString('tr-TR', {minimumFractionDigits:2})} TL</b></div>` +
            `<div class="d-flex justify-content-between border-bottom pb-1 mb-1"><span>İzmir Depoya Sevkiyat Lojistiği:</span><b>${lojistikBirimYuku.toLocaleString('tr-TR', {minimumFractionDigits:2})} TL</b></div>` +
            `<div class="d-flex justify-content-between"><span>Hedef Kâr Oranı:</span><b>%${(profitMargin * 100).toFixed(0)}</b></div>`;
    }

    // AJAX Form Gönderimi (URLSearchParams & credentials: include ile kararlı yapı)
    function submitBuilding(event) {
        event.preventDefault();
        const form = document.getElementById('buildingForm');
        const alertBox = document.getElementById('errorAlert');
        const errorMsg = document.getElementById('errorMessage');

        alertBox.classList.add('d-none');

        const formData = new URLSearchParams(new FormData(form));

        fetch("<?= base_url('app/playerbuilding/store'); ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: formData,
            credentials: "include"
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                return response.text().then(text => { throw new Error(text || "İşlem gerçekleştirilemedi."); });
            }
        })
        .catch(error => {
            errorMsg.innerText = error.message || "Bütçe yetersiz veya veritabanı hatası!";
            alertBox.classList.remove('d-none');
        });
    }
    </script>
</body>
</html>