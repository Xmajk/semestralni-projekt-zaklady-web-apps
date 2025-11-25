<?php
    use components\objects;
    use components\objects\Event;
    use components\objects\Registration;
    require_once __DIR__ . "/../../components/objects/User.php";
    require_once __DIR__ . "/../../components/objects/Event.php";
    require_once __DIR__ . "/../../components/objects/Registration.php";
    require_once __DIR__ . "/../../components/utils/links.php";
    require_once __DIR__ . "/../../components/check_auth.php";




    $event_id=$_GET['id'];
    $event_id=intval($event_id);
    $event = Event::getById($event_id);
    $registrations = Registration::findEventRegistrationsByEventId($event_id);

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
    </div>
    <div id="registrations-div">
        <p><u>Registrace</u></p>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
        <div class="registration-wrap">
            <p>Hroudaa</p>
            <a class="delete-ref" href="">
                <img src="https://www.reshot.com/preview-assets/icons/2Z6MPSCH3V/trash-bin-2Z6MPSCH3V.svg">
            </a>
        </div>
    </div>
</div>
<script src="<?= createScriptLink("/validation/events.js") ?>"></script>
</body>
</html>
