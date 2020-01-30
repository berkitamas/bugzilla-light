<?php
define("PROTECT", true);
require_once "include/init.php";
$query = $db->prepare("SELECT nev FROM `projekt`");
$query->execute();
$result = $query->fetchAll(PDO::FETCH_COLUMN, "nev");
breadcrumb_add_level("Projektek", $root_path . 'projects.php');
include("layout/header.php");
include("layout/nav.php");
?>
<h2 class="mb-3">Projektek</h2>
<?=(session_msg_exists())?"<div class=\"alert alert-success\" role=\"alert\">" . session_msg_take() . "</div>":""; ?>
<div class="list-group mb-3">
    <?php
    if (!empty($result)) {
        foreach ($result as $project) {
            echo "<a href=\"projectdetails.php?project=" . htmlspecialchars(urlencode($project)) . "\" class=\"list-group-item list-group-item-action\">" . htmlspecialchars($project) . "</a>";
        }
    } else {
        echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
    }
    ?>
</div>
<?=(is_admin())?"<a class=\"btn btn-success\" href=\"projectnew.php\"><i class=\"fas fa-plus-circle\"></i>&nbsp;&nbsp;Új projekt</a>":""?>
<?php
include("layout/footer.php");
