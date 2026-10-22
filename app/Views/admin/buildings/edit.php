<div class="container-fluid px-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1 class="fw-bold m-0"><i class="fas fa-edit me-2 text-warning"></i> Tesis Düzenle: <?= htmlspecialchars($building['name']); ?></h1>
        <a href="<?= base_url('admin/buildingtype'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Listeye Dön
        </a>
    </div>

    <form action="<?= base_url('admin/buildingtype/update/' . $building['id']); ?>" method="POST" enctype="multipart/form-data">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light fw-bold"><i class="fas fa-info-circle me-1"></i> Temel Tesis Bilgileri</div>
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tesis Adı</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($building['name']); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="category" class="form-select" required>
                            <option value="factory" <?= $building['category'] === 'factory' ? 'selected' : ''; ?>>Fabrika (Factory)</option>
                            <option value="farm" <?= $building['category'] === 'farm' ? 'selected' : ''; ?>>Çiftlik (Farm)</option>
                            <option value="mine" <?= $building['category'] === 'mine' ? 'selected' : ''; ?>>Maden (Mine)</option>
                            <option value="utility" <?= $building['category'] === 'utility' ? 'selected' : ''; ?>>Altyapı (Utility)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Kompleks Türü</label>
                        <select name="complexity_type" class="form-select" required>
                            <option value="raw" <?= $building['complexity_type'] === 'raw' ? 'selected' : ''; ?>>Hammadde (Raw)</option>
                            <option value="semi" <?= $building['complexity_type'] === 'semi' ? 'selected' : ''; ?>>Yarı Mamül (Semi)</option>
                            <option value="finished" <?= $building['complexity_type'] === 'finished' ? 'selected' : ''; ?>>Mamül Ürün (Finished)</option>
                            <option value="utility" <?= $building['complexity_type'] === 'utility' ? 'selected' : ''; ?>>Altyapı / Kaynak (Utility)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Gerekli Skor</label>
                        <input type="number" name="required_score" class="form-control" value="<?= $building['required_score']; ?>" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ürettiği Ürün</label>
                        <select name="produces_product_id" class="form-select">
                            <option value="">-- Ürün Seçin --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id']; ?>" <?= $building['produces_product_id'] == $p['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($p['name']); ?> (<?= $p['type']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Temel Üretim Hızı</label>
                        <input type="number" name="production_rate" class="form-control" value="<?= $building['production_rate']; ?>" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Taban Maliyet (TL)</label>
                        <input type="number" step="0.01" name="base_cost" class="form-control" value="<?= $building['base_cost']; ?>" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-center gap-2">
                        <img src="<?= base_url('public/uploads/buildings/' . ($building['image'] ?: 'default.png')); ?>" alt="" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <label class="form-label fw-bold small">Yeni Görsel</label>
                            <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning bg-opacity-25 fw-bold text-dark"><i class="fas fa-bolt me-1"></i> Kaynak ve İhtiyaç Tüketimleri</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Enerji Tüketimi</label>
                        <input type="number" name="energy_consumption" class="form-control" value="<?= $building['energy_consumption']; ?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Su Tüketimi</label>
                        <input type="number" name="water_consumption" class="form-control" value="<?= $building['water_consumption']; ?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">İşgücü (Personel) İhtiyacı</label>
                        <input type="number" name="labor_required" class="form-control" value="<?= $building['labor_required']; ?>" min="0" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seviye Matrisleri Bilgi Paneli -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white fw-bold"><i class="fas fa-layer-group me-1"></i> Tanımlı Seviye Matrisleri</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Seviye</th>
                                <th>Üretim Hızı</th>
                                <th>Yükseltme Maliyeti</th>
                                <th>Enerji Çarpanı</th>
                                <th>İşgücü Çarpanı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($levels as $lvl): ?>
                                <tr>
                                    <td class="fw-bold">Seviye <?= $lvl['level']; ?></td>
                                    <td><?= $lvl['production_rate']; ?> Adet</td>
                                    <td><?= number_format($lvl['upgrade_cost'], 2); ?> TL</td>
                                    <td><?= $lvl['energy_multiplier']; ?>x</td>
                                    <td><?= $lvl['labor_multiplier']; ?>x</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-warning px-5 py-2 fw-bold fs-5 text-dark">
                <i class="fas fa-sync-alt me-2"></i> Tesis Şablonunu Güncelle
            </button>
        </div>
    </form>
</div>