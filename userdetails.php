<?php
define("PROTECT", true);
require_once "include/init.php";
if (!logged_in()) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    die;
}

if (empty($_GET["user"])) {
    header("Location: ./");
    die;
}
breadcrumb_add_level("Felhasználók", $root_path . "users.php");
breadcrumb_add_level("@" . htmlspecialchars(strtolower($_GET["user"])), $root_path . "userdetails.php?user=" . htmlspecialchars(urlencode(strtolower($_GET["user"]))));

$query = $db->prepare("SELECT felhasznalonev FROM felhasznalo WHERE felhasznalonev = :user");
$query->execute([":user" => $_GET["user"]]);
include("layout/header.php");
include("layout/nav.php");
if ($query->rowCount()) {
    $query = $db->prepare("SELECT azonosito, targy FROM bug WHERE `szerzo.felhasznalonev` = :user ORDER BY keszites_idopont DESC");
    $query->execute([":user" => $_GET["user"]]);
    $bugs = $query->fetchAll();
    $query = $db->prepare("SELECT `projekt.nev`, `kategoria.nev` FROM kategoriakezeles WHERE `felhasznalo.felhasznalonev` = :user");
    $query->execute([":user" => $_GET["user"]]);
    $assigns = [];
    while ( ($item = $query->fetch()) ) {
        if(empty($assigns[$item["projekt.nev"]])) $assigns[$item["projekt.nev"]] = ["assigned" => false, "categories" => []];
        array_push($assigns[$item["projekt.nev"]]["categories"], $item["kategoria.nev"]);
    }
    $query = $db->prepare("SELECT `projekt.nev` FROM projektkezeles WHERE `felhasznalo.felhasznalonev` = :user");
    $query->execute([":user" => $_GET["user"]]);
    foreach ($query->fetchAll(PDO::FETCH_COLUMN, "projekt.nev") as $project) {
        if(empty($assigns[$project])) $assigns[$project] = ["assigned" => true, "categories" => []];
        $assigns[$project]["assigned"] = true;
    }
    ?>
    <h2>@<?=htmlspecialchars(strtolower($_GET["user"]))?></h2>
    <h3 class="mt-5 mb-3">Bugok</h3>
    <?php
    if (!empty($bugs)) {
        ?>
        <div class="list-group mb-3">
            <?php
            foreach ($bugs as $bug) {
                ?>
                <a href="bugdetails.php?id=<?=htmlspecialchars(urlencode($bug["azonosito"]))?>" class="list-group-item list-group-item-action"><?=htmlspecialchars($bug["targy"])?></a>
                <?php
            }
            ?>
        </div>
        <?php
    } else {
        echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
    }
    ?>
    <h3 class="mt-5 mb-3">Hozzárendelt projektek</h3>
    <?php
    if (!empty($assigns)) {
        echo "<div id=\"accordion\">";
        $count = 0;
        foreach ($assigns as $project => $props) {
            $id = $count++;
            ?>
            <div class="card">
                <div class="card-header" id="heading<?=$id?>">
                    <h5 class="mb-0" <?=(!empty($props["categories"]))?"data-toggle=\"collapse\" data-target=\"#collapse$id\" aria-controls=\"collapse$id\"":""?>>
                        <a href="projectdetails.php?project=<?=htmlspecialchars(urlencode($project))?>">
                            <button class="btn btn-link"><?=htmlspecialchars($project)?></button>
                        </a>
                        <?php
                        if (is_admin($project) && $props["assigned"]) {
                            ?>
                            <a href="deleteassign.php?type=project&project=<?=htmlspecialchars(urlencode($project))?>&user=<?=htmlspecialchars(urlencode(strtolower($_GET["user"])))?>"
                               class="btn btn-danger float-right"><i class="fas fa-times-circle"></i>&nbsp;&nbsp;Hozzárendelés
                                törlése</a>
                            <?php
                        }
                        ?>
                    </h5>
                </div>
                <?php
                if (!empty($props["categories"])) {
                    ?>
                    <div id="collapse<?= $id ?>" class="collapse" aria-labelledby="heading<?= $id ?>"
                         data-parent="#accordion">
                        <div class="list-group mb-3">
                            <?php
                            foreach ($props["categories"] as $category) {
                                ?>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 align-middle">
                                            <a href="categorydetails.php?project=<?=htmlspecialchars(urlencode($project))?>&category=<?=htmlspecialchars(urlencode($category))?>"><?=htmlspecialchars($category)?></a>
                                        </div>
                                        <?php
                                        if(is_admin($project, $category)) {
                                        ?>
                                        <div class="col-md-6 text-right">
                                            <a href="deleteassign.php?type=category&project=<?= htmlspecialchars(urlencode($project)) ?>&category=<?= htmlspecialchars(urlencode($category)) ?>&user=<?= htmlspecialchars(urlencode(strtolower($_GET["user"]))) ?>"
                                               class="btn btn-danger"><i class="fas fa-times-circle"></i>&nbsp;&nbsp;Hozzárendelés
                                                törlése</a>
                                        </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        echo "</div>";
    } else {
        echo "<div class=\"alert alert-warning\">Nincs hozzárendelés!</div>";
    }
} else {
    http_response_code(404);
    echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
}
include("layout/footer.php");
