<?php
use components\objects\User;
use components\objects\Registration;

require_once __DIR__ . "/components/objects/User.php";
require_once __DIR__ . "/components/objects/Registration.php";
require_once __DIR__ . "/components/check_auth.php";
require_once __DIR__ . "/components/utils/links.php";

// 1. Ověření přihlášení
check_auth_user();

// 2. Načtení dat uživatele
$userId = $_COOKIE['user_id'] ?? 0;
$user = User::getUserById((int)$userId);

// Pokud uživatel neexistuje, odhlásíme ho
if (!$user) {
    header("Location: logout.php");
    exit;
}

// 3. Načtení akcí, na které je uživatel přihlášen
try {
    $myEvents = Registration::getEventsByUser($user->id);
} catch (Exception $e) {
    $myEvents = [];
    $error = "Nepodařilo se načíst vaše rezervace.";
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/components/head.php"; ?>
    <title>Můj Profil – Kacubó Kenrikai</title>

    <!-- Specifické styly pro profil -->
    <style>
        :root {
            --profile-bg: #fff;
            --text-color: #333;
            --muted-color: #777;
            --border-radius: 12px;
        }

        body {
            background-color: #f7f7f7; /* Stejné jako index */
        }

        /* Hlavní kontejner profilu */
        .profile-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Sekce uživatele - Minimalistická karta */
        .user-header {
            background-color: var(--profile-bg);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 40px;
            margin-bottom: 50px;
        }

        /* Avatar z iniciál */
        .avatar-circle {
            width: 100px;
            height: 100px;
            background-color: var(--primary-kacubo); /* Červená z webu */
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 300;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(162, 13, 13, 0.3);
        }

        .user-info {
            flex-grow: 1;
        }

        .user-info h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
            color: var(--text-color);
            font-weight: 700;
        }

        .user-meta {
            display: flex;
            gap: 30px;
            color: var(--muted-color);
            font-size: 1rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Nadpis sekce rezervací */
        .section-heading {
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: var(--text-color);
            border-left: 4px solid var(--primary-kacubo);
            padding-left: 15px;
        }

        /* Responzivita pro User Header */
        @media (max-width: 768px) {
            .user-header {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
                gap: 20px;
            }
            .user-meta {
                justify-content: center;
                gap: 15px;
                flex-direction: column;
            }
        }

        /* Úprava gridu událostí z index.css pro profil */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: var(--border-radius);
            color: var(--muted-color);
            border: 2px dashed #e0e0e0;
        }

        .btn-action {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 25px;
            background: var(--primary-kacubo);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .btn-action:hover {
            background: var(--primary-kacubo-hover);
            color: white;
            text-decoration: none;
        }

        /* Animace */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body id="home-body">
<header>
    <?php include "components/navbar.php"; ?>
</header>

<div class="profile-container">

    <!-- 1. Karta uživatele -->
    <section class="user-header">
        <div class="avatar-circle">
            <!-- Generování iniciál: První písmeno jména + první písmeno příjmení -->
            <?= strtoupper(mb_substr($user->firstname, 0, 1) . mb_substr($user->lastname, 0, 1)) ?>
        </div>
        <div class="user-info">
            <h1><?= htmlspecialchars($user->firstname . ' ' . $user->lastname) ?></h1>
            <div class="user-meta">
                <div class="meta-item">
                    <span>@<?= htmlspecialchars($user->username) ?></span>
                </div>
                <div class="meta-item">
                    <span><?= htmlspecialchars($user->email) ?></span>
                </div>
                <?php if($user->is_admin): ?>
                    <div class="meta-item" style="color: var(--primary-kacubo); font-weight: bold;">
                        ★ Administrátor
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- 2. Seznam rezervací -->
    <h2 class="section-heading">Moje rezervace</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($myEvents)): ?>
        <!-- Stav: Žádné rezervace -->
        <div class="empty-state">
            <h3>Zatím nemáte žádné rezervace</h3>
            <p>Podívejte se na seznam nadcházejících událostí a přihlaste se.</p>
            <a href="index.php" class="btn-action">Prohlédnout události</a>
        </div>
    <?php else: ?>
        <!-- Stav: Výpis rezervací -->
        <div class="events-grid">
            <?php foreach ($myEvents as $event): ?>
                <!-- Používáme stejnou strukturu karty jako na indexu pro konzistenci -->
                <div class="event-card joined">
                    <img class="event-image" src="<?= create_small_image_link($event->image_filename ?? "default.jpg") ?>" alt="Obrázek události">

                    <div class="event-content">
                        <div class="event-title"><?= htmlspecialchars($event->name) ?></div>
                        <div class="event-date">
                            <strong>Kdy:</strong> <?= date('d.m.Y H:i', strtotime($event->start_datetime)) ?>
                        </div>
                        <div class="event-location">
                            <strong>Kde:</strong> <?= htmlspecialchars($event->location) ?>
                        </div>
                        <div class="event-description">
                            <?= mb_strimwidth(htmlspecialchars($event->description), 0, 90, "...") ?>
                        </div>
                    </div>

                    <div class="event-actions">
                        <a class="link-as-btn rounded" href="<?= createLink("/event.php?".http_build_query(["id"=>$event->id])) ?>">Zobrazit detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>