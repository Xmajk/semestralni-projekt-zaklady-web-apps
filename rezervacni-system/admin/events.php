<?php
use components\objects\Event;
require_once __DIR__ . "/../components/objects/Event.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";
require_once __DIR__ . "/../components/utils/image_helper.php";

check_auth_admin();

$error = NULL;

// Zpracování formuláře pro přidání nové události
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $image_db_filename = null; // Název souboru pro uložení do DB

    // Zpracování obrázku
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $source_path = $file['tmp_name'];

        // Zjištění typu obrázku
        $image_type = exif_imagetype($source_path);
        $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];

        if (!in_array($image_type, $allowed_types)) {
            $error = "Neplatný formát obrázku. Povoleny jsou pouze JPEG a PNG.";
        } else {
            // Vytvoření unikátního názvu souboru
            $base_filename = "event_" . uniqid();
            $extension = ($image_type == IMAGETYPE_JPEG) ? '.jpg' : '.png';
            $image_db_filename = $base_filename . $extension; // např. event_60b8d29f1c3a4.jpg

            // Cílové adresáře (musí existovat a být zapisovatelné!)
            $dir_large = __DIR__ . "/../public/imgs/events/large/";
            $dir_thumb = __DIR__ . "/../public/imgs/events/thumb/";

            // Vytvoření adresářů, pokud neexistují
            if (!is_dir($dir_large)) @mkdir($dir_large, 0755, true);
            if (!is_dir($dir_thumb)) @mkdir($dir_thumb, 0755, true);

            // Zpracování velké verze (1200px)
            $success_large = \components\utils\processUploadedImage($source_path, $image_type, $dir_large, $image_db_filename, 1200);
            // Zpracování náhledu (300px)
            $success_thumb = \components\utils\processUploadedImage($source_path, $image_type, $dir_thumb, $image_db_filename, 300);

            if (!$success_large || !$success_thumb) {
                $error = "Chyba při ukládání obrázku. Zkontrolujte práva zápisu do adresáře 'public/imgs/events/'.";
                $image_db_filename = null; // Reset
            }
        }
    }

    // Uložení dat do databáze
    if ($error === NULL) { // Pokud nenastala žádná chyba (ani při nahrávání obrázku)

        $newEvent = new Event();
        $newEvent->name = $_POST["name"];
        $newEvent->description = $_POST["description"];
        $newEvent->location = $_POST["location"];
        $newEvent->start_datetime = $_POST["start_datetime"];
        $newEvent->registration_deadline = $_POST["registration_deadline"];
        $newEvent->image_filename = $image_db_filename; // Uložíme název souboru (nebo null, pokud nebyl nahrán)

        // Metoda z Event.php
        if ($newEvent->insert()) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $error = "Chyba při ukládání události do databáze.";
        }

    }

    // Pokud chyba nastala, zobrazíme ji
    if ($error) {
        // Použití alertu je zde pro jednoduchost, v produkci by bylo lepší chybovou hlášku vypsat do HTML
        echo "<script>alert('".htmlspecialchars($error)."');</script>";
    }
}


