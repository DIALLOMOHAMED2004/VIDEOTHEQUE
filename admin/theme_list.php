<?php
// admin/theme_list.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

//
// 1) Définir la pagination
//
$perPage = 10; // Nombre de thèmes par page
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0
    ? (int) $_GET['page']
    : 1;
$offset = ($page - 1) * $perPage;

//
// 2) Récupérer le nombre total de thèmes
//
try {
    $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM themes');
    $stmtCount->execute();
    $totalThemes = (int) $stmtCount->fetchColumn();
} catch (Exception $e) {
    die('Erreur lors du comptage des thèmes : ' . $e->getMessage());
}

$totalPages = (int) ceil($totalThemes / $perPage);

//
// 3) Récupérer les thèmes de la page courante
//
try {
    $stmt = $pdo->prepare('
        SELECT id, nom 
        FROM themes 
        ORDER BY nom 
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Erreur lors de la récupération des thèmes : ' . $e->getMessage());
}

// (Optionnel) Message flash si ajouté/modifié/supprimé
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des thèmes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f0f9ff, #fff);
            color: #333;
            padding: 2rem;
        }

        h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            max-width: 600px;
            margin: 1rem auto;
            text-align: center;
        }

        .actions {
            text-align: center;
            margin-bottom: 1rem;
        }

        .actions a {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin: 0 5px;
            transition: background 0.3s;
        }

        .actions a:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        thead {
            background-color: #16a085;
            color: white;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #ecf0f1;
        }

        td a {
            color: #2c3e50;
            font-weight: bold;
            text-decoration: none;
        }

        td a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .no-result {
            text-align: center;
            font-style: italic;
            color: #888;
            margin-top: 2rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination a,
        .pagination span {
            display: block;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: #2c3e50;
        }
        .pagination a {
            background-color: #ecf0f1;
            transition: background 0.2s;
        }
        .pagination a:hover {
            background-color: #bdc3c7;
        }
        .pagination .current-page {
            background-color: #16a085;
            color: #fff;
        }
    </style>
</head>
<body>

    <h2>Liste des thèmes</h2>

    <!-- Messages flash -->
    <?php if ($msg === 'ajout_ok'): ?>
        <div class="message">✅ Thème ajouté avec succès.</div>
    <?php elseif ($msg === 'update_ok'): ?>
        <div class="message">✏️ Thème modifié avec succès.</div>
    <?php elseif ($msg === 'delete_ok'): ?>
        <div class="message">🗑️ Thème supprimé avec succès.</div>
    <?php endif; ?>

    <!-- Liens d'action -->
    <div class="actions">
        <a class="button" href="theme_create.php">+ Ajouter un thème</a>
        <a class="button" href="dashboard.php">← Retour au tableau de bord</a>
    </div>

    <!-- Tableau des thèmes -->
    <?php if (count($themes) === 0): ?>
        <p class="no-result">Aucun thème trouvé.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nom du thème</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($themes as $theme): ?>
                    <tr>
                        <td><?= sanitize($theme['nom']) ?></td>
                        <td>
                            <a href="theme_edit.php?id=<?= (int)$theme['id'] ?>">✏️ Modifier</a>
                            &nbsp;|&nbsp;
                            <a href="theme_delete_confirm.php?id=<?= (int)$theme['id'] ?>">🗑️ Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <!-- Lien vers la page précédente -->
                <?php if ($page > 1): ?>
                    <li>
                        <a href="theme_list.php?page=<?= $page - 1 ?>">
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
                            <a href="theme_list.php?page=<?= $p ?>"><?= $p ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <!-- Lien vers la page suivante -->
                <?php if ($page < $totalPages): ?>
                    <li>
                        <a href="theme_list.php?page=<?= $page + 1 ?>">
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
