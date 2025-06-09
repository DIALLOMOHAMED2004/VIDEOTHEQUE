<?php
// admin/video_list.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si pas connecté

require_once __DIR__ . '/../config/database.php';

//
// 1) Récupérer la liste des thèmes pour la dropdown de filtrage
//
try {
    $stmtThemes = $pdo->prepare('SELECT id, nom FROM themes ORDER BY nom');
    $stmtThemes->execute();
    $themes = $stmtThemes->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Erreur lors de la récupération des thèmes : ' . $e->getMessage());
}

//
// 2) Récupérer les paramètres de filtre en GET
//
$selectedThemeId = isset($_GET['theme_id']) && ctype_digit($_GET['theme_id'])
    ? (int) $_GET['theme_id']
    : 0;

$keyword = isset($_GET['keyword'])
    ? trim($_GET['keyword'])
    : '';

//
// 3) Pagination : définir la page courante et le nombre d’entrées par page
//
$perPage = 10; // Nombre de vidéos par page
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0
    ? (int) $_GET['page']
    : 1;
$offset = ($page - 1) * $perPage;

//
// 4) Construire dynamiquement la clause WHERE selon les filtres
//
$conditions = [];
$params = [];

// Filtre par thème
if ($selectedThemeId > 0) {
    $conditions[] = 'v.theme_id = :theme_id';
    $params[':theme_id'] = $selectedThemeId;
}

