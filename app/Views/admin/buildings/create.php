<div class="container-fluid px-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1 class="fw-bold m-0"><i class="fas fa-plus-circle me-2 text-primary"></i> Yeni Tesis Şablonu Ekle</h1>
        <a href="<?= base_url('admin/buildingtype'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Listeye Dön
        </a>
    </div>

    <form action="<?= base_url('admin/buildingtype/store'); ?>" method="POST" enctype="multipart/form-data">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light fw-bold"><i class="fas fa-info-circle me-1"></i> Temel Tesis Bilgileri</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tesis Adı</label>
                        <input type="text" name="name" class="form-control" placeholder="Örn: Fırın, Değirmen, Su Kuyusu" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="category" class="form-select" required>
                            <option value="factory">Fabrika (Factory)</option>
                            <option value="farm">Çiftlik (Farm)</option>
                            <option value="mine">Maden (Mine)</option>
                            <option value="utility">Altyapı (Utility)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Kompleks Türü</label>
                        <select name="complexity_type" class="form-select" required>
                            <option value="raw">Hammadde (Raw)</option>
                            <option value="semi">Yarı Mamül (Semi)</option>
                            <option value="finished">Mamül Ürün (Finished)</option>
                            <option value="utility">Altyapı / Kaynak (Utility)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Gerekli Skor</label>
                        <input type="number" name="required_score" class="form-control" value="0" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ürettiği Ürün</label>
                        <select name="produces_product_id" class="form-select">
                            <option value="">-- Ürün Seçin --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['name']); ?> (<?= $p['type']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Temel Üretim Hızı (Adet)</label>
                        <input type="number" name="production_rate" class="form-control" value="10" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Taban Maliyet (TL)</label>
                        <input type="number" step="0.01" name="base_cost" class="form-control" value="50000.00" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tesis Görseli</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
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
                        <input type="number" name="energy_consumption" class="form-control" value="50" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Su Tüketimi</label>
                        <input type="number" name="water_consumption" class="form-control" value="10" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">İşgücü (Personel) İhtiyacı</label>
                        <input type="number" name="labor_required" class="form-control" value="10" min="0" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-success px-5 py-2 fw-bold fs-5">
                <i class="fas fa-save me-2"></i> Tesis Şablonunu Kaydet
            </button>
        </div>
    </form>
</div>