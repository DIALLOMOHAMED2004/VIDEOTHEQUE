<?php
// admin/video_show.php

require_once __DIR__ . '/../includes/functions.php';
//checkAdminSession(); // Protection : redirige vers login si pas connecté

require_once __DIR__ . '/../config/database.php';

// 1) Récupérer l'ID de la vidéo via GET et vérifier qu'il s'agit d'un entier valide
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID de vidéo non valide.');
}

$video_id = (int) $_GET['id'];

try {
    // 2) Requête pour récupérer les détails de la vidéo
    $sql = '
        SELECT
            v.titre,
            v.lien_youtube,
            v.description,
            v.date_ajout,
            t.nom AS theme
        FROM videos v
        JOIN themes t ON v.theme_id = t.id
        WHERE v.id = :id
        LIMIT 1
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $video_id, PDO::PARAM_INT);
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Erreur lors de la récupération de la vidéo : ' . $e->getMessage());
}

if (!$video) {
    die('Vidéo introuvable.');
}

// 3) Extraire l’ID YouTube depuis l’URL pour générer l’iframe
$youtubeId = '';
// On parse l’URL pour récupérer le paramètre "v"
$parsed = parse_url($video['lien_youtube']);
if (isset($parsed['query'])) {
    parse_str($parsed['query'], $queryVars);
    if (isset($queryVars['v'])) {
        $youtubeId = $queryVars['v'];
    }
}
// Si l’URL n’est pas au format "watch?v=..." ou pas reconnue, on laissera $youtubeId vide
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails : <?= sanitize($video['titre']) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f5f7fa, #c3cfe2);
            color: #333;
            padding: 2rem;
            margin: 0;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        strong {
            color: #2c3e50;
        }

        iframe {
            display: block;
            margin: 1rem auto;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .video-container {
            max-width: 800px;
            margin: 2rem auto;
            background-color: #ffffffee;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .description {
            background-color: #f9f9f9;
            padding: 1rem;
            border-left: 4px solid #3498db;
            border-radius: 8px;
            margin-top: 1.5rem;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 2rem;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="video-container">
        <h2><?= sanitize($video['titre']) ?></h2>
        <p><strong>Thème :</strong> <?= sanitize($video['theme']) ?></p>
        <p><strong>Date d’ajout :</strong> <?= sanitize($video['date_ajout']) ?></p>

        <?php if ($youtubeId !== ''): ?>
            <div>
                <iframe
                    width="560"
                    height="315"
                    src="https://www.youtube.com/embed/<?= sanitize($youtubeId) ?>"
                    frameborder="0"
                    allowfullscreen>
                </iframe>
            </div>
        <?php else: ?>
            <p>
                <a href="<?= sanitize($video['lien_youtube']) ?>" target="_blank">
                    Voir la vidéo sur YouTube
                </a>
            </p>
        <?php endif; ?>

        <?php if (trim($video['description']) !== ''): ?>
            <h3>Description</h3>
            <div class="description">
                <p><?= nl2br(sanitize($video['description'])) ?></p>
            </div>
        <?php endif; ?>

        <p class="back-link"><a class="button" href="video_list.php">← Retour à la liste des vidéos</a></p>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
