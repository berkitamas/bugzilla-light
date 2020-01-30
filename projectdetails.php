<?php
define("PROTECT", true);
require_once "include/init.php";
if (!empty($_GET["action"]) && $_GET["action"] === "getcategories") {
    $query = $db->prepare("SELECT nev FROM kategoria WHERE `projekt.nev`=:name");
    $query->execute([":name" => $_GET["project"]]);
    $categories = $query->fetchAll(PDO::FETCH_COLUMN, "nev");
    die(json_encode($categories));
}

if (empty($_GET["project"])) {
    header("Location: ./");
    die;
}

$query = $db->prepare("SELECT * FROM `projekt` WHERE nev=:name");
$query->execute([":name" => $_GET["project"]]);
$project = $query->fetch();
breadcrumb_add_level("Projektek", $root_path . 'projects.php');
breadcrumb_add_level(htmlspecialchars($_GET["project"]), $root_path . 'projectdetails.php?project=' . htmlspecialchars(urlencode($_GET["project"])));
include("layout/header.php");
include("layout/nav.php");
if (!empty($project)) {
    $query = $db->prepare("SELECT nev FROM `kategoria` WHERE `projekt.nev`=:name");
    $query->execute([":name" => $project["nev"]]);
    $categories = $query->fetchAll(PDO::FETCH_COLUMN, "nev");
    $query = $db->prepare("SELECT `felhasznalo.felhasznalonev` AS felhasznalo FROM `projektkezeles` WHERE `projekt.nev`=:name");
    $query->execute([":name" => $project["nev"]]);
    $assigns = $query->fetchAll(PDO::FETCH_COLUMN, "felhasznalo");
    ?>
    <h2 class="card-title mb-4"><?= htmlspecialchars($project["nev"]) ?></h2>
    <?php if (!empty($categories)) { ?>
    <h3 class="mt-5 mb-3">Statisztika</h3>
    <div class="card">
        <div class="card-header">Bugok száma az utolsó egy hónapban kategóriák szerint csoportosítva</div>
        <div class="card-body">
            <img src="projectstats.php?project=<?=htmlspecialchars(urlencode($project["nev"]))?>" class="img-fluid" alt="Bugok száma az utolsó egy hónapban kategóriák szerint csoportosítva">
        </div>
    </div>
    <?php } ?>
    <h3 class="mt-5 mb-3">Kategóriák</h3>
    <div class="list-group mb-3">
        <?php
        if (!empty($categories)) {
            foreach ($categories as $category) {
                echo "<a href=\"categorydetails.php?project=" . htmlspecialchars(urlencode($project["nev"])) . "&category=" . htmlspecialchars(urlencode($category)) . "\" class=\"list-group-item list-group-item-action\">" . htmlspecialchars($category) . "</a>";
            }
        } else {
            echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
        }
        ?>
    </div>
    <?= (is_admin($_GET["project"])) ? "<a class=\"btn btn-success\" href=\"categorynew.php?project=" . htmlspecialchars(urlencode($project["nev"])) . "\"><i class=\"fas fa-plus-circle\"></i>&nbsp;&nbsp;Új kategória</a>" : "" ?>
    <h3 class="mt-5 mb-3">Hozzárendelések</h3>
    <?php
    if (!empty($assigns)) {
        echo "<div class=\"list-group mb-3\">";
        foreach ($assigns as $assign) {
        ?>
            <div class="list-group-item">
                <div class="row align-items-center">
                    <div class="col-md-6 align-middle">
                        <a href="userdetails.php?user=<?=htmlspecialchars(urlencode($assign))?>" class="list-group-item-action align-middle">@<?=htmlspecialchars($assign)?></a>
                    </div>
                    <?= is_admin() ? "<div class=\"col-md-6 text-right\"><a href=\"deleteassign.php?type=project&project=" . htmlspecialchars(urlencode($project["nev"])) . "&user=" . htmlspecialchars(urlencode($assign)) . "\" class=\"btn btn-danger mr-2\"><i class=\"fas fa-times-circle\"></i>&nbsp;&nbsp;Hozzárendelés törlése</a></div>" : "" ?>
                </div>
            </div>
        <?php
        }
        echo "</div>";
    } else {
        echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
    }
    ?>
    <?= is_admin() ? "<a class=\"btn btn-success\" href=\"assignuser.php?type=project&project=" . htmlspecialchars(urlencode($project["nev"])) . "\"><i class=\"fas fa-plus-circle\"></i>&nbsp;&nbsp;Új hozzárendelés</a>":""?>
    <?php
} else {
    http_response_code(404);
    echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
}
include("layout/footer.php");
