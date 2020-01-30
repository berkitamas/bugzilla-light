<?php
define("PROTECT", true);
require_once "include/init.php";
if (!logged_in()) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    die;
}
$users = [];
$query = $db->prepare("SELECT felhasznalonev FROM felhasznalo ORDER BY felhasznalonev");
$query->execute();
$users = $query->fetchAll(PDO::FETCH_COLUMN, "felhasznalonev");

breadcrumb_add_level("Felhasználók", $root_path . "users.php");
include("layout/header.php");
include("layout/nav.php");
?>
<h2 class="mb-3">Felhasználók</h2>
<div class="list-group mb-3">
    <?php
    if (!empty($users)) {
        foreach ($users as $user) {
            $user = strtolower($user);
            ?>
            <a href="userdetails.php?user=<?=htmlspecialchars(urlencode($user))?>" class="list-group-item list-group-item-action">@<?=htmlspecialchars($user)?></a>
            <?php
        }
    } else {
        echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
    }
    ?>
</div>
<?php
include("layout/footer.php");