// Filtre par mot-clé (dans titre ou description)
if ($keyword !== '') {
    $conditions[] = '(v.titre LIKE :keyword OR v.description LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}

// 5) Calculer le nombre total de vidéos correspondant aux filtres
$sqlCount = 'SELECT COUNT(*) FROM videos v';
if (count($conditions) > 0) {
    $sqlCount .= ' WHERE ' . implode(' AND ', $conditions);
}
try {
    $stmtCount = $pdo->prepare($sqlCount);
    foreach ($params as $placeholder => $value) {
        if ($placeholder === ':theme_id') {
            $stmtCount->bindValue($placeholder, $value, PDO::PARAM_INT);
        } else {
            $stmtCount->bindValue($placeholder, $value, PDO::PARAM_STR);
        }
    }
    $stmtCount->execute();
    $totalVideos = (int) $stmtCount->fetchColumn();
} catch (Exception $e) {
    die('Erreur lors du comptage des vidéos : ' . $e->getMessage());
}

$totalPages = (int) ceil($totalVideos / $perPage);

//
// 6) Construire la requête principale avec LIMIT et OFFSET
//
$sql = '
    SELECT 
        v.id,
        v.titre,
        v.lien_youtube,
        v.date_ajout,
        t.nom AS theme
    FROM videos v
    JOIN themes t ON v.theme_id = t.id
';

if (count($conditions) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY v.date_ajout DESC
          LIMIT :limit OFFSET :offset';

try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $placeholder => $value) {
        if ($placeholder === ':theme_id') {
            $stmt->bindValue($placeholder, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($placeholder, $value, PDO::PARAM_STR);
        }
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Erreur lors de la récupération des vidéos : ' . $e->getMessage());
}

// (Optionnel) Vérifier si un message de confirmation est présent en GET
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des vidéos</title>
    <style>
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(to right, #f8f9fa, #e6f2ff);
    margin: 0;
    padding: 20px;
    color: #333;
}

h2 {
    text-align: center;
    color: #004085;
    margin-bottom: 30px;
}

.alert {
    padding: 10px 15px;
    margin-bottom: 20px;
    border-radius: 6px;
    font-weight: bold;
    text-align: center;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.button,
button,
a.button {
    display: inline-block;
    background-color: #007bff;
    color: white !important;
    padding: 10px 18px;
    border-radius: 6px;
    border: none;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.button:hover,
button:hover {
    background-color: #0056b3;
}

form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    margin: 20px 0;
    justify-content: center;
}

form label {
    font-weight: bold;
    color: #0056b3;
}

form input,
form select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1em;
    min-width: 200px;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 30px 0;
    background-color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    overflow: hidden;
}

table thead {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

table th,
table td {
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
}

table tr:hover {
    background-color: #f1f9ff;
}

.pagination {
    list-style: none;
    display: flex;
    justify-content: center;
    padding-left: 0;
    gap: 8px;
    margin-top: 30px;
}

.pagination li a,
.pagination li span {
    padding: 8px 14px;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.pagination li span.current-page {
    background-color: #0056b3;
    font-weight: bold;
}

.pagination li a:hover {
    background-color: #0056b3;
}

a[href*="video_edit"],
a[href*="video_delete_confirm"],
a[href*="video_show"] {
    color: #007bff;
    font-weight: bold;
    text-decoration: underline;
}

a[href*="video_edit"]:hover,
a[href*="video_delete_confirm"]:hover,
a[href*="video_show"]:hover {
    color: #0056b3;
}

    </style>
</head>
<body>
    <h2>Liste des vidéos</h2>

    <!-- Affichage d'un message flash si nécessaire -->
    <?php if ($msg === 'ajout_ok'): ?>
        <p class="alert alert-success">Vidéo ajoutée avec succès.</p>
    <?php elseif ($msg === 'update_ok'): ?>
        <p class="alert alert-success">Vidéo modifiée avec succès.</p>
    <?php elseif ($msg === 'delete_ok'): ?>
        <p class="alert alert-success">Vidéo supprimée avec succès.</p>
    <?php endif; ?>

    <!-- Liens d'action -->
    <p>
        <a class="button" href="video_create.php">+ Ajouter une vidéo</a>
        &nbsp;|&nbsp;
        <a class="button" href="dashboard.php">← Tableau de bord</a>
    </p>

    <!-- Formulaire de filtrage -->
    <form method="get" action="video_list.php">
        <!-- Filtre par thème -->
        <label for="theme_id">Filtrer par thème :</label>
        <select id="theme_id" name="theme_id">
            <option value="0" <?= $selectedThemeId === 0 ? 'selected' : '' ?>>Tous les thèmes</option>
            <?php foreach ($themes as $theme): ?>
                <option 
                    value="<?= $theme['id'] ?>" 
                    <?= $theme['id'] === $selectedThemeId ? 'selected' : '' ?>
                >
                    <?= sanitize($theme['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Filtre par mot-clé -->
        <label for="keyword">Mot-clé :</label>
        <input 
            type="text" 
            id="keyword" 
            name="keyword" 
            value="<?= sanitize($keyword) ?>" 
            placeholder="Rechercher dans le titre ou la description"
        >

        <button type="submit">Filtrer</button>
        <?php if ($selectedThemeId > 0 || $keyword !== ''): ?>
            <a href="video_list.php" style="margin-left: 10px;">Réinitialiser</a>
        <?php endif; ?>
    </form>

    <!-- Affichage des résultats filtrés -->
    <?php if (count($videos) === 0): ?>
        <p>Aucune vidéo trouvée selon vos critères.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Thème</th>
                        <th>Date d’ajout</th>
                        <th>Lien YouTube</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                        <tr>
                            <td><?= sanitize($video['titre']) ?></td>
                            <td><?= sanitize($video['theme']) ?></td>
                            <td><?= sanitize($video['date_ajout']) ?></td>
                            <td>
                            <a href="<?= sanitize($video['lien_youtube']) ?>" target="_blank">
                                Voir
                            </a>
                        </td>
                        <td>
                            <a href="video_edit.php?id=<?= (int)$video['id'] ?>">✏️</a>
                        </td>
                        <td>
                            <a href="video_delete_confirm.php?id=<?= (int)$video['id'] ?>">🗑️</a>
                        </td>
                        <td>
                            <a href="video_show.php?id=<?= (int)$video['id'] ?>">👁️</a>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

         <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <!-- Lien vers la page précédente -->
                <?php if ($page > 1): ?>
                    <li>
                        <a href="video_list.php?page=<?= $page - 1 ?>">
                            ← Précédent
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Liens numérotés -->
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li>
                        <?php if ($p === $page): ?>
                            <span class="current-page"><?= $p ?></span>
                        <?php else: ?>
                            <a href="video_list.php?page=<?= $p ?>"><?= $p ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <!-- Lien vers la page suivante -->
                <?php if ($page < $totalPages): ?>
                    <li>
                        <a href="video_list.php?page=<?= $page + 1 ?>">
                            Suivant →
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
