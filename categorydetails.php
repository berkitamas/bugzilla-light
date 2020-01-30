<?php
define("PROTECT", true);
require_once "include/init.php";

if (empty($_GET["project"]) || empty($_GET["category"])) {
    header("Location: ./");
    die;
}
breadcrumb_add_level("Projektek", $root_path . "projects.php");
breadcrumb_add_level(htmlspecialchars($_GET["project"]), $root_path . "projectdetails.php?project=" . htmlspecialchars(urlencode($_GET["project"])));
breadcrumb_add_level(htmlspecialchars($_GET["category"]), $root_path . "categorydetails.php?project=" . htmlspecialchars(urlencode($_GET["project"])) . "&category=" . htmlspecialchars(urlencode($_GET["category"])));

$query = $db->prepare("SELECT COUNT(*) FROM kategoria WHERE `projekt.nev`=:project AND nev=:category");
$query->execute([":project" => $_GET["project"], ":category" => $_GET["category"]]);
include("layout/header.php");
include("layout/nav.php");
if ($query->fetch()[0] > 0) {
    $project = $_GET["project"];
    $category = $_GET["category"];
    $query = $db->prepare("SELECT azonosito, targy FROM bug WHERE `kategoria.nev`=:name ORDER BY keszites_idopont DESC");
    $query->execute([":name" => $category]);
    $bugs = $query->fetchAll();
    $query = $db->prepare("SELECT `felhasznalo.felhasznalonev` AS felhasznalo FROM `kategoriakezeles` WHERE `projekt.nev`=:project AND `kategoria.nev`=:category");
    $query->execute([":project" => $project, ":category" => $category]);
    $assigns = $query->fetchAll(PDO::FETCH_COLUMN, "felhasznalo");
    $query = $db->prepare(<<<SQLSTMT
SELECT sulyossag, CAST(AVG(lezaras.lezarasi_kul) AS DECIMAL(10,2)) AS atlag_ido
FROM bug
JOIN (
     SELECT `bug.azonosito`, TIMESTAMPDIFF(HOUR, bug.keszites_idopont, MAX(statuszfrissites.idopont)) AS lezarasi_kul
     FROM statuszfrissites, bug
     WHERE statuszfrissites.`bug.azonosito` = bug.azonosito AND uj_statusz='Megoldott'
     GROUP BY `bug.azonosito`
) AS lezaras ON lezaras.`bug.azonosito` = bug.azonosito
WHERE `projekt.nev` = :project AND `kategoria.nev` = :category
GROUP BY sulyossag
ORDER BY FIELD(sulyossag, "Kritikus", "Magas", "Közepes", "Alacsony", "Nagyon Alacsony", "Ismeretlen"), sulyossag;
SQLSTMT
    );
    $query->execute([":project" => $project, ":category" => $category]);
    $avg_bug_time_per_severity = $query->fetchAll();
    ?>
    <h6 class="card-subtitle mb-2 mt-4"><?=htmlspecialchars($project)?></h6>
    <h2 class="card-title mb-3"><?=htmlspecialchars($category)?></h2>
    <h3 class="mb-3">Bugok</h3>
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
    <?=(logged_in())?"<a class=\"btn btn-success\" href=\"bugnew.php?project=" . htmlspecialchars(urlencode($project)) . "&category=" . htmlspecialchars(urlencode($category)) . "\"><i class=\"fas fa-plus-circle\"></i>&nbsp;&nbsp;Új bug</a>":""?>
    <h3 class="mt-5 mb-3">Statisztika</h3>
    <div class="card mt-2">
        <div class="card-header">Bugok átlagos élettartama súlyosság szerint csoportosítva</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Súlyosság</th>
                        <th scope="col">Átlagos idő</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($avg_bug_time_per_severity as $severity) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($severity["sulyossag"]) . "</td>";
                    echo "<td>" . htmlspecialchars($severity["atlag_ido"]) . " óra</td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <h3 class="mt-5 mb-3">Hozzárendelések</h3>
    <?php
    if (!empty($assigns)) {
        ?>
        <div class="list-group mb-3">
            <?php
            foreach ($assigns as $user) {
                ?>
                <div class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-md-6 align-middle">
                            <a href="userdetails.php?user=<?=htmlspecialchars(urlencode($user))?>" class="list-group-item-action align-middle">@<?=htmlspecialchars($user)?></a>
                        </div>
                        <?= (is_admin($_GET["project"])) ? "<div class=\"col-md-6 text-right\"><a href=\"deleteassign.php?type=category&project=" . htmlspecialchars(urlencode($project)) . "&category=" . htmlspecialchars(urlencode($category)) . "&user=" . htmlspecialchars(urlencode($user)) . "\" class=\"btn btn-danger mr-2\"><i class=\"fas fa-times-circle\"></i>&nbsp;&nbsp;Hozzárendelés törlése</a></div>" : "" ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    } else {
        echo "<div class=\"alert alert-warning\">Nincs hozzárendelés!</div>";
    }
    echo (is_admin($project))?"<a class=\"btn btn-success\" href=\"assignuser.php?type=category&project=" . htmlspecialchars(urlencode($project)) . "&category=" . htmlspecialchars(urlencode($category)) . "\"><i class=\"fas fa-plus-circle\"></i>&nbsp;&nbsp;Új hozzárendelés</a>":"";
} else {
    http_response_code(404);
    echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
}
include("layout/footer.php");
