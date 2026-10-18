<div class="card shadow-sm border-0 col-md-8 mx-auto">
    <div class="card-header bg-white py-3">
        <h4 class="m-0 fw-bold">Yeni Çiftlik Yönetimi Ekle</h4>
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Başlık</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Açıklama</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <a href="<?= base_url('admin/farms') ?>" class="btn btn-secondary">Geri Dön</a>
                <button type="submit" class="btn btn-success">Kaydet</button>
            </div>
        </form>
    </div>
</div>
