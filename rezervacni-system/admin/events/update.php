<?php
    use components\objects;
    use components\objects\Event;
    use components\objects\Registration;
    require_once __DIR__ . "/../../components/objects/User.php";
    require_once __DIR__ . "/../../components/objects/Event.php";
    require_once __DIR__ . "/../../components/objects/Registration.php";
    require_once __DIR__ . "/../../components/utils/links.php";
    require_once __DIR__ . "/../../components/check_auth.php";

    session_start();
    check_auth_admin();

    $event_id=$_GET['id']??null;
    if(!is_numeric($event_id)) {
        redirect_to(create_error_link("Nevalidní id"));
    }
    $event_id=intval($event_id);
    $event = Event::getById($event_id);
    if(!isset($event)) {
        redirect_to(create_error_link("Událost nebyla nalezena"));
    }


    $errors = $_SESSION['form_errors'] ?? [];
    $formData = $_SESSION['form_data'] ?? [];
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $formData = [
                "name" => $_POST["name"],
                "description" => $_POST["description"],
                "location" => $_POST["location"],
                "price" => $_POST["price"],
                "capacity" => $_POST["capacity"],
                "start_datetime" => $_POST["start_datetime"],
                "registration_deadline" => $_POST["registration_deadline"],
                "image" => $_FILES["image"],
        ];

        $_SESSION['form_data'] = $formData;
        $newEvent = new Event();
        $event = Event::getById($event_id);
        $errors = $event->fill($formData);
        $_SESSION['form_errors'] = $errors;

        if (isset($formData['image']) && $formData['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $source_path = $file['tmp_name'];

            $image_type = exif_imagetype($source_path);
            $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];

            if (!in_array($image_type, $allowed_types)) {
                $errors["image"] = "Neplatný formát obrázku. Povoleny jsou pouze JPEG a PNG.";
            } else {
                $base_filename = "event_" . uniqid();
                $extension = ($image_type == IMAGETYPE_JPEG) ? '.jpg' : '.png';
                $event->image_filename = $base_filename . $extension;

                $dir_large = __DIR__ . "/../public/imgs/events/large/";
                $dir_thumb = __DIR__ . "/../public/imgs/events/thumb/";
                if (!is_dir($dir_large)) @mkdir($dir_large, 0777, true);
                if (!is_dir($dir_thumb)) @mkdir($dir_thumb, 0777, true);

                $success_large = processUploadedImage($source_path, $image_type, $dir_large, $event->image_filename, 1200);
                $success_thumb = processUploadedImage($source_path, $image_type, $dir_thumb, $event->image_filename, 300);

                if (!$success_large || !$success_thumb) {
                    $errors["general"] = "Chyba při ukládání obrázku. Zkontrolujte práva zápisu do adresáře 'public/imgs/events/'.";
                }
            }
        }

        if (count($errors)==0) {
            if ($event->update()) {
                $_SESSION['form_data'] = [];
                redirect_to(createLink("/admin/events.php"));
            } else {
                $errors["db_error"] = "Chyba při ukládání události do databáze.";
            }
        }
        redirect_to(createLink("/admin/events/update.php?".http_build_query(["id" => $event_id])));
    }elseif ($_SERVER["REQUEST_METHOD"] === "GET"){
        $_SESSION["redirectto"]=createLink("/admin/events/update.php?".http_build_query(["id" => $event_id]));
        $registrations = Registration::findEventRegistrationsByEventId($event_id);
    }

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../../components/head.php" ?>
    <link rel="stylesheet" href="<?= createStylesLink("/forms.css") ?>">
</head>
<body id="admin-update-event-body">
<header>
    <?php include "../../components/navbar.php"; ?>
