<?php
define("PROTECT", true);
require_once "include/init.php";
require_once "include/autoload.php";
breadcrumb_add_level("Mintaadatok", $root_path . 'mockdata.php');

if (!is_admin()) {
    header("Location: ./");
    die;
}

if (!empty($_POST["submit"])) {
    if ($_POST["submit"] == "Mintaadatok felvétele") {
        set_time_limit(0); // Nagyon, nagyon hosszú lekérdezés...
        ini_set('memory_limit', '-1'); // És nagyon, nagyon sokat eszik
        $bug_statuses = ["Nyitott", "Hozzárendelt", "Lezárt", "Válaszra vár", "Folyamatban", "Megoldott"];
        $bug_severity = ["Kritikus", "Magas", "Közepes", "Alacsony", "Nagyon Alacsony", "Ismeretlen"];
        $faker = Faker\Factory::create("en_US");
        $n = random_int(40, 60);
        $users = [];
        for ($i = 0; $i < $n; ++$i) {
            array_push($users, [
                    "username" => $faker->userName,
                "password" => $faker->password,
                "email" => $faker->email,
                "phone" => (random_int(0,1))?"":$faker->phoneNumber
            ]);
        }
        $projects = [];
        $n = random_int(4, 6);
        for ($i = 0; $i < $n; ++$i) {
            $project = $faker->company;
            $m = random_int(1, 2);
            $assigns = [];
            for ($j = 0; $j < $m; ++$j) {
                $user = $users[random_int(0, count($users) - 1)]["username"];
                if (!in_array($user, $assigns))
                    array_push($assigns, $user);
            }
            $projects[$project]["assigns"] = $assigns;
            $m = random_int(8, 12);
            $categories = [];
            for ($j = 0; $j < $m; ++$j) {
                $o = random_int(1, 3);
                $assigns = [];
                for ($k = 0; $k < $o; ++$k) {
                    $user = $users[random_int(0, count($users) - 1)]["username"];
                    if (!in_array($user, $assigns))
                        array_push($assigns, $user);
                }
                $name = $faker->jobTitle;
                $categories[$name]["assigns"] = $assigns;
                $bugs = [];
                $o = random_int(50, 100);
                for ($k = 0; $k < $o; ++$k) {
                    $title = $faker->realText(32);
                    $text = $faker->realText(3000);
                    $creator = $users[random_int(0, count($users) - 1)]["username"];
                    $createDate = $faker->dateTimeBetween(new DateTime("2017-01-01"));
                    $assignee = "";
                    $assignRand = random_int(1, 5);
                    $status = "Nyitott";
                    $severity = "Ismeretlen";
                    
                    $updates = [];
                    if ($assignRand == 5) {
                        $p = random_int(1, 10);
                        for ($l = 0; $l < $p; ++$l) {
                            array_push($updates, [
                                "type" => "comment",
                                "author" => $users[random_int(0, count($users) - 1)]["username"],
                                "creation" => $faker->dateTimeBetween((empty($updates))?$createDate:$updates[count($updates) - 1]["creation"]),
                                "text" => $faker->realText(200)
                            ]);
                        }
                    } else {
                        $severity = $bug_severity[random_int(0, count($bug_severity) - 2)];
                        $assignee = (random_int(0, 2))?$projects[$project]["assigns"][random_int(0, count($projects[$project]["assigns"]) - 1)]:$categories[$name]["assigns"][random_int(0, count($categories[$name]["assigns"]) - 1)];
                        $severity = $bug_severity[random_int(0, count($bug_severity) - 2)];
                        array_push($updates, [
                            "type" => "status",
                            "creation" => $faker->dateTimeBetween($createDate),
                            "from" => "Nyitott",
                            "to" => "Hozzárendelt"
                        ]);
                        $status = "Hozzárendelt";
                        $p = random_int(20, 50);

                        for ($l = 0; $l < $p; ++$l) {
                            if ($status == "Lezárt") {
                                break;
                            }
                            $isstatus = random_int(1, 10);
                            if ($isstatus == 10) {
                                $latest = $status;
                                $status = $bug_statuses[random_int(1, count($bug_statuses) - 1)];
                                array_push($updates, [
                                    "type" => "status",
                                    "creation" => $faker->dateTimeBetween($updates[count($updates) - 1]["creation"]),
                                    "from" => $latest,
                                    "to" => $status
                                ]);
                            } else {
                                array_push($updates, [
                                    "type" => "comment",
                                    "author" => $users[random_int(0, count($users) - 1)]["username"],
                                    "creation" => $faker->dateTimeBetween($updates[count($updates) - 1]["creation"]),
                                    "text" => $faker->realText(200)
                                ]);
                            }
                        }
                    }

                    array_push($bugs, [
                            "creator" =>$creator,
                            "create_date" => $createDate,
                            "subject" => $title,
                            "content" => $text,
                            "updates" => $updates,
                            "assignee" => $assignee,
                            "severity" => $severity,
                    ]);
                }
                $categories[$name]["bugs"] = $bugs;
            }
            $projects[$project]["categories"] = $categories;
        }

        $counter = 0;

        foreach ($users as $user) {
            $query = $db->prepare("INSERT INTO `felhasznalo` (felhasznalonev, jelszo, email, telefon) VALUES (:user, :pass, :email, :phone)");
            $query->execute([
                ":user" => $user["username"],
                ":pass" => password_hash($user["password"], PASSWORD_DEFAULT),
                ":email" => $user["email"],
                ":phone" => (!empty($user["phone"]))?$user["phone"]:null
            ]);
            $counter++;
        }

        foreach ($projects as $project_name => $project) {
            $query = $db->prepare("INSERT INTO `projekt` (nev) VALUES (:name)");
            $query->execute([":name" => $project_name]);
            $counter++;
            foreach ($project["assigns"] as $assignee) {
                $query = $db->prepare("INSERT INTO `projektkezeles` (`projekt.nev`, `felhasznalo.felhasznalonev`) VALUES (:project_name, :username)");
                $query->execute([":project_name" => $project_name, ":username" => $assignee]);
                $counter++;
            }
            foreach ($project["categories"] as $category_name => $category) {
                $query = $db->prepare("INSERT INTO `kategoria` (`projekt.nev`, nev) VALUES (:project_name, :name)");
                $query->execute([":project_name" => $project_name, ":name" => $category_name]);
                $counter++;
                foreach ($category["assigns"] as $assignee) {
                    $query = $db->prepare("INSERT INTO `kategoriakezeles` (`projekt.nev`, `kategoria.nev`, `felhasznalo.felhasznalonev`) VALUES (:project_name, :category_name, :username)");
                    $query->execute([":project_name" => $project_name, ":category_name" => $category_name, ":username" => $assignee]);
                    $counter++;
                }
                foreach ($category["bugs"] as $bug) {
                    $query = $db->prepare("INSERT INTO bug (targy, leiras, sulyossag, `szerzo.felhasznalonev`, `hozzarendelt.felhasznalonev`, `projekt.nev`, `kategoria.nev`, keszites_idopont) 
                      VALUES (:title, :details, :severity, :author, :assignee, :project_name, :category_name, :create_date)");
                    $query->execute([
                            ":title" => $bug["subject"],
                            ":details" => $bug["content"],
                            ":severity" => $bug["severity"],
                            ":author" => $bug["creator"],
                            ":assignee" => $bug["assignee"],
                            ":project_name" => $project_name,
                            ":category_name" => $category_name,
                            ":create_date" => $bug["create_date"]->format('Y-m-d H:i:s'),
                    ]);
                    $query = $db->prepare("SELECT azonosito FROM bug ORDER BY azonosito DESC LIMIT 1");
                    $query->execute();
                    $bug["id"] = $query->fetch()[0];
                    $counter++;
                    foreach ($bug["updates"] as $update) {
                        if ($update["type"] == "comment") {
                            $query = $db->prepare("INSERT INTO hozzaszolas (idopont, `bug.azonosito`, tartalom, `felhasznalo.felhasznalonev`) VALUES (:create_date, :bug_id, :content, :author)");
                            $query->execute([
                                    ":create_date" => $update["creation"]->format('Y-m-d H:i:s'),
                                    ":bug_id" => $bug["id"],
                                    ":content" => $update["text"],
                                    ":author" => $update["author"]
                            ]);
                            $counter++;
                        }
                        elseif ($update["type"] == "status") {
                            $query = $db->prepare("INSERT INTO statuszfrissites (idopont, `bug.azonosito`, regi_statusz, uj_statusz) VALUES (:create_date, :bug_id, :old, :new)");
                            $query->execute([
                                ":create_date" => $update["creation"]->format('Y-m-d H:i:s'),
                                ":bug_id" => $bug["id"],
                                ":old" => $update["from"],
                                ":new" => $update["to"]
                            ]);
                            $counter++;
                        }
                    }
                }
            }
        }

        session_msg_place($counter  . " adat sikeresen hozzá lett adva a rendszerhez!");
        header("Location: projects.php");
    } elseif ($_POST["submit"] == "Összes adat törlése") {
        $db->query("DELETE FROM kategoriakezeles;");
        $db->query("DELETE FROM projektkezeles;");
        $db->query("DELETE FROM hozzaszolas;");
        $db->query("DELETE FROM statuszfrissites;");
        $db->query("DELETE FROM bug;");
        $db->query("DELETE FROM felhasznalo WHERE felhasznalonev != 'admin';");
        $db->query("DELETE FROM kategoria;");
        $db->query("DELETE FROM projekt;");
        header("Location: ./");
    }
}

include("layout/header.php");
include("layout/nav.php");
?>
    <div class="row justify-content-center">
        <div class="card mt-3 col-7 p-0">
            <div class="card-header">Mintaadatok felvétele</div>
            <div class="card-body">
                <div class="alert alert-danger">Figyelem! A gomb megnyomásával új, fiktív adatokkal lesz feltöltve a rendszer. Kérem ellenőrizze a megfelelő biztonsági mentések meglétét!</div>
                <form method="post">
                    <input type="submit" class="btn btn-primary" name="submit" value="Mintaadatok felvétele">
                </form>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="card mt-3 col-7 p-0">
            <div class="card-header">Összes adat törlése</div>
            <div class="card-body">
                <div class="alert alert-danger">Figyelem! A gomb megnyomásával az összes eddigi bevitt adatot törli a rendszerből.</div>
                <form method="post">
                    <input type="submit" class="btn btn-danger" name="submit" value="Összes adat törlése">
                </form>
            </div>
        </div>
    </div>
<?php
include("layout/footer.php");
