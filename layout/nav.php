<?php
if (!defined("PROTECT")) {
    header("Location: ./");
    die;
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a href="<?=$root_path?>" class="navbar-brand"><img src="<?=$root_path?>assets/img/logo.png" alt="BugZilla Light" height="40px" class="align-middle mr-3">BugZilla Light</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a href="projects.php" class="nav-link">Projektek</a></li>
                <?php
                if (is_admin()) {
                    echo "<li class=\"nav-item\"><a href=\"projectnew.php\" class=\"nav-link\">Új projekt</a></li>";
                    echo "<li class=\"nav-item\"><a href=\"mockdata.php\" class=\"nav-link\">Mintaadatok</a></li>";
                }
                if (!logged_in()) {
                    echo "<li class=\"nav-item\"><a href=\"login.php\" class=\"nav-link\">Bejelentkezés</a></li>";
                } else {
                    echo "<li class=\"nav-item\"><a href=\"users.php\" class=\"nav-link\">Felhasználók</a></li>";
                }
                ?>
                <li class="nav-item"><a href="/Bugzilla%20Light.docx" class="nav-link">Dokumentáció</a></li>
            </ul>
        </div>
        <form class="form-inline my-2 my-lg-0" method="get" action="search.php">
            <input class="form-control mr-sm-2" type="search" name="q" placeholder="Írj be valamit..." aria-label="Bug keresése" required>
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Keresés</button>
        </form>
    </div>
</nav>
<?php
if (!empty($_SESSION["user"])) {
    ?>
    <div class="nav nav-underline bg-white text-dark">
        <div class="container">
            <ul class="nav">
                <li class="nav-item">
                    <b class="nav-link">Üdv @<?=htmlspecialchars(get_user()["felhasznalonev"])?>!</b>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bugnew.php">Új bug</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Kijelentkezés</a>
                </li>
            </ul>
        </div>
    </div>
    <?php
}
?>
<div class="container">
    <nav aria-label="breadcrumb" class="mt-3">
        <ol class="breadcrumb">
            <?php
            $items = [];
            foreach (breadcrumb_get() as $item) {
                array_push($items, "<li class=\"breadcrumb-item\"><a href=\"" . $item[1] . "\">" . $item[0] ."</a></li>\n");
            }
            end($items);
            $item = &$items[key($items)];

            $item = str_replace("\"breadcrumb-item\"", "\"breadcrumb-item active\" aria-current=\"page\"", $item);
            $item = preg_replace('/<a href="(.*?)">(.*?)<\/a>/', '$2', $item);
            foreach ($items as $value) {
                echo $value;
            }
            ?>
        </ol>
    </nav>
