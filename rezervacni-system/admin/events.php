<?php
use components\objects\Event;
use components\objects\Registration;
use components\objects\User;
require_once __DIR__ . "/../components/objects/Event.php";
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/objects/Registration.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";
require_once __DIR__ . "/../components/utils/image_helper.php";

session_start();
check_auth_admin();

CONST DEFAULT_IMAGE_NAME = "default.jpg";

function createEventUpdateLink ($event_id):string {
    return createLink("/admin/events/update.php?".http_build_query(array("id" => $event_id)));
}
function createEventDeleteLink ($event_id):string {
    return createLink("/admin/events/delete.php?".http_build_query(array("id" => $event_id)));
}

$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
if($_SERVER["REQUEST_METHOD"] == "POST"){
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

    //datavalidation
    if(!isset($formData["name"])){
        $errors["name"] = "Toto pole je povinné";
    }

    if(isset($formData["description"])){
        if(strlen($formData["description"]) > 1000){
            $errors["description"] = "Popis nesmí mít více jak 1000 znaků";
        }
    }

    if(!isset($formData["location"])){
        $errors["location"] = "Toto pole je povinné";
    }elseif(strlen($formData["location"]) > 100){
        $errors["location"] = "Místo nesmí mít více jak 100 znaků";
    }

    if(isset($formData["price"])){
        if(trim(strtolower($formData["price"]))=="zdarma"){
            $formData["price"]=0;
        }
        if(!is_numeric($formData["price"])){
            $errors["price"] = "Cenam musí být číslo";
        }else{
            $formData["price"] = intval($formData["price"]);
        }
    }else{
        $formData["price"] = 0;
    }

    if(!isset($formData["capacity"])){
        $errors["capacity"] = "Toto pole je povinné.";
    }
    elseif(!is_numeric($formData["capacity"])){
        $errors["capacity"] = "Kapacita musí být číslo";
    }

    if(!isset($formData["start_datetime"])){
        $errors["start_datetime"] = "Toto pole je povinné";
    }

    if(!isset($formData["registration_deadline"])){
        $errors["registration_deadline"] = "Toto pole je povinné";
    }

    $_SESSION['form_errors'] = $errors;

    $image_db_filename = DEFAULT_IMAGE_NAME;
    if (isset($formData['image']) && $formData['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $source_path = $file['tmp_name'];

        // Zjištění typu obrázku
        $image_type = exif_imagetype($source_path);
        $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];

        if (!in_array($image_type, $allowed_types)) {
            $errors["image"] = "Neplatný formát obrázku. Povoleny jsou pouze JPEG a PNG.";
        } else {
            //uuid obrázku
            $base_filename = "event_" . uniqid();
            $extension = ($image_type == IMAGETYPE_JPEG) ? '.jpg' : '.png';
            $image_db_filename = $base_filename . $extension;

            $dir_large = __DIR__ . "/../public/imgs/events/large/";
            $dir_thumb = __DIR__ . "/../public/imgs/events/thumb/";
            if (!is_dir($dir_large)) @mkdir($dir_large, 0777, true);
            if (!is_dir($dir_thumb)) @mkdir($dir_thumb, 0777, true);

            $success_large = \components\utils\processUploadedImage($source_path, $image_type, $dir_large, $image_db_filename, 1200);
            $success_thumb = \components\utils\processUploadedImage($source_path, $image_type, $dir_thumb, $image_db_filename, 300);

            if (!$success_large || !$success_thumb) {
                $errors["general"] = "Chyba při ukládání obrázku. Zkontrolujte práva zápisu do adresáře 'public/imgs/events/'.";
                $image_db_filename = DEFAULT_IMAGE_NAME; // Reset
            }
        }
    }
    $_SESSION['form_errors'] = $errors;
    if (count($errors)==0) {
        $newEvent = new Event();
        $newEvent->name = $formData["name"];
        $newEvent->description = $formData["description"];
        $newEvent->location = $formData["location"];
        $newEvent->start_datetime = $formData["start_datetime"];
        $newEvent->registration_deadline = $formData["registration_deadline"];
        $newEvent->image_filename = $image_db_filename;
        $newEvent->capacity = $formData["capacity"];
        $newEvent->price = $formData["price"];

        if ($newEvent->insert()) {
            redirect_to(createLink("/admin/events.php"));
            exit;
        } else {
            $error = "Chyba při ukládání události do databáze.";
        }
    }

    redirect_to(createLink("/admin/events.php"));
}

