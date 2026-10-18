<?php
namespace App\Controllers;

use System\Core\Controller;

class FarmsController extends Controller {

    public function __construct() {
        admin_check();
    }

    public function index() {
        $model = $this->model('Farms');
        $data = [
            'title' => 'Çiftlik Yönetimi Listesi',
            'items' => $model->getAll()
        ];
        $this->adminView('admin/farms/index', $data);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = $this->model('Farms');
            $model->create($_POST);
            redirect('admin/farms');
        }
        $data = ['title' => 'Yeni Çiftlik Yönetimi Ekle'];
        $this->adminView('admin/farms/create', $data);
    }

    public function edit($id) {
        $model = $this->model('Farms');
        $item = $model->find($id);
        if (!$item) { redirect('admin/farms'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model->update($id, $_POST);
            redirect('admin/farms');
        }
        $data = ['title' => 'Çiftlik Yönetimi Düzenle', 'item' => $item];
        $this->adminView('admin/farms/edit', $data);
    }

    public function delete($id) {
        $model = $this->model('Farms');
        $model->delete($id);
        redirect('admin/farms');
    }
}
