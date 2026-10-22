<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Stratejik Tesis ve Lojistik Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .square-img-card { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; }
        .tycoon-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .tycoon-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid px-4 py-4 mb-5">
    
    <!-- Üst Bilgi ve Cüzdan Barı -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded shadow-sm">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="fas fa-industry text-primary me-2"></i>Tesis & Lojistik Merkezi</h2>
            <p class="text-muted mb-0">Ana Depo: <span class="fw-bold text-dark"><i class="fas fa-warehouse text-danger me-1"></i>İzmir (Merkez)</span> | Şirket: <span class="text-warning fw-bold"><?= htmlspecialchars($player['company_name'] ?? 'Holding'); ?></span></p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="fs-5 fw-bold text-success bg-light px-3 py-2 rounded border">
                <i class="fas fa-wallet me-2"></i>₺<?= number_format($player['money'] ?? 0, 2, ',', '.'); ?>
            </span>
            <button class="btn btn-primary fw-bold px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#newBuildingModal">
                <i class="fas fa-plus-circle me-2"></i>Yeni Tesis Kur
            </button>
        </div>
    </div>

    <!-- MERKEZİ DEPO / ENVANTER (user_inventories) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-warehouse text-warning me-2"></i>Merkezi Depo Envanteri (Tüm Ürünler)</span>
            <span class="badge bg-warning text-dark"><?= count($inventory ?? []); ?> Çeşit Ürün</span>
        </div>
        <div class="card-body bg-white">
            <?php if (!empty($inventory)): ?>
                <div class="row g-3">
                    <?php foreach ($inventory as $inv): ?>
                        <div class="col-xl-3 col-md-4">
                            <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($inv['product_name'] ?? 'Ürün'); ?></h6>
                                    <span class="small text-muted">Ürün ID: #<?= $inv['product_id']; ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="fs-5 fw-bold text-success"><?= number_format($inv['quantity'] ?? 0); ?></span>
                                    <small class="text-muted d-block" style="font-size: 11px;">Birim</small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-2 text-secondary"></i>
                    <p class="small mb-0">Deponuzda şu an kayıtlı ürün bulunmuyor. Üretimler tamamlandıkça buraya yansıyacaktır.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AKTİF TESİSLER KARTLARI -->
    <h4 class="fw-bold mb-3"><i class="fas fa-cubes me-2 text-success"></i>Aktif Tesisleriniz (<?= count($my_buildings ?? []); ?>)</h4>
    <div class="row g-4">
        <?php if (!empty($my_buildings)): ?>
            <?php foreach ($my_buildings as $mb): 
                $progress = $mb['production_progress'] ?? 0;
                $remaining = $mb['remaining_seconds'] ?? 600;
                $hasInput = $mb['formula_details']['has_enough_input'];
                $unitLabel = $mb['formula_details']['unit_label'];
                $warningMsg = $mb['formula_details']['warning_message'] ?? '';
                $buildingJson = htmlspecialchars(json_encode($mb, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
            ?>
                <div class="col-xl-3 col-md-4 building-item" data-remaining="<?= $remaining; ?>" data-building-id="<?= $mb['id']; ?>">
                    <div class="card h-100 tycoon-card shadow-sm border-0">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="<?= base_url('public/uploads/buildings/' . ($mb['image'] ?? 'factory-default.png')); ?>" class="square-img-card border shadow-sm bg-white object-fit-contain p-1" alt="...">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="badge bg-info text-dark mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($mb['province_name']); ?></span>
                                            <span class="badge bg-secondary">Sev. <?= $mb['level'] ?? 1; ?></span>
                                        </div>
                                        <h6 class="fw-bold mb-1">
                                            <a href="javascript:void(0);" class="text-dark text-decoration-none open-modal-btn" data-building='<?= $buildingJson; ?>'>
                                                <?= htmlspecialchars($mb['building_name']); ?> <i class="fas fa-info-circle small text-primary"></i>
                                            </a>
                                        </h6>
                                    </div>
                                </div>

                                <?php if (!$hasInput && !empty($warningMsg)): ?>
                                    <div class="alert alert-danger py-1 px-2 small mb-2 fw-bold" style="font-size: 11px;">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($warningMsg); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="bg-light p-2 rounded small mb-3 border">
                                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Üretim Miktarı:</span><strong class="text-success">+<?= $mb['production_amount']; ?> <?= $unitLabel; ?></strong></div>
                                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Hammadde Durumu:</span><strong class="<?= $hasInput ? 'text-success' : 'text-danger'; ?>"><?= $hasInput ? 'Yeterli' : '⚠️ Eksik'; ?></strong></div>
                                    <div class="d-flex justify-content-between"><span class="text-muted">Sağlık:</span><strong class="text-danger">%<?= number_format($mb['condition_health'], 0); ?></strong></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-3 building-progress-container" data-total="<?= $cycle_seconds ?? 600; ?>">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Üretim Durumu</span>
                                        <span class="fw-bold <?= $hasInput ? 'text-success' : 'text-danger'; ?>">
                                            <?= $hasInput ? '<span class="progress-text">' . round($progress, 1) . '</span>% (<span class="timer-display countdown-timer" data-seconds="' . $remaining . '">' . sprintf('%02d:%02d', floor($remaining / 60), $remaining % 60) . '</span>)' : 'BEKLİYOR'; ?>
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 18px;"><div class="progress-bar progress-bar-striped <?= $hasInput ? 'progress-bar-animated bg-success' : 'bg-secondary'; ?>" style="width: <?= $hasInput ? $progress : 0; ?>%;"></div></div>
                                </div>
                                <button class="btn btn-outline-primary btn-sm w-100 open-modal-btn mb-2" data-building='<?= $buildingJson; ?>'><i class="fas fa-history me-1"></i> Detay & Çarpanları Gör</button>
                                
                                <div class="pt-2 border-top d-flex gap-1">
                                    <form action="<?= base_url('app/playerbuilding/adminAction'); ?>" method="POST" class="flex-grow-1">
                                        <input type="hidden" name="building_id" value="<?= $mb['id']; ?>">
                                        <input type="hidden" name="action" value="<?= ($mb['status'] ?? 'active') === 'paused' ? 'resume' : 'pause'; ?>">
                                        <button type="submit" class="btn btn-sm btn-dark w-100 py-1" style="font-size: 11px;">
                                            <i class="fas fa-<?= ($mb['status'] ?? 'active') === 'paused' ? 'play' : 'pause'; ?> me-1"></i> 
                                            <?= ($mb['status'] ?? 'active') === 'paused' ? 'Devam' : 'Durdur'; ?>
                                        </button>
                                    </form>
                                    <form action="<?= base_url('app/playerbuilding/adminAction'); ?>" method="POST" class="flex-grow-1">
                                        <input type="hidden" name="building_id" value="<?= $mb['id']; ?>">
                                        <input type="hidden" name="action" value="instant_complete">
                                        <button type="submit" class="btn btn-sm btn-warning w-100 py-1 text-dark fw-bold" style="font-size: 11px;" title="God Mod: Anında Bitir">
                                            <i class="fas fa-bolt me-1"></i> Bitir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted bg-white rounded shadow-sm"><h5>Henüz kurulmuş bir tesisiniz bulunmuyor.</h5></div>
        <?php endif; ?>
    </div>
</div>

<!-- YENİ TESİS KURULUM MODALI -->
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
                    <div id="errorAlert" class="alert alert-danger d-none mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><span id="errorMessage">İşlem sırasında bir hata oluştu.</span>
                    </div>

                    <div class="row g-4">
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
                                            <?php foreach ($building_types as $bt): ?>
                                                <?php $pCost = $bt['product_base_cost'] ?? 1000.00; ?>
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
                                            <?php foreach ($provinces as $prov): ?>
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
                                    </div>

                                    <div class="p-3 bg-white border rounded shadow-sm mb-3">
                                        <h6 class="text-dark fw-bold border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-building text-primary me-1"></i>Tesis Kurulum Maliyeti</span>
                                            <span id="lblTotalCost" class="text-danger fw-bold fs-5">0.00 TL</span>
                                        </h6>
                                        <div class="row g-1 small text-muted mb-2">
                                            <div class="col-6">İzmir Mesafe: <span id="lblDistance" class="fw-bold text-dark">0 km</span></div>
                                            <div class="col-6">Sektör Çarpanı: <span id="lblRegionalMult" class="fw-bold text-dark">1.00x</span></div>
                                        </div>
                                        <div class="bg-light p-2 rounded small text-muted" id="formulaLogText" style="font-size: 0.75rem;">
                                            Ayrıntılı kurulum maliyeti dökümü bekleniyor...
                                        </div>
                                    </div>

                                    <div class="p-3 bg-white border rounded shadow-sm">
                                        <h6 class="text-dark fw-bold border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-boxes text-success me-1"></i>Ürün Birim Maliyet & Satış</span>
                                            <span id="lblUnitCost" class="text-success fw-bold">0.00 TL / birim</span>
                                        </h6>
                                        <div class="row g-2 small mb-2">
                                            <div class="col-6"><span class="text-muted d-block">Öngörülen Satış Fiyatı:</span><span id="lblSellingPrice" class="fw-bold text-primary fs-6">0.00 TL</span></div>
                                            <div class="col-6"><span class="text-muted d-block">Birim Başına Net Kâr:</span><span id="lblNetProfit" class="fw-bold text-success fs-6">0.00 TL</span></div>
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

<!-- MODAL: TESİS DETAY VE ÇARPAN DÖKÜMÜ -->
<div class="modal fade" id="buildingDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white"><h5 class="modal-title" id="modalBuildingName">Tesis Yönetimi</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Kurulum Tarihi:</span><strong id="modalPurchasedAt">-</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Gereken Hammadde:</span><strong id="modalInputProduct" class="text-warning">-</strong></li>
                </ul>

                <div class="card bg-light border-0 p-3 mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-calculator me-1 text-primary"></i> Üretim Çarpanları & Hesaplama Etkisi</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white small mb-0">
                            <thead class="table-dark"><tr><th>Parametre</th><th>Tür / Açıklama</th><th class="text-end">Değer / Katkı</th></tr></thead>
                            <tbody>
                                <tr><td><strong>Taban Üretim</strong></td><td>Şablon & Seviye Matrisi</td><td class="text-end fw-bold" id="paramBaseProd">-</td></tr>
                                <tr><td><strong id="paramRegionLabel">Bölge Verimliliği</strong></td><td>İl Sektör Çarpanı</td><td class="text-end fw-bold text-info" id="paramRegionMult">-</td></tr>
                                <tr><td><strong>Sağlık Faktörü</strong></td><td>Tesis Kondisyonu (% Sağlık)</td><td class="text-end fw-bold text-danger" id="paramHealthFactor">-</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h6 class="fw-bold mb-2"><i class="fas fa-history me-1"></i> Son Üretim ve İşlem Geçmişi (building_logs)</h6>
                <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                    <table class="table table-sm table-striped small mb-3">
                        <thead class="table-light"><tr><th>İşlem</th><th>Ürün</th><th>Miktar</th><th>Zaman</th></tr></thead>
                        <tbody id="modalLogTableBody"><tr><td colspan="4" class="text-center text-muted">Yükleniyor...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const warehouseIzmir = {
        lat: <?= $warehouse_izmir['lat'] ?? 38.4192; ?>,
        lon: <?= $warehouse_izmir['lon'] ?? 27.1287; ?>
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
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function deg2rad(deg) { return deg * (Math.PI / 180); }

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
            `<div class="d-flex justify-content-between border-bottom pb-1 mb-1"><span>Üretim Maliyeti:</span><b>${uretimMaliyetiBirim.toLocaleString('tr-TR', {minimumFractionDigits:2})} TL</b></div>` +
            `<div class="d-flex justify-content-between border-bottom pb-1 mb-1"><span>Lojistik Sevkiyat Yükü:</span><b>${lojistikBirimYuku.toLocaleString('tr-TR', {minimumFractionDigits:2})} TL</b></div>` +
            `<div class="d-flex justify-content-between"><span>Hedef Kâr Oranı:</span><b>%${(profitMargin * 100).toFixed(0)}</b></div>`;
    }

    // Kararlı Sayaç Döngüsü ve Süre Bitince Otomatik Sayfa Yenileme
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.open-modal-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                try {
                    openBuildingDetail(JSON.parse(this.getAttribute('data-building')));
                } catch (err) { console.error("Modal veri hatası:", err); }
            });
        });

        const cycleSeconds = <?= $cycle_seconds ?? 600; ?>;
        document.querySelectorAll(".building-item").forEach(item => {
            let remainingSecs = parseInt(item.dataset.remaining || 0);
            const timerElement = item.querySelector(".timer-display");
            const progressBar = item.querySelector(".progress-bar");
            const progressText = item.querySelector(".progress-text");

            if (!timerElement) return;

            const targetTime = Date.now() + (remainingSecs * 1000);

            const updateTimer = () => {
                const distance = targetTime - Date.now();
                let currentRemaining = Math.max(0, Math.floor(distance / 1000));

                if (currentRemaining > 0) {
                    let mins = Math.floor(currentRemaining / 60);
                    let secs = currentRemaining % 60;
                    timerElement.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

                    if (progressBar) {
                        let elapsedInCycle = cycleSeconds - currentRemaining;
                        let progress = Math.min(100, Math.max(0, (elapsedInCycle / cycleSeconds) * 100));
                        progressBar.style.width = progress + "%";
                        if (progressText) progressText.textContent = progress.toFixed(1);
                    }
                } else {
                    timerElement.textContent = "00:00";
                    if (progressBar) progressBar.style.width = "100%";
                    if (progressText) progressText.textContent = "100";

                    // Süre dolduğunda sayfayı yenile (PHP otomatik olarak üretimi işleyip depoya ekleyecektir)
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            };

            updateTimer();
            setInterval(updateTimer, 1000);
        });
    });

    function openBuildingDetail(building) {
        const safeSetText = (id, text) => {
            let el = document.getElementById(id);
            if (el) el.textContent = text;
        };

        safeSetText('modalBuildingName', (building.building_name || 'Tesis') + " (" + (building.province_name || '') + ")");
        safeSetText('modalPurchasedAt', building.created_at || 'Bilinmiyor');

        if (building.formula_details) {
            safeSetText('paramBaseProd', building.formula_details.base_production + " " + building.formula_details.unit_label);
            safeSetText('paramRegionLabel', building.formula_details.region_label);
            safeSetText('paramRegionMult', "x" + parseFloat(building.formula_details.region_multiplier).toFixed(2));
            safeSetText('paramHealthFactor', "%" + (building.formula_details.health_factor * 100).toFixed(0));
            safeSetText('modalInputProduct', building.formula_details.input_product_name + (building.formula_details.consumption_amount > 0 ? " (" + building.formula_details.consumption_amount + " " + building.formula_details.unit_label + ")" : ""));
        }

        let logTableBody = document.getElementById('modalLogTableBody');
        if (logTableBody) {
            logTableBody.innerHTML = "";
            if (building.logs && building.logs.length > 0) {
                building.logs.forEach(log => {
                    let badgeClass = log.action_type === 'production' ? 'text-success' : (log.action_type === 'consumption' ? 'text-danger' : 'text-primary');
                    let sign = log.action_type === 'production' ? '+' : (log.action_type === 'consumption' ? '-' : '');
                    logTableBody.innerHTML += `<tr><td><span class="${badgeClass} fw-bold">${log.description || log.action_type}</span></td><td>${log.product_name || 'Genel'}</td><td><strong class="${badgeClass}">${sign}${log.amount}</strong></td><td class="text-muted small">${log.created_at}</td></tr>`;
                });
            } else {
                logTableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">Bu tesise ait henüz işlem geçmişi bulunmuyor.</td></tr>`;
            }
        }
        
        bootstrap.Modal.getOrCreateInstance(document.getElementById('buildingDetailModal')).show();
    }

    function submitBuilding(event) {
        event.preventDefault();
        const form = document.getElementById('buildingForm');
        const alertBox = document.getElementById('errorAlert');
        const errorMsg = document.getElementById('errorMessage');

        alertBox.classList.add('d-none');
        const formData = new URLSearchParams(new FormData(form));

        fetch("<?= base_url('app/playerbuilding/store'); ?>", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
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