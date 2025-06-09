<?php
// public/index.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';




//
// 1. Récupération des thèmes
//
try {
    $stmtThemes = $pdo->query('SELECT id, nom FROM themes ORDER BY nom');
    $themes = $stmtThemes->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur lors du chargement des thèmes : " . $e->getMessage());
}

//
// 2. Gestion des filtres GET
//
$selectedThemeId = isset($_GET['theme_id']) && ctype_digit($_GET['theme_id']) ? (int) $_GET['theme_id'] : 0;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

//
// 3. Pagination
//
$perPage = 6;
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

//
// 4. Construction dynamique de la clause WHERE
//
$conditions = [];
$params = [];

if ($selectedThemeId > 0) {
    $conditions[] = 'v.theme_id = :theme_id';
    $params[':theme_id'] = $selectedThemeId;
}

if ($keyword !== '') {
    $conditions[] = '(v.titre LIKE :keyword OR v.description LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}

//
// 5. Compter le nombre total de vidéos pour la pagination
//
$sqlCount = 'SELECT COUNT(*) FROM videos v';
if (!empty($conditions)) {
    $sqlCount .= ' WHERE ' . implode(' AND ', $conditions);
}
$stmtCount = $pdo->prepare($sqlCount);
foreach ($params as $key => $value) {
    $stmtCount->bindValue($key, $value);
}
$stmtCount->execute();
$totalVideos = (int) $stmtCount->fetchColumn();
$totalPages = ceil($totalVideos / $perPage);

//
// 6. Requête principale pour récupérer les vidéos
//
$sql = '
    SELECT v.id, v.titre, v.lien_youtube, v.description, v.date_ajout, t.nom AS theme
    FROM videos v
    JOIN themes t ON v.theme_id = t.id
';

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY v.date_ajout DESC LIMIT :limit OFFSET :offset';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue des vidéos</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f5f7fa, #c3cfe2);
            padding: 2rem;
            color: #2c3e50;
        }

        h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #34495e;
            margin-bottom: 2rem;
        }

        form {
            max-width: 900px;
            margin: 0 auto 2rem;
            background-color: #ecf0f1;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        form label {
            font-weight: bold;
            margin-right: 10px;
        }

        form select,
        form input[type="text"] {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form button {
            background-color: #3498db;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #2980b9;
        }

        form a {
            color: #e74c3c;
            font-weight: bold;
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .video-card {
            background-color: white;
            padding: 1.2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .video-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        .video-card p {
            margin: 0.3rem 0;
            font-size: 0.95rem;
        }

        .video-card a {
            display: inline-block;
            margin-top: 1rem;
            color: #2980b9;
            font-weight: bold;
            text-decoration: none;
        }

        .video-card a:hover {
            text-decoration: underline;
        }

        /* nav[aria-label="Pagination"] {
            text-align: center;
            margin-top: 2rem;
        }

        nav ul {
            list-style: none;
            display: inline-flex;
            gap: 10px;
            padding: 0;
        }

        nav li {
            display: inline;
        }

        nav a {
            text-decoration: none;
            padding: 8px 12px;
            background-color: #bdc3c7;
            color: #2c3e50;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        nav a:hover {
            background-color: #95a5a6;
        }

        nav strong {
            background-color: #3498db;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
        } */

        .no-result {
            text-align: center;
            font-style: italic;
            color: #7f8c8d;
            margin-top: 2rem;
        }

        /* Style du bouton de connexion */
        .top-right {
            position: absolute;
            top: 120px;
            right: 30px;
        }

        .top-right a.button-login {
            background-color: #27ae60;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .top-right a.button-login:hover {
            background-color: #1e8449;
        }
    </style>
</head>

<body>

 <!-- Bouton de connexion -->
  <br>
    <div class="top-right">
        <a href="login.php" class="button-login">Se connecter</a>
    </div>
    <h2>Catalogue de vidéos</h2>

    <!-- Formulaire de recherche/filtrage -->
    <form method="get" action="index.php">
        <label for="theme_id">Filtrer par thème :</label>
        <select name="theme_id" id="theme_id">
            <option value="0" <?= $selectedThemeId === 0 ? 'selected' : '' ?>>Tous les thèmes</option>
            <?php foreach ($themes as $theme): ?>
                <option value="<?= $theme['id'] ?>" <?= $theme['id'] === $selectedThemeId ? 'selected' : '' ?>>
                    <?= sanitize($theme['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="keyword">Mot-clé :</label>
        <input type="text" name="keyword" id="keyword" value="<?= sanitize($keyword) ?>" placeholder="Titre ou description">

        <button type="submit">Rechercher</button>
        <?php if ($selectedThemeId > 0 || $keyword !== ''): ?>
            <a href="index.php" style="margin-left: 10px;">Réinitialiser</a>
        <?php endif; ?>
    </form>

    <!-- Affichage des vidéos -->
    <?php if (empty($videos)): ?>
        <p>Aucune vidéo trouvée.</p>
    <?php else: ?>
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
                <div class="video-card" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
                    <h3><?= sanitize($video['titre']) ?></h3>
                    <p><strong>Thème :</strong> <?= sanitize($video['theme']) ?></p>
                    <p><strong>Date :</strong> <?= sanitize($video['date_ajout']) ?></p>
                    <p><?= nl2br(sanitize($video['description'])) ?></p>
                    <!-- <a href="<?= sanitize($video['lien_youtube']) ?>" target="_blank">Voir sur YouTube</a> -->
                      <a href="../admin/video_show.php?id=<?= (int)$video['id'] ?>">Voir la fiche</a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination">
                <ul style="list-style: none; display: flex; gap: 8px; margin-top: 20px; padding: 0;">
                    <?php if ($page > 1): ?>
                        <li>
                            <a href="index.php?page=<?= $page - 1 ?>&theme_id=<?= $selectedThemeId ?>&keyword=<?= urlencode($keyword) ?>">← Précédent</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li>
                            <?php if ($p === $page): ?>
                                <strong><?= $p ?></strong>
                            <?php else: ?>
                                <a href="index.php?page=<?= $p ?>&theme_id=<?= $selectedThemeId ?>&keyword=<?= urlencode($keyword) ?>"><?= $p ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li>
                            <a href="index.php?page=<?= $page + 1 ?>&theme_id=<?= $selectedThemeId ?>&keyword=<?= urlencode($keyword) ?>">Suivant →</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
