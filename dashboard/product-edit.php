<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

if (!is_admin() && !is_seller()) {
    header('Location: ../index.php');
    exit();
}

$page_title = "Ajouter/Modifier un produit - Artisanat Local";

// Vérifier si c'est une modification
$product_id = $_GET['id'] ?? null;
$is_edit = !!$product_id;

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Récupérer les données du produit si modification
$product = null;
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérifier que l'utilisateur a le droit de modifier ce produit
    if ($product && is_seller() && $product['artisan_id'] != $_SESSION['user_id']) {
        header('Location: products.php');
        exit();
    }
    
    if (!$product) {
        header('Location: products.php');
        exit();
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $detailed_description = $_POST['detailed_description'] ?? '';
    $price = $_POST['price'] ?? '';
    $compare_price = $_POST['compare_price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    $errors = [];
    
    if (empty($name)) $errors[] = "Le nom du produit est requis";
    if (empty($description)) $errors[] = "La description est requise";
    if (empty($price) || !is_numeric($price) || $price <= 0) $errors[] = "Le prix doit être un nombre positif";
    if (empty($category_id)) $errors[] = "La catégorie est requise";
    if (empty($stock) || !is_numeric($stock) || $stock < 0) $errors[] = "Le stock doit être un nombre positif ou zéro";
    
    if (empty($errors)) {
        try {
            if ($is_edit) {
                // Mise à jour
                $sql = "UPDATE products SET name = ?, description = ?, detailed_description = ?, price = ?, compare_price = ?, category_id = ?, stock = ?, sku = ?, tags = ?, is_featured = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $description, $detailed_description, $price, $compare_price ?: null, 
                    $category_id, $stock, $sku, $tags, $is_featured, $is_active, $product_id
                ]);
                $message = "Produit mis à jour avec succès";
            } else {
                // Création
                $sql = "INSERT INTO products (name, description, detailed_description, price, compare_price, category_id, artisan_id, stock, sku, tags, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $description, $detailed_description, $price, $compare_price ?: null, 
                    $category_id, $_SESSION['user_id'], $stock, $sku, $tags, $is_featured, $is_active
                ]);
                $product_id = $pdo->lastInsertId();
                $message = "Produit créé avec succès";
            }
            
            // Gestion de l'upload d'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . $product_id . '_' . time() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                    // Mettre à jour le chemin de l'image dans la base
                    $update_stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
                    $update_stmt->execute(['uploads/products/' . $filename, $product_id]);
                }
            }
            
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = 'success';
            header('Location: products.php');
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<main class="dashboard">
    <?php include 'includes/dashboard-nav.php'; ?>
    
    <div class="dashboard-content">
        <div class="container">
            <div class="dashboard-header">
                <h1><?php echo $is_edit ? 'Modifier le produit' : 'Ajouter un produit'; ?></h1>
                <a href="products.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour aux produits
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-grid">
                        <div class="form-column">
                            <!-- Informations de base -->
                            <div class="form-section">
                                <h3>Informations de base</h3>
                                
                                <div class="form-group">
                                    <label for="name" class="form-label">Nom du produit *</label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description" class="form-label">Description courte *</label>
                                    <textarea id="description" name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                    <small>Description affichée dans les listes de produits</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="detailed_description" class="form-label">Description détaillée</label>
                                    <textarea id="detailed_description" name="detailed_description" class="form-control" rows="6"><?php echo htmlspecialchars($product['detailed_description'] ?? ''); ?></textarea>
                                    <small>Description complète affichée sur la page produit</small>
                                </div>
                            </div>

                            <!-- Prix et stock -->
                            <div class="form-section">
                                <h3>Prix et stock</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="price" class="form-label">Prix * (€)</label>
                                        <input type="number" id="price" name="price" class="form-control" 
                                               step="0.01" min="0" 
                                               value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="compare_price" class="form-label">Prix de comparaison (€)</label>
                                        <input type="number" id="compare_price" name="compare_price" class="form-control" 
                                               step="0.01" min="0" 
                                               value="<?php echo htmlspecialchars($product['compare_price'] ?? ''); ?>">
                                        <small>Ancien prix barré pour les promotions</small>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="stock" class="form-label">Stock *</label>
                                    <input type="number" id="stock" name="stock" class="form-control" 
                                           min="0" 
                                           value="<?php echo htmlspecialchars($product['stock'] ?? '0'); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="sku" class="form-label">Référence (SKU)</label>
                                    <input type="text" id="sku" name="sku" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>">
                                    <small>Référence unique du produit</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-column">
                            <!-- Image du produit -->
                            <div class="form-section">
                                <h3>Image du produit</h3>
                                
                                <div class="form-group">
                                    <label for="image" class="form-label">Image principale</label>
                                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                    
                                    <?php if ($is_edit && !empty($product['image'])): ?>
                                        <div class="current-image">
                                            <p>Image actuelle :</p>
                                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 style="max-width: 200px; height: auto;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Catégorie et tags -->
                            <div class="form-section">
                                <h3>Catégorie et organisation</h3>
                                
                                <div class="form-group">
                                    <label for="category_id" class="form-label">Catégorie *</label>
                                    <select id="category_id" name="category_id" class="form-control" required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($product['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" id="tags" name="tags" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['tags'] ?? ''); ?>">
                                    <small>Séparez les tags par des virgules</small>
                                </div>
                            </div>

                            <!-- Paramètres -->
                            <div class="form-section">
                                <h3>Paramètres</h3>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="is_featured" name="is_featured" 
                                           class="form-check-input" 
                                           <?php echo ($product['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <label for="is_featured" class="form-check-label">Produit en vedette</label>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" 
                                           class="form-check-input" 
                                           <?php echo ($product['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <label for="is_active" class="form-check-label">Produit actif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $is_edit ? 'Mettre à jour' : 'Créer le produit'; ?>
                        </button>
                        <a href="products.php" class="btn btn-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<style>
.product-form {
    max-width: 100%;
}

.form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.form-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: var(--border-radius);
}

.form-section h3 {
    margin-bottom: 1rem;
    font-size: 1.2rem;
    color: var(--dark-color);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-check {
    margin-bottom: 1rem;
}

.form-check-input {
    margin-right: 0.5rem;
}

.current-image {
    margin-top: 1rem;
    padding: 1rem;
    background: white;
    border-radius: var(--border-radius);
    border: 1px solid var(--gray-light);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 2rem;
    border-top: 1px solid var(--gray-light);
    margin-top: 2rem;
}

@media (max-width: 968px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>