// Načtení událostí - metoda getAllOrdered() je řadí podle start_datetime DESC
$events = Event::getAllOrdered();

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <link rel="stylesheet" href="<?= createStylesLink("/forms.css") ?>">
    <style>

        /* Obalovač filtru */
        .filter-wrapper {
            margin-bottom: 15px;
            max-width: 400px; /* Omezení šířky filtru */
        }
        .filter-wrapper label {
            font-weight: bold;
            margin-right: 10px;
        }
        .filter-wrapper input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body id="admin-users-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">

    <button class="expand-btn" onclick="toggleForm()">Přidat událost</button>

    <!-- !! DŮLEŽITÉ: Přidán enctype pro nahrávání souborů !! -->
    <form id="add-event-form" autocomplete="off" method="post" action="" enctype="multipart/form-data">
        <div id="name-wrapper" class="form-wrapper">
            <label for="form-name">Název<span class="required"></span></label>
            <input id="form-name" type="text" name="name" placeholder="Název události" required>
            <span id="error-name" class="validation-error <?= isset($errors['name']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['name'] ?? '') ?>
            </span>
        </div>

        <div id="description-wrapper" class="form-wrapper">
            <label for="form-description">Popis</label>
            <textarea id="form-description" name="description" placeholder="Podrobný popis události..."></textarea>
            <span id="error-description" class="validation-error <?= isset($errors['description']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['description'] ?? '') ?>
            </span>
        </div>

        <div id="location-wrapper" class="form-wrapper">
            <label for="form-location">Místo<span class="required"></span></label>
            <input id="form-location" type="text" name="location" placeholder="Místo konání (např. adresa nebo 'Online')" required>
            <span id="error-location" class="validation-error <?= isset($errors['location']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['location'] ?? '') ?>
            </span>
        </div>

        <div id="price-wrapper" class="form-wrapper">
            <label for="form-price">Cena</label>
            <input id="form-price" type="text" placeholder="Cena události" name="price" required>
            <span id="error-price" class="validation-error <?= isset($errors['price']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['price'] ?? '') ?>
            </span>
        </div>

        <div id="capacity-wrapper" class="form-wrapper">
            <label for="form-capacity">Kapacita</label>
            <input id="form-capacity" type="text" placeholder="Kapacita" name="capacity" required>
            <span id="error-capacity" class="validation-error <?= isset($errors['capacity']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['capacity'] ?? '') ?>
            </span>
        </div>

        <div id="start-datetime-wrapper" class="form-wrapper">
            <label for="form-start-datetime">Datum a čas zahájení<span class="required"></span></label>
            <input id="form-start-datetime" type="datetime-local" name="start_datetime" required>
            <span id="error-start-datetime" class="validation-error <?= isset($errors['start_datetime']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['start_datetime'] ?? '') ?>
            </span>
        </div>

        <div id="registration-deadline-wrapper" class="form-wrapper">
            <label for="form-registration-deadline">Datum a čas konce registrací<span class="required"></span></label>
            <input id="form-registration-deadline" type="datetime-local" name="registration_deadline" required>
            <span id="error-registration-deadline" class="validation-error <?= isset($errors['registration_deadline']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['registration_deadline'] ?? '') ?>
            </span>
        </div>

        <!-- Nové pole pro obrázek -->
        <div id="image-wrapper" class="form-wrapper">
            <label for="form-image">Obrázek události (JPG, PNG)</label>
            <input id="form-image" type="file" name="image" accept="image/jpeg, image/png">
            <span id="error-image" class="validation-error <?= isset($errors['image']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['image'] ?? '') ?>
            </span>
        </div>

        <button type="submit">Uložit</button>
    </form>

    <div class="filter-wrapper">
        <label for="filter">Filtrovat podle názvu:</label>
        <input name="filter" id="event-filter" type="text" placeholder="Zadejte název události...">
    </div>

    <!-- Tabulka pro výpis událostí -->
    <div class="table-wrap" data-density="comfy">
        <table class="table">
            <thead>
            <tr>
                <th classs="sortable" aria-sort="descending">Název události</th> <!-- Seřazeno dle data konání (v PHP), ne klikatelně -->
                <th>Datum registrace do</th>
                <th>Registrace</th>
                <th>Upravit</th>
                <th>Smazat</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #777;">
                        Zatím nebyly vytvořeny žádné události.
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td class="filter-element" class="clip"><?= htmlspecialchars($event->name) ?></td>
                    <td class="muted">
                        <?php
                            try {
                                echo htmlspecialchars(date('d. m. Y H:i', strtotime($event->registration_deadline)));
                            } catch (Exception $e) {
                                echo htmlspecialchars($event->registration_deadline); // Fallback
                            }
                        ?>
                    </td>
                    <td>20/25</td>
                    <!-- TODO: Nahraďte '#' odkazy na skutečné editační a mazací skripty -->
                    <td><a href="event_edit.php?id=<?= $event->id ?>">upravit</a></td>
                    <td><a href="event_delete.php?id=<?= $event->id ?>" onclick="return confirm('Opravdu chcete smazat tuto událost?');">vymazat</a></td>
                    <td><a class="download-link" href="events.php"><img src="<?= createPublicLink("/icons/dwnload.svg") ?>"></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    // Funkce pro zobrazení/skrytí formuláře
    function toggleForm() {
        const form = document.getElementById('add-event-form');
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }

    // Filtrování tabulky
    const filterInput = document.getElementById('event-filter');
    const rows = document.querySelectorAll('tbody tr');

    if(filterInput) {
        filterInput.addEventListener('input', function () {
            const filterValue = this.value.toLowerCase().trim();

            rows.forEach(row => {
                // Ujistěte se, že buňka existuje a má atribut 'mark'
                const nameCell = row.querySelector('.filter-element');

                if (nameCell) { // Pokud je to řádek s událostí
                    const name = nameCell.textContent.toLowerCase();
                    if (name.includes(filterValue)) {
                        row.style.display = ''; // Zobrazit řádek
                    } else {
                        row.style.display = 'none'; // Skrýt řádek
                    }
                } else if (filterValue === "" && row.querySelector('td[colspan]')) {
                    // Zobrazit zprávu "Zatím nebyly vytvořeny..." pokud je filtr prázdný
                    row.style.display = '';
                } else if (row.querySelector('td[colspan]')) {
                    // Skrýt zprávu "Zatím nebyly vytvořeny..." pokud se filtruje
                    row.style.display = 'none';
                }
            });
        });
    }
</script>
<script src="<?= createScriptLink("/validation/events.js") ?>"></script>

</body>
</html>