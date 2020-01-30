<?php
define("PROTECT", true);
require_once "include/init.php";

breadcrumb_add_level("Regisztráció", $root_path . "register.php");

if (logged_in()) {
    header("Location: ./");
    die;
}

$errors = [];
if (!empty($_POST["submit"]) && $_POST["submit"] === "Regisztráció") {
    $user = (!empty($_POST["username"])) ? trim($_POST["username"]) : "";
    $pass = (!empty($_POST["password"])) ? trim($_POST["password"]) : "";
    $pass_again = (!empty($_POST["password_again"])) ? trim($_POST["password_again"]) : "";
    $email = (!empty($_POST["email"])) ? trim($_POST["email"]) : "";
    $phone = (!empty($_POST["phone"])) ? trim($_POST["phone"]) : "";
    if ($user === "") {
        array_push($errors, "Felhasználónév megadása kötelező!");
    } else {
        if (preg_match("#^[A-Za-z0-9\.\_\-]*$#", $user) === 0) {
            array_push($errors, "Felhasználónév formátuma nem megfelelő!");
        }
        if (strlen($user) > 32) {
            array_push($errors, "A felhasználónév maximum 32 karakterből állhat!");
        }
        if (strlen($user) < 3) {
            array_push($errors, "A felhasználónév minimum 3 karakterből kell, hogy álljon!");
        }
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
        $query = $db->prepare("SELECT COUNT(*) FROM `felhasznalo` WHERE felhasznalonev = :user OR email = :email OR ( NOT NULL AND telefon = :phone )");
        $query->execute([
            ":user" => $user,
            ":email" => $email,
            ":phone" => (!empty($phone))?$phone:null
        ]);
        if ($query->fetch()[0] == "0") {
            $query = $db->prepare("INSERT INTO `felhasznalo` (felhasznalonev, jelszo, email, telefon) VALUES (:user, :pass, :email, :phone)");
            $query->execute([
                ":user" => $user,
                ":pass" => password_hash($pass, PASSWORD_DEFAULT),
                ":email" => $email,
                ":phone" => (!empty($phone))?$phone:null
            ]);
            session_msg_place("Sikeres regisztráció! Most már bejelentkezhet!");
            header("Location: login.php");
            die;
        } else {
            array_push($errors, "Felhasználó már létezik!");
        }
    }
}
include("layout/header.php");
include("layout/nav.php");
?>
<div class="row justify-content-center">
    <div class="card m-3 col-lg-7 p-0">
        <div class="card-header">Regiszráció</div>
        <?php
        if (!empty($errors)) {
            echo "<div class=\"alert alert-danger border-0 rounded-0\" role=\"alert\">
            A művelet nem sikerült!<br />Kérem a folytatáshoz javítsa ki az itt felsorolt hibákat:
            <ul>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul></div>";
        }
        ?>
        <div class="card-body">
            <form method="post">
                <div class="input-group mb-4">
                    <label for="username">Felhasználónév</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="at-symbol">@</span>
                        </div>
                        <input type="text" class="form-control" name="username" id="username" placeholder="Felhasználónév" aria-label="Felhasználónév" aria-describedby="at-symbol" <?=(!empty($_POST["username"]))?"value=\"" . htmlspecialchars($_POST["username"]) . "\"":""?>>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label for="password">Jelszó</label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Jelszó" aria-label="Jelszó">
                </div>
                <div class="form-group mb-4">
                    <label for="password-again">Jelszó újra</label>
                    <input type="password" class="form-control" name="password_again" id="password-again" placeholder="Jelszó újra" aria-label="Jelszó újra">
                </div>
                <div class="form-group mb-3">
                    <label for="password-again">E-mail cím</label>
                    <input type="text" class="form-control" name="email" id="email" placeholder="E-mail cím" aria-label="E-mail cím" <?=(!empty($_POST["email"]))?"value=\"" . htmlspecialchars($_POST["email"]) . "\"":""?>>
                </div>
                <div class="form-group mb-4">
                    <label for="phone">Telefonszám</label>
                    <input type="text" class="form-control" name="phone" id="phone" placeholder="Telefonszám" aria-label="Telefonszám" <?=(!empty($_POST["phone"]))?"value=\"" . htmlspecialchars($_POST["phone"]) . "\"":""?>>
                </div>
                <input type="submit" class="btn btn-primary" name="submit" value="Regisztráció">
            </form>
        </div>
    </div>
</div>
<?php
include("layout/footer.php");
