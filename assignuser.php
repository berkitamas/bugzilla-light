<?php
define("PROTECT", true);
require_once "include/init.php";
if (!empty($_GET["type"]) && !empty($_GET["project"]) && $_GET["type"] === "project") {
    if (!is_admin()) {
        header("Location: ./");
        die;
    }
    $query = $db->prepare("SELECT COUNT(*) FROM projekt WHERE nev=:project");
    $query->execute([":project" => $_GET["project"]]);
    if ($query->fetch()[0]) {
        $query = $db->prepare("SELECT felhasznalonev FROM felhasznalo WHERE felhasznalonev NOT IN (SELECT `felhasznalo.felhasznalonev` FROM projektkezeles WHERE `projekt.nev`=:project) ORDER BY felhasznalonev");
        $query->execute([":project" => $_GET["project"]]);
        $users = $query->fetchAll(PDO::FETCH_COLUMN, "felhasznalonev");
        if (!empty($_POST["submit"]) && $_POST["submit"] == "Hozzárendelés" && !empty($_POST["user"]) && in_array($_POST["user"], $users)) {
            $query = $db->prepare("INSERT INTO projektkezeles (`projekt.nev`, `felhasznalo.felhasznalonev`) VALUES (:project, :user)");
            $query->execute([":project" => $_GET["project"], ":user" => $_POST["user"]]);
            header("Location: projectdetails.php?project=" . urlencode($_GET["project"]));
            die;
        }

        breadcrumb_add_level("Projektek", $root_path . 'projects.php');
        breadcrumb_add_level(htmlspecialchars($_GET["project"]), $root_path . 'projectdetails.php?project=' . htmlspecialchars(urlencode($_GET["project"])));
        breadcrumb_add_level("Hozzárendelés", $root_path . 'assignuser.php?type=project&project=' . htmlspecialchars(urlencode($_GET["project"])));
        include("layout/header.php");
        include("layout/nav.php");
        ?>
        <div class="row justify-content-center">
            <div class="card m-3 col-lg-7 p-0">
                <div class="card-header">Hozzárendelés</div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group mb-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="projectname">Projekt</label>
                                </div>
                                <input type="text" class="form-control" id="projectname" value="<?=htmlspecialchars($_GET["project"])?>"
                                       placeholder="Projekt neve" aria-label="Projekt neve" disabled>
                            </div>
                        </div>
                        <div class="form-group mb-4" id="userSelectGroup">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="userSelect">@</label>
                                </div>
                                <select class="custom-select" id="userSelect" name="user">
                                    <option selected disabled>Válassz...</option>
                                    <?php
                                    foreach ($users as $user) {
                                        echo "<option value=\"" . htmlspecialchars($user) . "\">" . htmlspecialchars($user) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-primary" name="submit" value="Hozzárendelés">
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
} elseif (!empty($_GET["type"]) && !empty($_GET["project"]) && !empty($_GET["category"]) && $_GET["type"] === "category") {
    if (!is_admin($_GET["project"])) {
        header("Location: ./");
        die;
    }
    $query = $db->prepare("SELECT COUNT(*) FROM kategoria WHERE `projekt.nev`=:project AND nev=:category");
    $query->execute([":project" => $_GET["project"], ":category" => $_GET["category"]]);
    if ($query->fetch()[0]) {
        $query = $db->prepare("SELECT felhasznalonev FROM felhasznalo WHERE felhasznalonev NOT IN (SELECT `felhasznalo.felhasznalonev` FROM kategoriakezeles WHERE `projekt.nev`=:project AND `kategoria.nev`=:category) ORDER BY felhasznalonev");
        $query->execute([":project" => $_GET["project"], ":category" => $_GET["category"]]);
        $users = $query->fetchAll(PDO::FETCH_COLUMN, "felhasznalonev");
        if (!empty($_POST["submit"]) && $_POST["submit"] == "Hozzárendelés" && !empty($_POST["user"]) && in_array($_POST["user"], $users)) {
            $query = $db->prepare("INSERT INTO kategoriakezeles (`projekt.nev`, `kategoria.nev`, `felhasznalo.felhasznalonev`) VALUES (:project, :category, :user)");
            $query->execute([":project" => $_GET["project"], ":category" => $_GET["category"], ":user" => $_POST["user"]]);
            header("Location: categorydetails.php?project=" . urlencode($_GET["project"]) . "&category=" . urlencode($_GET["category"]));
            die;
        }
        breadcrumb_add_level("Projektek", $root_path . "projects.php");
        breadcrumb_add_level(htmlspecialchars($_GET["project"]), $root_path . "projectdetails.php?project=" . htmlspecialchars(urlencode($_GET["project"])));
        breadcrumb_add_level(htmlspecialchars($_GET["category"]), $root_path . "categorydetails.php?project=" . htmlspecialchars(urlencode($_GET["project"])) . "&category=" . htmlspecialchars(urlencode($_GET["category"])));
        breadcrumb_add_level("Hozzárendelés", $root_path . "assignuser.php?type=category&project=" . htmlspecialchars(urlencode($_GET["project"])) . "&category=" . htmlspecialchars(urlencode($_GET["category"])));
        include("layout/header.php");
        include("layout/nav.php");
        ?>
        <div class="row justify-content-center">
            <div class="card mt-3 col-7 p-0">
                <div class="card-header">Hozzárendelés</div>
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
                                       aria-label="Kategória név" disabled>
                            </div>
                        </div>
                        <div class="form-group mb-4" id="userSelectGroup">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="userSelect">@</label>
                                </div>
                                <select class="custom-select" id="userSelect" name="user">
                                    <option selected disabled>Válassz...</option>
                                    <?php
                                    foreach ($users as $user) {
                                        echo "<option value=\"" . htmlspecialchars($user) . "\">" . htmlspecialchars($user) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-primary" name="submit" value="Hozzárendelés">
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
    header("Location: ./");
}