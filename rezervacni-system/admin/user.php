<?php
use components\objects\User;
require_once __DIR__ . "/../components/objects/User.php";
require_once __DIR__ . "/../components/utils/crypto.php";
require_once __DIR__ . "/../components/utils/links.php";
require_once __DIR__ . "/../components/check_auth.php";
require_once __DIR__ . "/../components/breadcrumb.php";

session_start();
check_auth_admin();

$errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
$error = NULL;
$user_id = htmlspecialchars($_GET["id"]) ?? null;
$is_success = (htmlspecialchars($_GET["success"]??'0') ?? '0')=='1';

if (!isset($user_id)) redirect_to(createLink("/admin/users.php"));
if(!is_numeric($user_id)) redirect_to(create_error_link("uživatel nenalezen"));
$user = User::getUserById($user_id);
if (!isset($user)) redirect_to(create_error_link("Uživatel nenalezen"));

function create_user_link(){
    global $user_id;
    return createLink("/admin/user.php?".http_build_query(array('id'=>$user_id),'',"&amp;"));
}
function create_user_updated_link(){
    global $user_id;
    return createLink("/admin/user.php?".http_build_query(array('id'=>$user_id,'success'=>'1'),'',"&amp;"));
}

function reload(){
    global $form_data,$errors;
    $_SESSION["form_errors"] = $errors;
    $_SESSION["form_data"] = $form_data;
    redirect_to(create_user_link());
}

if($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']))){
    User::deleteById((int)$user_id);
    redirect_to(createLink("/admin/users.php"));
}
else if($_SERVER["REQUEST_METHOD"] == "POST"){
    $form_data = array(
            "firstname" => trim($_POST["firstname"]??''),
        "lastname" => trim($_POST["lastname"]??''),
        "email" => trim($_POST["email"]??''),
        "password" => trim($_POST["password"]??''),
        "bdate" => trim($_POST["bdate"]??''),
        "is_admin" => trim($_POST["is_admin"]??''),
    );

    $user->firstname = $form_data["firstname"];
    $user->lastname = $form_data["lastname"];
    $user->email = $form_data["email"];
    $user->bdate = $form_data["bdate"];
    $user->is_admin = $form_data["is_admin"];
    if(!empty($form_data["password"])){
        if(strlen($form_data["password"]) < 6){
            $errors["password"] = "heslo musí být dlouhé minimálně 6 znaků";
            reload();
        }
        $user->password=hashSHA256($form_data["password"]);
    }

    $errors = array();
    if(!User::validation($user,$errors)) reload();

    if($user->update()){
        redirect_to(create_user_updated_link());
    }else{
        redirect_to(create_error_link("Chyba při aktualizaci dat uživatele. Kontaktujte správce"));
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?php include __DIR__ . "/../components/head.php" ?>
    <link rel="stylesheet" href="<?= createStylesLink("/forms.css") ?>">
    <script src="<?= createScriptLink("/validation/users.js") ?>"></script>
    <link rel="stylesheet" href="<?= createStylesLink("/toogleswitch.css") ?>">
</head>
<body id="admin-user-body">
<header>
    <?php include "../components/navbar.php"; ?>
</header>
<div id="page-content">

    <?= generateBreadcrumbs(["Home","Admin","Admin-users","Admin-users-update"]) ?>

    <form id="add-user-form" autocomplete="off" method="post">
        <span id="form-error" class="error">
            <?php
            if (isset($errors['general'])):
                echo htmlspecialchars($errors['general']);
            endif;
            ?>
        </span>

        <div id="username-wrapper" class="form-wrapper">
            <label for="form-username">Uživatelské jméno<span class="required"></span></label>
            <input id="form-username" type="text" name="username" placeholder="Uživatelské jméno" required autocomplete="off" aria-describedby="error-username"
                   value="<?= htmlspecialchars($user->username) ?>" disabled>
        </div>

        <div id="email-wrapper" class="form-wrapper">
            <label for="form-email">Email<span class="required"></span></label>
            <input id="form-email" type="email" name="email" placeholder="E-mail" required aria-describedby="error-email"
                   value="<?= htmlspecialchars($form_data->email ?? $user->email ?? '') ?>">
            <span id="error-email" class="validation-error <?= isset($errors['email']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['email'] ?? '') ?>
            </span>
        </div>

        <div id="names-wrapper" class="form-wrapper">
            <div id="firstname-wrapper">
                <label for="form-firstname">Jméno<span class="required"></span></label>
                <input id="form-firstname" type="text" name="firstname" placeholder="Jméno" required aria-describedby="error-firstname"
                       value="<?= htmlspecialchars( $form_data->firstname ??$user->firstname ?? '') ?>">
                <span id="error-firstname" class="validation-error <?= isset($errors['firstname']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($errors['firstname'] ?? '') ?>
                </span>
            </div>
            <div id="lastname-wrapper">
                <label for="form-lastname">Příjmení<span class="required"></span></label>
                <input id="form-lastname" type="text" name="lastname" placeholder="Příjmení" required aria-describedby="error-lastname"
                       value="<?= htmlspecialchars($formData->lastname ?? $user->lastname ?? '') ?>">
                <span id="error-lastname" class="validation-error <?= isset($errors['lastname']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($errors['lastname'] ?? '') ?>
                </span>
            </div>
        </div>

        <div id="bdate-wrapper">
            <label for="form-bdate">Datum narození<span class="required"></span></label>
            <input id="form-bdate" type="date" name="bdate" required aria-describedby="error-bdate"
                   value="<?= htmlspecialchars($form_data->bdate ?? $user->bdate ?? '') ?>">
            <span id="error-bdate" class="validation-error <?= isset($errors['bdate']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['bdate'] ?? '') ?>
            </span>
        </div>

        <div id="password-wrapper" class="form-wrapper">
            <label for="form-password">Heslo</label>
            <input id="form-change-password" type="password" name="password" placeholder="Heslo" aria-describedby="error-password">
            <span id="error-password" class="validation-error <?= isset($errors['password']) ? 'active' : '' ?>">
                <?= htmlspecialchars($errors['password'] ?? '') ?>
            </span>
        </div>

        <div id="isadmin-wrapper" class="form-wrapper">
            <label for="form-isadmin">Admin práva</label>
            <div id="switch-wrapper" style="height: 34px;">
                <label class="switch" style="width: 60px;">
                    <input type="checkbox" id="form-isadmin" name="is_admin" value="1"
                            <?= ($form_data->is_admin ?? $user->is_admin ?? 0) ? 'checked' : '' ?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>

        <button type="submit">Uložit</button>
    </form>
</div>
</body>
</html>