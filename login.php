<?php
define("PROTECT", true);
require_once "include/init.php";
breadcrumb_add_level("Bejelentkezés", $root_path . 'login.php');

if (logged_in()) {
    header("Location: ./");
    die;
}

$errors = [];
if (!empty($_POST["submit"]) && $_POST["submit"] === "Bejelentkezés") {
    $user = (!empty($_POST["username"])) ? trim($_POST["username"]) : "";
    $pass = (!empty($_POST["password"])) ? trim($_POST["password"]) : "";
    if ($user === "") {
        array_push($errors, "Felhasználónév megadása kötelező!");
    }
    if ($pass === "") {
        array_push($errors, "Jelszó megadása kötelező!");
    }

    if (!count($errors)) {
        $query = $db->prepare("SELECT * FROM `felhasznalo` WHERE felhasznalonev = :user");
        $query->execute([
                ":user" => $user
        ]);
        $result = $query->fetch();
        if (!empty($result) && password_verify($pass, $result["jelszo"])) {
            $user = $result;
            $user["super"] = ($user["felhasznalonev"] === "admin");
            $query = $db->prepare("SELECT `projekt.nev` FROM projektkezeles WHERE `felhasznalo.felhasznalonev` = :user");
            $query->execute([
                    ":user" => $user["felhasznalonev"]
            ]);
            $user["projektek"] = $query->fetchAll(PDO::FETCH_COLUMN, "projekt.nev");
            $query = $db->prepare("SELECT `projekt.nev`, `kategoria.nev` FROM kategoriakezeles WHERE `felhasznalo.felhasznalonev` = :user");
            $query->execute([
                ":user" => $user["felhasznalonev"]
            ]);
            $user["kategoriak"] = [];
            while ( ($item = $query->fetch()) ) {
                if(empty($user["kategoriak"][$item["projekt.nev"]])) $user["kategoriak"][$item["projekt.nev"]] = [];
                array_push($user["kategoriak"][$item["projekt.nev"]], $item["kategoria.nev"]);
            }
            $_SESSION["user"] = $user;
            header("Location: " . (!empty($_GET["redirect"])?$_GET["redirect"]:"./"));
            die;
        } else {
            array_push($errors, "Felhasználónév vagy jelszó nem megfelelő!");
        }
    }
}

include("layout/header.php");
include("layout/nav.php");
?>
<div class="row justify-content-center">
    <div class="card m-3 col-lg-7 p-0">
        <div class="card-header">Bejelentkezés</div>
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
        <?=(session_msg_exists())?"<div class=\"alert alert-success border-0 rounded-0\" role=\"alert\">" . session_msg_take() . "</div>":""; ?>
        <div class="card-body">
            <form method="post">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="at-symbol">@</span>
                    </div>
                    <input type="text" class="form-control" name="username" placeholder="Felhasználónév" aria-label="Felhasználónév" aria-describedby="at-symbol">
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="key-symbol"><i class="fas fa-key"></i></span>
                    </div>
                    <input type="password" class="form-control" name="password" placeholder="Jelszó" aria-label="Jelszó" aria-describedby="key-symbol">
                </div>
                <button type="submit" class="btn btn-primary" name="submit" value="Bejelentkezés">Bejelentkezés</button>
                <a href="register.php" class="ml-3">Regisztráció</a>
            </form>
        </div>
    </div>
</div>
<?php
include("layout/footer.php");