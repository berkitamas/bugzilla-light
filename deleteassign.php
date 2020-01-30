<?php
define("PROTECT", true);
require_once "include/init.php";


if (!empty($_GET["type"]) && !empty($_GET["project"]) && !empty($_GET["user"]) && $_GET["type"] === "project") {
    if (!is_admin()) {
        header("Location: ./");
        die;
    }
    $query = $db->prepare("SELECT COUNT(*) FROM projektkezeles WHERE `projekt.nev`=:project AND `felhasznalo.felhasznalonev`=:user");
    $query->execute([":project" => $_GET["project"], ":user" => $_GET["user"]]);
    if ($query->fetch()[0]) {
        if (!empty($_POST["submit"]) && $_POST["submit"] = "Hozzárendelés törlése") {
            $query = $db->prepare("DELETE FROM projektkezeles WHERE `projekt.nev`=:project AND `felhasznalo.felhasznalonev`=:user");
            $query->execute([":project" => $_GET["project"], ":user" => $_GET["user"]]);
            header("Location: projectdetails.php?project=" . urlencode($_GET["project"]));
            die;
        }
        breadcrumb_add_level("Projektek", $root_path . 'projects.php');
        breadcrumb_add_level(htmlspecialchars($_GET["project"]), $root_path . 'projectdetails.php?project=' . htmlspecialchars(urlencode($_GET["project"])));
        breadcrumb_add_level("Hozzárendelés törlése", $root_path . 'deleteassgin.php?type=project&project=' . htmlspecialchars(urlencode($_GET["project"])) . "&user" . htmlspecialchars(urlencode($_GET["user"])));
        include("layout/header.php");
        include("layout/nav.php");
        ?>
        <div class="row justify-content-center">
            <div class="card mt-3 col-7 p-0">
                <div class="card-header">Hozzárendelés törlése</div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group mb-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="projectname">Projekt</label>
                                </div>
                                <input type="text" class="form-control" id="projectname" value="<?=htmlspecialchars($_GET["project"])?>"
                                       aria-label="Projekt neve" disabled>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="username">@</label>
                                </div>
                                <input type="text" class="form-control" id="username" value="<?=htmlspecialchars($_GET["user"])?>"
                                       aria-label="Felhasználónév" disabled>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-danger" name="submit" value="Hozzárendelés törlése">
                    </form>
                </div>
            </div>
        </div>
        <?php
        include("layout/footer.php");
    } else {
        http_response_code(404);
        include("layout/header.php");
        include("layout/nav.php");
        echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
        include("layout/footer.php");
    }
} elseif (!empty($_GET["type"]) && !empty($_GET["project"]) && !empty($_GET["category"]) && !empty($_GET["user"]) && $_GET["type"] === "category") {
    if (!is_admin($_GET["project"])) {
        header("Location: ./");
        die;
    }
    $query = $db->prepare("SELECT COUNT(*) FROM kategoriakezeles WHERE `projekt.nev`=:project AND `kategoria.nev`=:category AND `felhasznalo.felhasznalonev`=:user");
    $query->execute([":project" => $_GET["project"], ":category" => $_GET["category"], ":user" => $_GET["user"]]);
    if ($query->fetch()[0]) {
        if (!empty($_POST["submit"]) && $_POST["submit"] = "Hozzárendelés törlése") {
            $query = $db->prepare("DELETE FROM kategoriakezeles WHERE `projekt.nev`=:project AND `kategoria.nev`=:category AND `felhasznalo.felhasznalonev`=:user");
            $query->execute([":project" => $_GET["project"], ":category" => $_GET["category"], ":user" => $_GET["user"]]);
            header("Location: categorydetails.php?project=" . urlencode($_GET["project"]) . "&category=" . urlencode($_GET["category"]));
            die;
        }
        breadcrumb_add_level("Projektek", $root_path . "projects.php");
        breadcrumb_add_level(htmlspecialchars($_GET["project"]), $root_path . "projectdetails.php?project=" . htmlspecialchars(urlencode($_GET["project"])));
        breadcrumb_add_level(htmlspecialchars($_GET["category"]), $root_path . "categorydetails.php?project=" . htmlspecialchars(urlencode($_GET["project"])) . "&category=" . htmlspecialchars(urlencode($_GET["category"])));
        breadcrumb_add_level("Hozzárendelés törlése", $root_path . "deleteassign.php?type=category&project=" . htmlspecialchars(urlencode($_GET["project"])) . "&category=" . htmlspecialchars(urlencode($_GET["category"])) . "&user=" . htmlspecialchars(urlencode($_GET["user"])));
        include("layout/header.php");
        include("layout/nav.php");
        ?>
        <div class="row justify-content-center">
            <div class="card m-3 col-lg-7 p-0">
                <div class="card-header">Hozzárendelés törlése</div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group mb-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="projectname">Projekt</label>
                                </div>
                                <input type="text" class="form-control" id="projectname" value="<?=htmlspecialchars($_GET["project"])?>"
                                       aria-label="Projekt neve" disabled>
                            </div>
                            <div class="input-group mt-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="categoryname">Kategória</label>
                                </div>
                                <input type="text" class="form-control" id="categoryname" value="<?=htmlspecialchars($_GET["category"])?>"
                                       aria-label="Kategória neve" disabled>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="username">@</label>
                                </div>
                                <input type="text" class="form-control" id="username" value="<?=htmlspecialchars($_GET["user"])?>" aria-label="Felhasználónév"
                                       disabled>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-danger" name="submit" value="Hozzárendelés törlése">
                    </form>
                </div>
            </div>
        </div>
        <?php
        include("layout/footer.php");
    } else {
        http_response_code(404);
        include("layout/header.php");
        include("layout/nav.php");
        echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
        include("layout/footer.php");
    }
} else {
    header("Location: /");
}
?>
