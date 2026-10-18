<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= $title ?></h2>
    <a href="<?= base_url('admin/farms/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Yeni Ekle</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#ID</th>
                    <th>Başlık</th>
                    <th>Açıklama</th>
                    <th class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($item['title'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['description'] ?? '') ?></td>
                    <td class="text-end">
                        <a href="<?= base_url('admin/farms/edit/' . $item['id']) ?>" class="btn btn-sm btn-warning text-white"><i class="fas fa-edit"></i></a>
                        <a href="<?= base_url('admin/farms/delete/' . $item['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="10" class="text-center text-muted py-4">Henüz kayıt bulunmuyor.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