</header>
<div id="page-content">
    <div id="update-user-div">
        <form id="add-event-form" autocomplete="off" method="post" enctype="multipart/form-data">
            <div id="name-wrapper" class="form-wrapper">
                <label for="form-name">Název<span class="required"></span></label>
                <input id="form-name" type="text" name="name" placeholder="Název události" value="<?= htmlspecialchars($formData["name"] ?? $event->name ?? "") ?>" required>
                <span id="error-name" class="validation-error <?= isset($errors['name']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['name'] ?? '') ?>
            </span>
            </div>

            <div id="description-wrapper" class="form-wrapper">
                <label for="form-description">Popis</label>
                <textarea id="form-description" name="description" value="<?= htmlspecialchars($formData["description"] ?? $event->description ?? "")  ?>" placeholder="Podrobný popis události..."></textarea>
                <span id="error-description" class="validation-error <?= isset($errors['description']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['description'] ?? '') ?>
            </span>
            </div>

            <div id="location-wrapper" class="form-wrapper">
                <label for="form-location">Místo<span class="required"></span></label>
                <input id="form-location" type="text" name="location" value="<?= htmlspecialchars($formData["location"] ?? $event->location ?? "")  ?>" placeholder="Místo konání (např. adresa nebo 'Online')" required>
                <span id="error-location" class="validation-error <?= isset($errors['location']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['location'] ?? '') ?>
            </span>
            </div>

            <div id="price-wrapper" class="form-wrapper">
                <label for="form-price">Cena</label>
                <input id="form-price" type="text" placeholder="Cena události" name="price" value="<?= htmlspecialchars($formData["price"] ?? $event->price ?? "")  ?>">
                <span id="error-price" class="validation-error <?= isset($errors['price']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['price'] ?? '') ?>
            </span>
            </div>

            <div id="capacity-wrapper" class="form-wrapper">
                <label for="form-capacity">Kapacita<span class="required"></span></label>
                <input id="form-capacity" type="text" placeholder="Kapacita" name="capacity" required value="<?= htmlspecialchars($formData["capacity"] ?? $event->capacity ?? "")  ?>">
                <span id="error-capacity" class="validation-error <?= isset($errors['capacity']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['capacity'] ?? '') ?>
            </span>
            </div>

            <div id="start-datetime-wrapper" class="form-wrapper">
                <label for="form-start-datetime">Datum a čas zahájení<span class="required"></span></label>
                <input id="form-start-datetime" type="datetime-local" name="start_datetime" required value="<?= htmlspecialchars($formData["start_datetime"] ?? $event->start_datetime ?? "")  ?>">
                <span id="error-start-datetime" class="validation-error <?= isset($errors['start_datetime']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['start_datetime'] ?? '') ?>
            </span>
            </div>

            <div id="registration-deadline-wrapper" class="form-wrapper">
                <label for="form-registration-deadline">Datum a čas konce registrací<span class="required"></span></label>
                <input id="form-registration-deadline" type="datetime-local" name="registration_deadline" required value="<?= htmlspecialchars($formData["registration_deadline"] ?? $event->registration_deadline ?? "")  ?>">
                <span id="error-registration-deadline" class="validation-error <?= isset($errors['registration_deadline']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['registration_deadline'] ?? '') ?>
            </span>
            </div>

            <div id="image-wrapper" class="form-wrapper">
                <label for="form-image">Obrázek události (JPG, PNG)</label>
                <input id="form-image" type="file" name="image" accept="image/jpeg, image/png">
                <span id="error-image" class="validation-error <?= isset($errors['image']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['image'] ?? '') ?>
            </span>
            </div>

            <button type="submit">Uložit</button>
        </form>
    </div>
    <div id="registrations-div">
        <form action="/pridat-uzivatele.php" method="POST">
            <div id="add-registration-wrap">
                <input id="add-registration" name="nove_jmeno" placeholder="Jméno">
                <button id="add-registration-btn" type="submit">přidat</button>
            </div>
        </form>

        <p><u>Registrace</u></p>

        <div class="registration-wrap">
            <p>Hroudaa 1</p>
            <form class="delete-form" action="/cilova-stranka-pro-smazani.php" method="POST">
                <input type="hidden" name="uzivatel_ke_smazani" value="Hroudaa 1">

                <button class="btn-trash" type="submit" title="Smazat">
                    <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg" width="20">
                </button>
            </form>
        </div>

        <div class="registration-wrap">
            <p>Hroudaa 2</p>
            <form class="delete-form" action="/cilova-stranka-pro-smazani.php" method="POST">
                <input type="hidden" name="uzivatel_ke_smazani" value="Hroudaa 2">
                <button class="btn-trash" type="submit">
                    <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg" width="20">
                </button>
            </form>
        </div>

        <div class="registration-wrap">
            <p>Hroudaa 3</p>
            <form class="delete-form" action="/cilova-stranka-pro-smazani.php" method="POST">
                <input type="hidden" name="uzivatel_ke_smazani" value="Hroudaa 3">
                <button class="btn-trash" type="submit">
                    <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg" width="20">
                </button>
            </form>
        </div>

    </div>
</div>
<script src="<?= createScriptLink("/validation/events.js") ?>"></script>
</body>
</html>
