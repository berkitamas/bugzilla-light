<?php
define("PROTECT", true);
require_once "include/init.php";
breadcrumb_add_level("Projektek", $root_path . 'projects.php');
breadcrumb_add_level("Új projekt", $root_path . 'projectnew.php');

if (!is_admin()) {
    header("Location: ./");
    die;
}

$errors = [];
if (!empty($_POST["submit"]) && $_POST["submit"] === "Létrehozás") {
    $projectname = (!empty($_POST["name"])) ? trim($_POST["name"]) : "";
    if ($projectname === "") {
        array_push($errors, "Projektnév megadása kötelező!");
    } else {
        if (strlen($projectname) > 32) {
            array_push($errors, "A projektnév maximum 32 karakterből állhat!");
        }
        if (strlen($projectname) < 3) {
            array_push($errors, "A projektnév minimum 3 karakterből kell, hogy álljon!");
        }
    }
    if (!count($errors)) {
        $query = $db->prepare("SELECT * FROM `projekt` WHERE nev = :name");
        $query->execute([
            ":name" => $projectname
        ]);
        if (empty($query->fetch())) {
            $query = $db->prepare("INSERT INTO `projekt` (nev) VALUES (:name)");
            $query->execute([
                ":name" => $projectname
            ]);
            header("Location: projects.php");
            die;
        } else {
            array_push($errors, "A projekt már létezik!");
        }
    }
}

include("layout/header.php");
include("layout/nav.php");
?>
<div class="row justify-content-center">
    <div class="card m-3 col-lg-7 p-0">
        <div class="card-header">Új projekt</div>
        <?php
        if (!empty($errors)) {
            echo "<div class=\"alert alert-danger border-0 rounded-0\" role=\"alert\">
            A művelet nem sikerült!<br />Kérem a folytatáshoz javítsa ki az itt felsorolt hibákat:
            <ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul></div>";
        }
        ?>
        <div class="card-body">
            <form method="post">
                <div class="form-group mb-4">
                    <label for="projectname">Projekt neve</label>
                    <input type="text" class="form-control" name="name" id="projectname" placeholder="Projekt neve" aria-label="Projekt neve" <?=(!empty($_POST["name"]))?"value=\"" . htmlspecialchars($_POST["name"]) . "\"":""?>>
                </div>
                <input type="submit" class="btn btn-primary" name="submit" value="Létrehozás">
            </form>
        </div>
    </div>
</div>
<?php
include("layout/footer.php");