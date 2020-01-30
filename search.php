<?php
define("PROTECT", true);
require_once "include/init.php";
breadcrumb_add_level("Keresés", "search.php");

if (!empty($_GET["q"])) {
    $projects = [];
    $categories = [];
    $issues = [];
    $query = $db->prepare("SELECT nev FROM projekt WHERE nev LIKE concat('%', :query, '%')");
    $query->execute([":query" => $_GET["q"]]);
    $projects = $query->fetchAll(PDO::FETCH_COLUMN, "nev");
    $query = $db->prepare("SELECT `projekt.nev`, nev FROM kategoria WHERE nev LIKE concat('%', :query, '%')");
    $query->execute([":query" => $_GET["q"]]);
    $categories = $query->fetchAll();
    $query = $db->prepare("SELECT azonosito, targy, `projekt.nev`, `kategoria.nev` FROM bug WHERE azonosito LIKE concat('%', :query, '%') OR targy LIKE concat('%', :query, '%')");
    $query->execute([":query" => $_GET["q"]]);
    $issues = $query->fetchAll();
    $users = [];
    if (logged_in()) {
        $query = $db->prepare("SELECT felhasznalonev FROM felhasznalo WHERE felhasznalonev LIKE concat('%', :query, '%')");
        $query->execute([":query" => $_GET["q"]]);
        $users = $query->fetchAll(PDO::FETCH_COLUMN, "felhasznalonev");
    }
}

include("layout/header.php");
include("layout/nav.php");
?>
<h2>Projektek</h2>
<?php
if (!empty($projects)) {
    echo "<div class=\"list-group mb-3\">";
    foreach ($projects as $project) {

        echo "<a href=\"projectdetails.php?project=" . htmlspecialchars(urlencode($project)) . "\" class=\"list-group-item list-group-item-action\">" . htmlspecialchars($project) . "</a>";
    }
    echo "</div>";
} else {
    echo "<div class=\"alert alert-warning\">Nincs találat!</div>";
}
?>
<h2 class="mt-5 mb-3">Kategóriák</h2>
<?php
    if (!empty($categories)) {
        echo "<div class=\"list-group mb-3\">";
        foreach ($categories as $category) {
            echo "<a href=\"categorydetails.php?project=" . htmlspecialchars(urlencode($category["projekt.nev"])) . "&category=" . htmlspecialchars(urlencode($category["nev"])) . "\" class=\"list-group-item list-group-item-action\">" . htmlspecialchars($category["nev"]) . " (" . htmlspecialchars($category["projekt.nev"]) . ")</a>";
        }
        echo "</div>";
    } else {
        echo "<div class=\"alert alert-warning\">Nincs találat!</div>";
    }
?>
<h2 class="mt-5 mb-3">Bugok</h2>
<?php
if (!empty($issues)) {
    echo "<div class=\"list-group mb-3\">";
    foreach ($issues as $issue) {
        echo "<a href=\"bugdetails.php?id=" . htmlspecialchars(urlencode($issue["azonosito"])) . "\" class=\"list-group-item list-group-item-action\">" . htmlspecialchars($issue["targy"]) . " (" . htmlspecialchars($issue["projekt.nev"]) . " - " . htmlspecialchars($issue["kategoria.nev"]) . ")</a>";
    }
    echo "</div>";
} else {
    echo "<div class=\"alert alert-warning\">Nincs találat!</div>";
}
if (logged_in()) {
    ?>
    <h2 class="mt-5 mb-3">Felhasználók</h2>
    <?php
    if (!empty($users)) {
        echo "<div class=\"list-group mb-3\">";
        foreach ($users as $user) {
            echo "<a href=\"userdetails.php?user=" . htmlspecialchars(urlencode(strtolower($user))) . "\" class=\"list-group-item list-group-item-action\">@" . htmlspecialchars(strtolower($user)) . "</a>";
        }
        echo "</div>";
    } else {
        echo "<div class=\"alert alert-warning\">Nincs találat!</div>";
    }
}
include("layout/footer.php");