$events = Event::getAllOrdered();

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <link rel="stylesheet" href="<?= createStylesLink("/forms.css") ?>">
</head>
<body id="admin-users-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">
    <button id="show-form-btn" class="expand-btn">Přidat událost</button>
    <form id="add-event-form" autocomplete="off" method="post" enctype="multipart/form-data">
        <div id="name-wrapper" class="form-wrapper">
            <label for="form-name">Název<span class="required"></span></label>
            <input id="form-name" type="text" name="name" placeholder="Název události" value="<?= $formData["name"] ?? "" ?>" required>
            <span id="error-name" class="validation-error <?= isset($errors['name']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['name'] ?? '') ?>
            </span>
        </div>

        <div id="description-wrapper" class="form-wrapper">
            <label for="form-description">Popis</label>
            <textarea id="form-description" name="description" value="<?= $formData["description"] ?? "" ?>" placeholder="Podrobný popis události..."></textarea>
            <span id="error-description" class="validation-error <?= isset($errors['description']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['description'] ?? '') ?>
            </span>
        </div>

        <div id="location-wrapper" class="form-wrapper">
            <label for="form-location">Místo<span class="required"></span></label>
            <input id="form-location" type="text" name="location" value="<?= $formData["location"] ?? "" ?>" placeholder="Místo konání (např. adresa nebo 'Online')" required>
            <span id="error-location" class="validation-error <?= isset($errors['location']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['location'] ?? '') ?>
            </span>
        </div>

        <div id="price-wrapper" class="form-wrapper">
            <label for="form-price">Cena</label>
            <input id="form-price" type="text" placeholder="Cena události" name="price" value="<?= $formData["price"] ?? "" ?>">
            <span id="error-price" class="validation-error <?= isset($errors['price']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['price'] ?? '') ?>
            </span>
        </div>

        <div id="capacity-wrapper" class="form-wrapper">
            <label for="form-capacity">Kapacita<span class="required"></span></label>
            <input id="form-capacity" type="text" placeholder="Kapacita" name="capacity" required value="<?= $formData["capacity"] ?? "" ?>">
            <span id="error-capacity" class="validation-error <?= isset($errors['capacity']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['capacity'] ?? '') ?>
            </span>
        </div>

        <div id="start-datetime-wrapper" class="form-wrapper">
            <label for="form-start-datetime">Datum a čas zahájení<span class="required"></span></label>
            <input id="form-start-datetime" type="datetime-local" name="start_datetime" required value="<?= $formData["start_datetime"] ?? "" ?>">
            <span id="error-start-datetime" class="validation-error <?= isset($errors['start_datetime']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['start_datetime'] ?? '') ?>
            </span>
        </div>

        <div id="registration-deadline-wrapper" class="form-wrapper">
            <label for="form-registration-deadline">Datum a čas konce registrací<span class="required"></span></label>
            <input id="form-registration-deadline" type="datetime-local" name="registration_deadline" required value="<?= $formData["registration_deadline"] ?? "" ?>">
            <span id="error-registration-deadline" class="validation-error <?= isset($errors['registration_deadline']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['registration_deadline'] ?? '') ?>
            </span>
        </div>

        <div id="image-wrapper" class="form-wrapper">
            <label for="form-image">Obrázek události (JPG, PNG)</label>
            <input id="form-image" type="file" name="image" accept="image/jpeg, image/png" value="<?= $formData["image"] ?? "" ?>">
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

    <div class="table-wrap" data-density="comfy">
        <table class="table">
            <thead>
            <tr>
                <th classs="sortable" aria-sort="descending">Název události</th>
                <th>Datum registrace do</th>
                <th>Registrace</th>
                <th>Upravit</th>
                <th>Smazat</th>
                <th>Stáhnout</th>
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
                                echo htmlspecialchars($event->registration_deadline);
                            }
                        ?>
                    </td>
                    <td><?= htmlspecialchars(strval(Registration::numberOfRegistrationsByEventId($event->id))."/".strval($event->capacity)) ?></td>
                    <td><a href="<?= createEventUpdateLink($event->id) ?>">upravit</a></td>
                    <td><a href="<?= createEventDeleteLink($event->id) ?>">vymazat</a></td>
                    <td><a class="download-link" href="events.php"><img src="<?= createPublicLink("/icons/dwnload.svg") ?>"></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
<script src="<?= createScriptLink("/validation/events.js") ?>"></script>
</body>
</html>