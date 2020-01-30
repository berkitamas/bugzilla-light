<?php
define("PROTECT", true);
require_once "include/init.php";

if (empty($_GET["id"])) {
    header("Location: ./");
    die;
}

$query = $db->prepare(<<<SQLSTMT
SELECT azonosito, targy, leiras, sulyossag, `projekt.nev`, `kategoria.nev`, keszites_idopont, `szerzo.felhasznalonev`, szerzo.email AS `szerzo.email`, szerzo.telefon AS `szerzo.telefon`, `hozzarendelt.felhasznalonev`, hozzarendelt.email AS `hozzarendelt.email`, hozzarendelt.telefon AS `hozzarendelt.telefon`
    FROM bug
    LEFT JOIN felhasznalo AS hozzarendelt ON bug.`hozzarendelt.felhasznalonev` = hozzarendelt.felhasznalonev
    JOIN felhasznalo AS szerzo ON bug.`szerzo.felhasznalonev` = szerzo.felhasznalonev
    WHERE bug.azonosito = :id
SQLSTMT
);
$query->execute([":id" => $_GET["id"]]);
$bug = $query->fetch();

if (!empty($bug)) {
    $query = $db->prepare(<<<SQLSTMT
SELECT idopont, 'hozzaszolas' AS tipus, tartalom, `felhasznalo.felhasznalonev`, NULL AS regi_statusz, NULL AS uj_statusz 
  FROM hozzaszolas WHERE `bug.azonosito`=:id

UNION ALL

SELECT idopont, 'statuszfrissites' AS tipus, NULL AS tartalom, NULL AS `felhasznalo.felhasznalonev`, regi_statusz, uj_statusz 
  FROM statuszfrissites WHERE `bug.azonosito`=:id 
ORDER BY idopont ASC;
SQLSTMT
);
    $query->execute([":id" => $bug["azonosito"]]);
    $updates = $query->fetchAll();
    $status = "Nyitott";
    foreach ($updates as $update) {
        if ($update["tipus"] == "statuszfrissites") {
            $status = $update["uj_statusz"];
        }
    }
    $query = $db->prepare(<<<SQLSTMT
SELECT `felhasznalo.felhasznalonev` FROM kategoriakezeles WHERE `projekt.nev`=:project AND `kategoria.nev`=:category 
UNION 
SELECT `felhasznalo.felhasznalonev` FROM projektkezeles WHERE `projekt.nev`=:project
SQLSTMT
);
    $query->execute([":project" => $bug["projekt.nev"], ":category" => $bug["kategoria.nev"]]);
    $assignable = $query->fetchAll(PDO::FETCH_COLUMN, "felhasznalo.felhasznalonev");
    $query = $db->prepare("SELECT nev FROM kategoria WHERE `projekt.nev`=:project ORDER BY nev");
    $query->execute([":project" => $bug["projekt.nev"]]);
    $categories = $query->fetchAll(PDO::FETCH_COLUMN, "nev");
    if (!in_array("admin", $assignable)) array_push($assignable, "admin");
    $bug_statuses = ["Nyitott", "Hozzárendelt", "Lezárt", "Válaszra vár", "Folyamatban", "Megoldott"];
    $bug_severity = ["Kritikus", "Magas", "Közepes", "Alacsony", "Nagyon Alacsony", "Ismeretlen"];
    $errors = [];
    if (logged_in() && !empty($_POST["submit"])) {
        if ($bug["hozzarendelt.felhasznalonev"] == get_user()["felhasznalonev"]) {
            if ($_POST["submit"] == "Státusz frissítése" && !empty($_POST["status"]) && in_array($_POST["status"], $bug_statuses)) {
                $query = $db->prepare("INSERT INTO statuszfrissites (idopont, `bug.azonosito`, regi_statusz, uj_statusz) VALUES (NOW(), :id, :old, :new)");
                $query->execute([
                        ":id" => $bug["azonosito"],
                        ":old" => $status,
                        ":new" => $_POST["status"]
                ]);
                header("Refresh:0");
            }
            if ($_POST["submit"] == "Súlyosság frissítése" && !empty($_POST["severity"]) && in_array($_POST["severity"], $bug_severity)) {
                $query = $db->prepare("UPDATE bug SET sulyossag=:severity WHERE azonosito=:id");
                $query->execute([
                    ":id" => $bug["azonosito"],
                    ":severity" => $_POST["severity"]
                ]);
                header("Refresh:0");
            }
        }
        if (is_admin($bug["projekt.nev"], $bug["kategoria.nev"])) {
            if ($_POST["submit"] == "Hozzárendelés frissítése" && !empty($_POST["user"]) && in_array($_POST["user"], $assignable)) {
                $query = $db->prepare("UPDATE bug SET `hozzarendelt.felhasznalonev`=:user WHERE azonosito=:id");
                $query->execute([
                    ":id" => $bug["azonosito"],
                    ":user" => $_POST["user"]
                ]);
                header("Refresh:0");
            }
            if ($_POST["submit"] == "Kategória módosítása" && !empty($_POST["category"]) && in_array($_POST["category"], $categories)) {
                $query = $db->prepare("UPDATE bug SET `kategoria.nev`=:category WHERE azonosito=:id");
                $query->execute([
                    ":id" => $bug["azonosito"],
                    ":category" => $_POST["category"]
                ]);
                header("Refresh:0");
            }
        }
        if ($_POST["submit"] == "Hozzászólás") {
            if (!empty($_POST["body"])) {
                if (strlen($_POST["body"]) > 32) {
                    array_push($errors, "A hozzászólás tartalma maximum 3000 karakterből állhat!");
                }
                if (strlen($_POST["body"]) < 2) {
                    array_push($errors, "A hozzászólás tartalma mnimum 2 karakterből kell, hogy álljon!");
                }
            } else {
                array_push($errors, "Hozzászólás tartalmának megadása kötelező!");
            }
            if (!count($errors)) {
                $query = $db->prepare("INSERT INTO hozzaszolas (idopont, `bug.azonosito`, `felhasznalo.felhasznalonev`, tartalom) VALUES (NOW(), :id, :user, :body)");
                $query->execute([
                    ":id" => $bug["azonosito"],
                    ":user" => get_user()["felhasznalonev"],
                    ":body" => $_POST["body"]
                ]);
                header("Refresh:0");
            }
        }
    }
    breadcrumb_add_level("Projektek", $root_path . 'projects.php');
    breadcrumb_add_level(htmlspecialchars($bug["projekt.nev"]), $root_path . "projectdetails.php?project=" . htmlspecialchars(urlencode($bug["projekt.nev"])));
    breadcrumb_add_level(htmlspecialchars($bug["kategoria.nev"]), $root_path . "categorydetails.php?project=" . htmlspecialchars(urlencode($bug["projekt.nev"])) . "&category=" . htmlspecialchars(urlencode($bug["kategoria.nev"])));
    breadcrumb_add_level(htmlspecialchars($bug["targy"]), $root_path . "bugdetails.php?id=" . $bug["azonosito"]);
    include("layout/header.php");
    include("layout/nav.php");
    ?>
    <div class="card mt-3 mb-2">
        <div class="card-body">
            <h2 class="card-title mb-4"><?=htmlspecialchars($bug["targy"])?></h2>
            <div class="row justify-content-between mb-3">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="hash-symbol">#</span>
                        </div>
                        <input type="text" class="form-control" aria-label="Azonosító" title="Azonosító"
                               value="<?=htmlspecialchars($bug["azonosito"])?>" aria-describedby="hash-symbol" disabled
                               aria-disabled="true">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="input-group<?=($bug["hozzarendelt.felhasznalonev"] == get_user()["felhasznalonev"])?" clickable\" data-toggle=\"modal\" data-target=\"#changeStatusModal\" role=\"button\"":"\""?>>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="status-symbol"><i class="fas fa-clipboard-list"></i></span>
                        </div>
                        <input type="text" class="form-control<?=($bug["hozzarendelt.felhasznalonev"] == get_user()["felhasznalonev"])?" clickable":""?>" aria-label="Státusz"
                               title="Státusz" value="<?=htmlspecialchars($status)?>" aria-describedby="status-symbol"
                               disabled aria-disabled="true">
                    </div>
                </div>
            </div>
            <div class="row justify-content-between">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="calendar-symbol"><i
                                        class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control" aria-label="Jelentve" title="Jelentve"
                               value="<?=htmlspecialchars($bug["keszites_idopont"])?>" aria-describedby="calendar-symbol" disabled
                               aria-disabled="true">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="input-group<?=($bug["hozzarendelt.felhasznalonev"] == get_user()["felhasznalonev"])?" clickable\" data-toggle=\"modal\" data-target=\"#changeSeverityModal\" role=\"button\"":"\""?>>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="warning-symbol"><i
                                        class="fas fa-exclamation-circle"></i></span>
                        </div>
                        <input type="text" class="form-control<?=($bug["hozzarendelt.felhasznalonev"] == get_user()["felhasznalonev"])?" clickable":""?>" aria-label="Súlyosság"
                               title="Súlyosság" value="<?=htmlspecialchars($bug["sulyossag"])?>" aria-describedby="warning-symbol" disabled
                               aria-disabled="true">
                    </div>
                </div>
            </div>
            <div class="row justify-content-between mt-3">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group clickable" title="Szerző" data-toggle="popover" data-html="true" data-content="E-mail: <a href='mailto:<?=htmlspecialchars($bug["szerzo.email"])?>'><?=htmlspecialchars($bug["szerzo.email"])?></a><br />Telefon: <?=(!empty($bug["szerzo.telefon"])?htmlspecialchars($bug["szerzo.telefon"]):"Nincs")?>" >
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="at-symbol">@</span>
                        </div>
                        <input type="text" class="form-control clickable" aria-label="Szerző" title="Szerző" value="<?=htmlspecialchars($bug["szerzo.felhasznalonev"])?>"
                               aria-describedby="at-symbol" disabled aria-disabled="true">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="input-group clickable" <?=(!empty($bug["hozzarendelt.felhasznalonev"]))?"title=\"Hozzárendelt\" data-toggle=\"popover\" data-placement=\"left\" data-html=\"true\" data-content=\"E-mail: <a href='mailto:" . htmlspecialchars($bug["hozzarendelt.email"]) . "'>" . htmlspecialchars($bug["hozzarendelt.email"]) . "</a><br />Telefon: " . (!empty($bug["hozzarendelt.telefon"])?htmlspecialchars($bug["hozzarendelt.telefon"]):"Nincs") . "\"":""?>>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="admin-symbol"><i class="fas fa-user-cog"></i></span>
                        </div>
                        <input type="text" class="form-control clickable" <?=(is_admin($bug["projekt.nev"], $bug["kategoria.nev"]))?" data-toggle=\"modal\" data-target=\"#changeAssignModal\" role=\"button\"":"\""?> aria-label="Hozzárendelt felhasználó"
                               title="Hozzárendelt felhasználó" value="<?=(!empty($bug["hozzarendelt.felhasznalonev"]))?htmlspecialchars($bug["hozzarendelt.felhasznalonev"]):"Nincs"?>" aria-describedby="admin-symbol"
                               readonly aria-readonly="true">
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <p class="card-text"><?=nl2br(htmlspecialchars($bug["leiras"]))?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
    if(is_admin($bug["projekt.nev"], $bug["kategoria.nev"])) {
    ?>
    <div class="card mt-3">
        <div class="card-body">
            <div class="btn btn-primary" data-toggle="modal" data-target="#changeAssignModal" role="button">Hozzárendelés módosítása</div>
            <br/>
            <div class="btn btn-primary mt-2" data-toggle="modal" data-target="#changeCategoryModal" role="button">Kategória módosítása</div>
        </div>
    </div>
    <?php
    }
    ?>
    <div class="card mt-3 activity">
        <div class="card-body pb-2">
            <h4 class="card-title border-bottom pb-2">Aktivitás</h4>
            <?php
            if (!empty($updates)) {
                foreach ($updates as $update) {
                    ?>
                    <div class="row mt-2">
                        <div class="col-12 border-bottom pb-2">
                            <?=($update["tipus"] == "hozzaszolas")?"<h5><a href=\"userdetails.php?user=" . htmlspecialchars(urlencode($update["felhasznalo.felhasznalonev"])) . "\">@" . htmlspecialchars(strtolower($update["felhasznalo.felhasznalonev"])) . "</a></h5>":""?>
                            <p><?=($update["tipus"] == "hozzaszolas")?nl2br(htmlspecialchars($update["tartalom"])):"Státusz módosítva lett erről: <b>" . htmlspecialchars($update["regi_statusz"]) . "</b>, erre: <b>" . $update["uj_statusz"] . "</b>."?></p>
                            <h6><?=htmlspecialchars($update["idopont"])?></h6>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";

            }
            ?>
        </div>
    </div>
    <?php if (logged_in()) {
        if (!empty($errors)) {
            echo "<div class=\"alert alert-danger mt-5\" role=\"alert\">
            A művelet nem sikerült!<br />Kérem a folytatáshoz javítsa ki az itt felsorolt hibákat:
            <ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul></div>";
        }
        ?>
        <h4 class="mt-4" id="compose-comment">Új hozzászólás</h4>
        <form method="post" action="#compose-comment">
            <div class="form-group">
                <label for="comment-body">Hozzászólás tartalma</label>
                <textarea name="body" id="comment-body" rows="6" class="form-control"></textarea>
            </div>
            <input type="submit" class="btn btn-primary" name="submit" value="Hozzászólás">
        </form>
    <?php } ?>
    <?php
    if ($bug["hozzarendelt.felhasznalonev"] == get_user()["felhasznalonev"]) {
        ?>
        <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="changeStatusModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Státusz frissítése</h5>
                            <button type="reset" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="changeAssignSelect">Státusz</label>
                                </div>
                                <select class="custom-select" id="changeAssignSelect" name="status">
                                    <?php
                                    foreach ($bug_statuses as $bug_status) {
                                        echo "<option value=\"" . $bug_status . "\"" . (($status == $bug_status)?" selected":"") . ">" . $bug_status . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-secondary" data-dismiss="modal">Mégse</button>
                            <button type="submit" class="btn btn-primary" name="submit" value="Státusz frissítése">
                                Státusz frissítése
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="changeSeverityModal" tabindex="-1" role="dialog" aria-labelledby="changeSeverityModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Súlyosság frissítése</h5>
                            <button type="reset" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="changeSeveritySelect">Súlyosság</label>
                                </div>
                                <select class="custom-select" id="changeSeveritySelect" name="severity">
                                    <?php
                                        foreach ($bug_severity as $severity) {
                                            echo "<option value=\"" . $severity . "\"" . (($severity == $bug["sulyossag"])?" selected":"") . ">" . $severity . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-secondary" data-dismiss="modal">Mégse</button>
                            <button type="submit" class="btn btn-primary" name="submit" value="Súlyosság frissítése">
                                Súlyosság frissítése
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        }
        if (is_admin($bug["projekt.nev"], $bug["kategoria.nev"])) {
            ?>
        <div class="modal fade" id="changeAssignModal" tabindex="-1" role="dialog" aria-labelledby="changeAssignModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Hozzárendelés frissítése</h5>
                            <button type="reset" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="changeAssignSelect">Felhasználó</label>
                                </div>
                                <select class="custom-select" id="changeAssignSelect" name="user">
                                    <?php
                                    foreach ($assignable as $user) {
                                        echo "<option value=\"" . htmlspecialchars($user) . "\"" . (($user == $bug["hozzarendelt.felhasznalonev"])?" selected":"") . ">" . htmlspecialchars($user) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-secondary" data-dismiss="modal">Mégse</button>
                            <button type="submit" class="btn btn-primary" name="submit" value="Hozzárendelés frissítése">
                                Hozzárendelés frissítése
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="changeCategoryModal" tabindex="-1" role="dialog" aria-labelledby="changeCategoryModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Kategória módosítása</h5>
                            <button type="reset" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="changeCategorySelect">Kategória</label>
                                </div>
                                <select class="custom-select" id="changeCategorySelect" name="category">
                                    <?php
                                    foreach ($categories as $category) {
                                        echo "<option value=\"" . htmlspecialchars($category) . "\"" . (($category == $bug["kategoria.nev"])?" selected":"") . ">" . htmlspecialchars($category) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-secondary" data-dismiss="modal">Mégse</button>
                            <button type="submit" class="btn btn-primary" name="submit" value="Kategória módosítása">
                                Kategória módosítása
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <?php include("layout/footer.php");
} else {
    http_response_code(404);
    include("layout/header.php");
    include("layout/nav.php");
    echo "<div class=\"alert alert-warning\">Nincs megjelenítendő adat!</div>";
    include("layout/footer.php");
}
?>