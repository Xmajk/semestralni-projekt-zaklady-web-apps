<?php
use components\objects\Event;
require_once __DIR__ . "/../components/objects/Event.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";
require_once __DIR__ . "/../components/utils/image_helper.php"; // Náš nový helper

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
    <link rel="stylesheet" href="<?= createStylesLink("/toogleswitch.css") ?>">
    <link rel="stylesheet" href="<?= createStylesLink("/table.css") ?>"> <!-- Přidání stylů pro tabulku -->
    <style>
        /* Styly pro formulář, podobné jako v users.php */
        #add-event-form {
            display: none; /* Skryto ve výchozím stavu */
            background: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            max-width: 800px; /* Omezení šířky formuláře */
            margin-left: auto;
            margin-right: auto;
        }
        #add-event-form h2 {
            text-align: center;
            margin-top: 0;
            color: #333;
        }
        #add-event-form .form-wrapper {
            margin-bottom: 15px;
        }
        #add-event-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        #add-event-form input[type="text"],
        #add-event-form input[type="datetime-local"],
        #add-event-form input[type="file"],
        #add-event-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Zajišťuje, že padding neovlivní celkovou šířku */
            font-size: 14px;
        }
        #add-event-form input[type="file"] {
            padding: 6px;
            background: white;
            cursor: pointer;
        }
        #add-event-form textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }
        #add-event-form button[type="submit"] {
            background-color: #007b55; /* Zelená barva pro uložení */
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.2s;
        }
        #add-event-form button[type="submit"]:hover {
            background-color: #006347;
        }

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
        <h2>Nová událost</h2>

        <div id="name-wrapper" class="form-wrapper">
            <label for="form-name">Název</label>
            <input id="form-name" type="text" name="name" placeholder="Název události" required>
        </div>

        <div id="description-wrapper" class="form-wrapper">
            <label for="form-description">Popis</label>
            <textarea id="form-description" name="description" placeholder="Podrobný popis události..."></textarea>
        </div>

        <div id="location-wrapper" class="form-wrapper">
            <label for="form-location">Místo</label>
            <input id="form-location" type="text" name="location" placeholder="Místo konání (např. adresa nebo 'Online')" required>
        </div>

        <div id="start-datetime-wrapper" class="form-wrapper">
            <label for="form-start-datetime">Datum a čas zahájení</label>
            <input id="form-start-datetime" type="datetime-local" name="start_datetime" required>
        </div>

        <div id="registration-deadline-wrapper" class="form-wrapper">
            <label for="form-registration-deadline">Datum a čas konce registrací</label>
            <input id="form-registration-deadline" type="datetime-local" name="registration_deadline" required>
        </div>

        <!-- Nové pole pro obrázek -->
        <div id="image-wrapper" class="form-wrapper">
            <label for="form-image">Obrázek události (JPG, PNG)</label>
            <input id="form-image" type="file" name="image" accept="image/jpeg, image/png">
        </div>

        <button type="submit">Uložit událost</button>
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
                    <!-- Přidán atribut 'mark' pro funkci filtrování -->
                    <td mark="name" class="clip"><?= htmlspecialchars($event->name) ?></td>
                    <td class="muted">
                        <?php
                        // Formátování data pro lepší čitelnost
                        try {
                            echo htmlspecialchars(date('d. m. Y H:i', strtotime($event->registration_deadline)));
                        } catch (Exception $e) {
                            echo htmlspecialchars($event->registration_deadline); // Fallback
                        }
                        ?>
                    </td>
                    <!-- TODO: Nahraďte '#' odkazy na skutečné editační a mazací skripty -->
                    <td><a href="event_edit.php?id=<?= $event->id ?>">upravit</a></td>
                    <td><a href="event_delete.php?id=<?= $event->id ?>" onclick="return confirm('Opravdu chcete smazat tuto událost?');">vymazat</a></td>
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
                const nameCell = row.querySelector('[mark="name"]');

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

</body>
</html>