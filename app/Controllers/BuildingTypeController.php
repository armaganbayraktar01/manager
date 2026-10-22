<?php
namespace App\Controllers;

use System\Core\Controller;
use System\Core\Database;

class BuildingTypeController extends Controller {

    private string $uploadDir = __DIR__ . '/../../public/uploads/buildings/';

    public function index() {
        $db = Database::getInstance();
        
        // Ürün tablosundaki görseli de (p.image as product_image) sorguya ekleyelim
        $buildings = $db->query("SELECT bt.*, p.name as product_name, p.type as product_type, p.image as product_image 
                                 FROM building_types bt 
                                 LEFT JOIN products p ON bt.produces_product_id = p.id 
                                 ORDER BY bt.id DESC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->adminView('admin/buildings/index', ['buildings' => $buildings]);
    }

    public function create() {
        $db = Database::getInstance();
        $products = $db->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->adminView('admin/buildings/create', ['products' => $products]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance();

            $name               = trim($_POST['name'] ?? '');
            $category           = trim($_POST['category'] ?? 'factory');
            $complexityType     = trim($_POST['complexity_type'] ?? 'raw');
            $producesProductId  = !empty($_POST['produces_product_id']) ? (int)$_POST['produces_product_id'] : null;
            $productionRate     = (int)($_POST['production_rate'] ?? 10);
            $baseCost           = (float)($_POST['base_cost'] ?? 50000.00);
            $requiredScore      = (int)($_POST['required_score'] ?? 0);
            $storageType        = trim($_POST['storage_type'] ?? 'raw');
            $energyConsumption  = (int)($_POST['energy_consumption'] ?? 50);
            $waterConsumption   = (int)($_POST['water_consumption'] ?? 10);
            $laborRequired      = (int)($_POST['labor_required'] ?? 10);

            $imageName = 'default.png';

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir($this->uploadDir)) {
                    mkdir($this->uploadDir, 0755, true);
                }

                $fileTmpPath   = $_FILES['image']['tmp_name'];
                $fileName      = $_FILES['image']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = uniqid('bld_') . '.' . $fileExtension;
                    if (move_uploaded_file($fileTmpPath, $this->uploadDir . $newFileName)) {
                        $imageName = $newFileName;
                    }
                }
            }

            try {
                $db->beginTransaction();

                $stmt = $db->prepare("INSERT INTO building_types (name, category, complexity_type, produces_product_id, production_rate, base_cost, required_score, storage_type, energy_consumption, water_consumption, labor_required, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $name, $category, $complexityType, $producesProductId, $productionRate, 
                    $baseCost, $requiredScore, $storageType, $energyConsumption, $waterConsumption, 
                    $laborRequired, $imageName
                ]);
                
                $buildingTypeId = $db->lastInsertId();

                $matrixStmt = $db->prepare("INSERT INTO building_level_matrix (building_type_id, level, production_rate, upgrade_cost, energy_multiplier, labor_multiplier) VALUES (?, 1, ?, ?, 1.00, 1.00)");
                $matrixStmt->execute([$buildingTypeId, $productionRate, $baseCost]);

                $db->commit();
            } catch (\Exception $e) {
                $db->rollBack();
            }

            redirect('admin/buildingtype');
        }
    }

    public function edit($id) {
        $db = Database::getInstance();
        $buildingId = (int)$id;

        $stmt = $db->prepare("SELECT * FROM building_types WHERE id = ?");
        $stmt->execute([$buildingId]);
        $building = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$building) {
            redirect('admin/buildingtype');
        }

        $matrixStmt = $db->prepare("SELECT * FROM building_level_matrix WHERE building_type_id = ? ORDER BY level ASC");
        $matrixStmt->execute([$buildingId]);
        $levels = $matrixStmt->fetchAll(\PDO::FETCH_ASSOC);

        $products = $db->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->adminView('admin/buildings/edit', [
            'building' => $building,
            'levels' => $levels,
            'products' => $products
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance();
            $buildingId = (int)$id;

            $name               = trim($_POST['name'] ?? '');
            $category           = trim($_POST['category'] ?? 'factory');
            $complexityType     = trim($_POST['complexity_type'] ?? 'raw');
            $producesProductId  = !empty($_POST['produces_product_id']) ? (int)$_POST['produces_product_id'] : null;
            $productionRate     = (int)($_POST['production_rate'] ?? 10);
            $baseCost           = (float)($_POST['base_cost'] ?? 50000.00);
            $requiredScore      = (int)($_POST['required_score'] ?? 0);
            $storageType        = trim($_POST['storage_type'] ?? 'raw');
            $energyConsumption  = (int)($_POST['energy_consumption'] ?? 50);
            $waterConsumption   = (int)($_POST['water_consumption'] ?? 10);
            $laborRequired      = (int)($_POST['labor_required'] ?? 10);

            $stmtOld = $db->prepare("SELECT image FROM building_types WHERE id = ?");
            $stmtOld->execute([$buildingId]);
            $oldBuilding = $stmtOld->fetch(\PDO::FETCH_ASSOC);
            $imageName = $oldBuilding['image'] ?? 'default.png';

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir($this->uploadDir)) {
                    mkdir($this->uploadDir, 0755, true);
                }

                $fileTmpPath   = $_FILES['image']['tmp_name'];
                $fileName      = $_FILES['image']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = uniqid('bld_') . '.' . $fileExtension;
                    if (move_uploaded_file($fileTmpPath, $this->uploadDir . $newFileName)) {
                        if ($imageName !== 'default.png' && file_exists($this->uploadDir . $imageName)) {
                            unlink($this->uploadDir . $imageName);
                        }
                        $imageName = $newFileName;
                    }
                }
            }

            $stmt = $db->prepare("UPDATE building_types SET name = ?, category = ?, complexity_type = ?, produces_product_id = ?, production_rate = ?, base_cost = ?, required_score = ?, storage_type = ?, energy_consumption = ?, water_consumption = ?, labor_required = ?, image = ? WHERE id = ?");
            $stmt->execute([
                $name, $category, $complexityType, $producesProductId, $productionRate, 
                $baseCost, $requiredScore, $storageType, $energyConsumption, $waterConsumption, 
                $laborRequired, $imageName, $buildingId
            ]);

            redirect('admin/buildingtype');
        }
    }

    public function delete($id) {
        $db = Database::getInstance();
        $buildingId = (int)$id;

        $stmt = $db->prepare("SELECT image FROM building_types WHERE id = ?");
        $stmt->execute([$buildingId]);
        $building = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($building && $building['image'] !== 'default.png') {
            $filePath = $this->uploadDir . $building['image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $db->prepare("DELETE FROM building_types WHERE id = ?")->execute([$buildingId]);
        redirect('admin/buildingtype');
    }
}