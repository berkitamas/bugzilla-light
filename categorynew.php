<?php
define("PROTECT", true);
require_once "include/init.php";
if (empty($_GET["project"])) {
    header("Location: ./");
    die;
}

if (!is_admin($_GET["project"])) {
    header("Location: ./");
    die;
}

$query = $db->prepare("SELECT * FROM `projekt` WHERE nev=:name");
$query->execute([":name" => $_GET["project"]]);
$project = $query->fetch();
if (!empty($project)) {
    $errors = [];
    if (!empty($_POST["submit"]) && $_POST["submit"] == "Létrehozás") {
        $categoryname = (!empty($_POST["name"])) ? trim($_POST["name"]) : "";
        if ($categoryname === "") {
            array_push($errors, "Kategórianév megadása kötelező!");
        } else {
            if (strlen($categoryname) > 32) {
                array_push($errors, "A kategórianév maximum 32 karakterből állhat!");
            }
            if (strlen($categoryname) < 3) {
                array_push($errors, "A kategórianév minimum 3 karakterből kell, hogy álljon!");
            }
        }
        if (!count($errors)) {
            $query = $db->prepare("SELECT * FROM `kategoria` WHERE `projekt.nev` = :project AND nev = :name");
            $query->execute([
                ":project" => $project["nev"],
                ":name" => $categoryname
            ]);
            if (empty($query->fetch())) {
                $query = $db->prepare("INSERT INTO `kategoria` (`projekt.nev`, nev) VALUES (:project, :name)");
                $query->execute([
                    ":project" => $project["nev"],
                    ":name" => $categoryname
                ]);
                header("Location: projectdetails.php?project=" . urlencode($_GET["project"]));
                die;
            } else {
                array_push($errors, "A kategória már létezik!");
            }
        }
    }
}
breadcrumb_add_level("Projektek", $root_path . 'projects.php');
breadcrumb_add_level(htmlspecialchars($_GET["project"]), $root_path . 'projectdetails.php?project=' . htmlspecialchars(urlencode($_GET["project"])));
breadcrumb_add_level("Új kategória", $root_path . "categorynew.php?project=" . htmlspecialchars(urlencode($_GET["project"])));
include("layout/header.php");
include("layout/nav.php");
if (!empty($project)) {
    ?>
    <div class="row justify-content-center">
        <div class="card m-3 col-lg-7 p-0">
            <div class="card-header">Új kategória</div>
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
                        <input type="text" class="form-control" id="projectname" value="<?=htmlspecialchars($project["nev"])?>"
                               placeholder="Projekt neve" aria-label="Projekt neve" disabled>
                    </div>
                    <div class="form-group mb-4">
                        <label for="categoryname">Kategória neve</label>
                        <input type="text" class="form-control" name="name" id="categoryname"
                               placeholder="Kategória neve" aria-label="Kategória neve">
                    </div>
                    <input type="submit" class="btn btn-primary" name="submit" value="Létrehozás">
                </form>
            </div>
        </div>
    </div>
    <?php
} else {
    http_response_code(404);
    echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
}
include("layout/footer.php");