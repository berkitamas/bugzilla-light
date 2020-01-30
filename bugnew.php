<?php
define("PROTECT", true);
require_once "include/init.php";

if (!logged_in()) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    die;
}

$errors = [];
if (!empty($_POST["submit"]) && $_POST["submit"] == "Létrehozás") {
    $project = (!empty($_POST["project"]))?trim($_POST["project"]):"";
    $category = (!empty($_POST["category"]))?trim($_POST["category"]):"";
    $subject = (!empty($_POST["subject"]))?trim($_POST["subject"]):"";
    $body = (!empty($_POST["body"]))?trim($_POST["body"]):"";
    if ($project == "") {
        array_push($errors, "Projekt megadása kötelező!");
    }

    if ($category == "") {
        array_push($errors, "Kategória megadása kötelező!");
    }

    if ($subject === "") {
        array_push($errors, "Tárgy megadása kötelező!");
    } else {
        if (strlen($subject) > 32) {
            array_push($errors, "A tárgy maximum 32 karakterből állhat!");
        }
        if (strlen($subject) < 3) {
            array_push($errors, "A tárgy minimum 3 karakterből kell, hogy álljon!");
        }
    }
    if ($body === "") {
        array_push($errors, "Tartalom megadása kötelező!");
    } else {
        if (strlen($body) < 16) {
            array_push($errors, "A tartalom minimum 16 karakterből kell, hogy álljon!");
        }
    }

    if (!count($errors)) {
        $query = $db->prepare("SELECT COUNT(*) FROM kategoria WHERE `projekt.nev` = :project AND nev=:category");
        $query->execute([
            ":project" => $project,
            ":category" => $category
        ]);
        if ($query->fetch()[0]) {
            $query = $db->prepare(<<<SQLSTMT
INSERT INTO bug (targy, leiras, sulyossag, `szerzo.felhasznalonev`, `projekt.nev`, `kategoria.nev`, keszites_idopont) 
VALUES (:subject, :details, 'Ismeretlen', :user, :project, :category, now());
SQLSTMT
);
            $query->execute([
                ":subject" => $subject,
                ":details" => $body,
                ":user" => get_user()["felhasznalonev"],
                ":project" => $project,
                ":category" => $category
            ]);
            $query = $db->prepare("SELECT azonosito FROM bug WHERE targy = :subject ORDER BY keszites_idopont DESC LIMIT 1");
            $query->execute([
                ":subject" => $subject
            ]);
            header("Location: bugdetails.php?id=" . $query->fetch()[0]);
            die;
        } else {
            array_push($errors, "Kategória vagy projekt nem létezik!");
        }
    }
}

$query = $db->prepare("SELECT nev FROM projekt");
$query->execute();
$projects = $query->fetchAll(PDO::FETCH_COLUMN, "nev");

breadcrumb_add_level("Projektek", $root_path . 'projects.php');
if (!empty($_REQUEST["project"]) && !empty($_REQUEST["category"])) {
    breadcrumb_add_level(htmlspecialchars($_REQUEST["project"]), $root_path . "projectdetails.php?project=" . htmlspecialchars(urlencode($_REQUEST["project"])));
    breadcrumb_add_level($_REQUEST["category"], $root_path . "categorydetails.php?project=" . htmlspecialchars(urlencode($_REQUEST["project"])) . "&category=" . htmlspecialchars(urlencode($_REQUEST["category"])));
}
breadcrumb_add_level("Új bug", $root_path . "bugnew.php?project=1");
include("layout/header.php");
include("layout/nav.php");
?>
    <div class="row justify-content-center">
        <div class="card m-3 col-lg-7 p-0">
            <div class="card-header">Új bug</div>
            <?php
            if (!empty($errors)) {
                echo "<div class=\"alert alert-danger border-0 rounded-0\" role=\"alert\">
            A művelet nem sikerült!<br />Kérem a folytatáshoz javítsa ki az itt felsorolt hibákat:
            <ul>";
                foreach ($errors as $error) {
                    echo "<li>" . htmlspecialchars($error) . "</li>";
                }
                echo "</ul></div>";
            }
            ?>
            <div class="card-body">
                <form method="post">
                    <div class="form-group mb-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="projectSelect">Projekt</label>
                            </div>
                            <select class="custom-select" id="projectSelect" name="project">
                                <option selected disabled>Válassz...</option>
                                <?php
                                foreach ($projects as $project) {
                                    echo "<option value=\"" . htmlspecialchars($project). "\" " . ((!empty($_REQUEST["project"]) && $project == $_REQUEST["project"])?"selected":"") . ">" . htmlspecialchars($project). "</option>";
                                }
                                ?>
                            </select>
                            <script><?php
                                    if (!empty($_REQUEST["project"]) && !empty($_REQUEST["category"])) {
                                        ?>
                                        $(document).ready(function () {
                                            $.getJSON("projectdetails.php?project=<?=str_replace("\"", "", json_encode(urlencode($_REQUEST["project"])))?>&action=getcategories", function (data) {
                                                $("#categorySelect").html("<option disabled>Válassz...</option>");
                                                $.each(data, function (key, val) {
                                                    if (val == <?=json_encode($_REQUEST["category"])?>) {
                                                        $("#categorySelect").append("<option value=\""+val+"\" selected>"+val+"</option>");
                                                    } else {
                                                        $("#categorySelect").append("<option value=\""+val+"\">"+val+"</option>");
                                                    }
                                                });
                                                $("#categorySelectGroup").removeClass("d-none");
                                            });
                                        });
                                        <?php
                                    }
                                ?>
                                $("#projectSelect").change(function () {
                                   $.getJSON("projectdetails.php?project="+$("#projectSelect").val()+"&action=getcategories", function (data) {
                                        $("#categorySelect").html("<option selected disabled>Válassz...</option>");
                                        $.each(data, function (key, val) {
                                            $("#categorySelect").append("<option value=\""+val+"\">"+val+"</option>");
                                        });
                                        $("#categorySelectGroup").removeClass("d-none");
                                   });
                                });
                            </script>
                        </div>
                    </div>
                    <div class="form-group mb-4 d-none" id="categorySelectGroup">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="categorySelect">Kategória</label>
                            </div>
                            <select class="custom-select " id="categorySelect" name="category">
                                <option selected disabled>Válassz...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="at-symbol">Tárgy</span>
                            </div>
                            <input type="text" class="form-control" name="subject" placeholder="Tárgy" aria-label="Tárgy" aria-describedby="at-symbol" <?=(!empty($_POST["subject"]))?"value=\"" . htmlspecialchars($_POST["subject"]) . "\"":""?>>
                        </div>
                        <label for="bug-body">Bug tartalma</label>
                        <textarea name="body" id="bug-body" rows="6" class="form-control"><?=(!empty($_POST["body"]))?htmlspecialchars($_POST["body"]):""?></textarea>
                    </div>
                    <input type="submit" class="btn btn-primary" name="submit" value="Létrehozás">
                </form>
            </div>
        </div>
    </div>
<?php
include("layout/footer.php");