<?php
define("PROTECT", true);
require_once "include/init.php";
breadcrumb_add_level("Profil", $root_path . 'profile.php');

if (!logged_in()) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    die;
}

$errors = [];
if (!empty($_POST["submit"]) && $_POST["submit"] === "Módosítás") {
    if (!empty($_POST["tab"]) && $_POST["tab"] === "details") {
        $email = (!empty($_POST["email"])) ? trim($_POST["email"]) : "";
        $phone = (!empty($_POST["phone"])) ? trim($_POST["phone"]) : "";
        if ($email === "") {
            array_push($errors, "E-mail cím megadása kötelező!");
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, "E-mail cím formátuma nem megfelelő!");
            } else {
                if (strlen($email) > 32) {
                    array_push($errors, "Az e-mail cím maximum 32 karakterből állhat!");
                }
            }
        }
        if ($phone !== "") {
            if (strlen($phone) > 16) {
                array_push($errors, "A telefonszám maximum 16 karakterből állhat!");
            }
            if (strlen($phone) < 6) {
                array_push($errors, "A telefonszám minimum 6 karakterből kell, hogy álljon!");
            }
        }
        if (!count($errors)) {
            $query = $db->prepare("UPDATE `felhasznalo` SET email = :email, telefon = :phone WHERE felhasznalonev = :user");
            $query->execute([
                ":user" => get_user()["felhasznalonev"],
                ":email" => $email,
                ":phone" => (!empty($phone))?$phone:null
            ]);
            set_user_props("email", $email);
            set_user_props("telefon", $phone);
            session_msg_place("Sikeresen módosította az adatokat!");
        }
    }
    elseif (!empty($_POST["tab"]) && $_POST["tab"] === "password") {
        $oldpass = (!empty($_POST["oldpassword"])) ? trim($_POST["oldpassword"]) : "";
        $pass = (!empty($_POST["password"])) ? trim($_POST["password"]) : "";
        $pass_again = (!empty($_POST["password_again"])) ? trim($_POST["password_again"]) : "";
        if ($oldpass === "") {
            array_push($errors, "Jelenlegi jelszó megadása kötelező!");
        }
        if ($pass === "") {
            array_push($errors, "Jelszó megadása kötelező!");
        } else {
            if (strlen($pass) < 6) {
                array_push($errors, "A jelszó minimum 6 karakterből kell, hogy álljon!");
            } else {
                if ($pass !== $pass_again) {
                    array_push($errors, "A megadott jelszóknak egyeznie kell!");
                }
            }
        }
        if (!count($errors)) {
            $query = $db->prepare("SELECT * FROM `felhasznalo` WHERE felhasznalonev = :user");
            $query->execute([
                ":user" => get_user()["felhasznalonev"]
            ]);
            $result = $query->fetch();
            if (!empty($result) && password_verify($oldpass, $result["jelszo"])) {
                $query = $db->prepare("UPDATE `felhasznalo` SET jelszo = :pass WHERE felhasznalonev = :user");
                $query->execute([
                    ":user" => get_user()["felhasznalonev"],
                    ":pass" => password_hash($pass, PASSWORD_DEFAULT)
                ]);
                session_msg_place("Sikeresen módosította a jelszót!");
            } else {
                array_push($errors, "Jelszó nem megfelelő!");
            }
        }
    }
}

include("layout/header.php");
include("layout/nav.php");
?>
<?php
if (!empty($errors)) {
    echo "<div class=\"alert alert-danger\" role=\"alert\">
            A művelet nem sikerült!<br />Kérem a folytatáshoz javítsa ki az itt felsorolt hibákat:
            <ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul></div>";
}
?>
<?=(session_msg_exists())?"<div class=\"alert alert-success border-0 rounded-0\" role=\"alert\">" . session_msg_take() . "</div>":""; ?>
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Általános adatok</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">Jelszó megváltoztatása</a>
    </li>
</ul>
<div class="tab-content bg-white border-left border-bottom border-right p-3 rounded-bottom" id="myTabContent">
    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
        <form method="post">
            <input type="hidden" name="tab" value="details">
            <div class="input-group mb-4">
                <label for="username">Felhasználónév</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="at-symbol">@</span>
                    </div>
                    <input type="text" class="form-control" id="username" placeholder="Felhasználónév" aria-label="Felhasználónév" value="<?=htmlspecialchars(get_user()["felhasznalonev"])?>" aria-describedby="at-symbol" disabled>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="password-again">E-mail cím
                <input type="text" class="form-control" name="email" id="email" placeholder="E-mail cím" aria-label="E-mail cím" value="<?=(!empty($_POST["email"]))?htmlspecialchars($_POST["email"]):get_user()["email"]?>">
            </div>
            <div class="form-group mb-4">
                <label for="phone">Telefonszám</label>
                <input type="text" class="form-control" name="phone" id="phone" placeholder="Telefonszám" aria-label="Telefonszám" value="<?=(!empty($_POST["phone"]))?htmlspecialchars($_POST["phone"]):get_user()["telefon"]?>">
            </div>
            <input type="submit" class="btn btn-primary" name="submit" value="Módosítás">
        </form>
    </div>
    <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
        <form method="post">
            <input type="hidden" name="tab" value="password">
            <div class="form-group mb-4">
                <label for="oldpassword">Régi jelszó</label>
                <input type="password" class="form-control" name="oldpassword" id="oldpassword" placeholder="Régi jelszó" aria-label="Régi jelszó">
            </div>
            <div class="form-group mb-2">
                <label for="password">Új jelszó</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Új jelszó" aria-label="Új jelszó">
            </div>
            <div class="form-group mb-4">
                <label for="password-again">Új jelszó újra</label>
                <input type="password" class="form-control" name="password_again" id="password-again" placeholder="Új jelszó újra" aria-label="Új jelszó újra">
            </div>
            <input type="submit" class="btn btn-primary" name="submit" value="Módosítás">
        </form>
    </div>
</div>
<?php
include("layout/footer.php");
