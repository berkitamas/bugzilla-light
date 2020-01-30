<?php
define("PROTECT", true);
require_once "include/init.php";
require_once "include/phpgraphlib.php";
if (!empty($_GET["project"])) {
    $query = $db->prepare("SELECT COUNT(*) FROM projekt WHERE nev=:name");
    $query->execute([":name" => $_GET["project"]]);
    if ($query->fetch()[0]) {
        $query = $db->prepare("SELECT FLOOR(UNIX_TIMESTAMP(NOW()) / (60*60*24)) AS now_time, FLOOR(UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL -1 MONTH )) / (60*60*24)) AS then_time");
        $query->execute();
        $stat_boundaries = $query->fetch();
        $query = $db->prepare("SELECT `kategoria.nev`, FLOOR(UNIX_TIMESTAMP(keszites_idopont) / (60*60*24)) AS idopont, COUNT(*) AS count FROM bug WHERE `projekt.nev`=:name AND keszites_idopont > DATE_ADD(NOW(), INTERVAL -1 MONTH ) GROUP BY `kategoria.nev`, idopont");
        $query->execute([":name" => $_GET["project"]]);
        $result = $query->fetchAll();
        if (!empty($result)) {
            $categories = [];
            foreach ($result as $item) {
                if (!array_key_exists($item["kategoria.nev"], $categories)) {
                    $categories[$item["kategoria.nev"]] = [];
                    for ($i = 0; $i < $stat_boundaries["now_time"] - $stat_boundaries["then_time"]; ++$i) {
                        $categories[$item["kategoria.nev"]][$i] = 0;
                    }
                }
                $categories[$item["kategoria.nev"]][$item["idopont"] - $stat_boundaries["then_time"]] = (int) $item["count"];
            }

            $graph = new PHPGraphLib(900,300);
            call_user_func_array(array($graph, "addData"), array_values($categories));
            $colors = [];
            for ($i = 0; $i < count($categories); ++$i) {
                array_push($colors, sprintf("#%06X", rand(0, 2**24)));
            }
            call_user_func_array(array($graph, "setLineColor"), array_values($colors));
            $graph->setBars(false);
            $graph->setLine(true);
            $graph->setLegend(true);
            call_user_func_array(array($graph, "setLegendTitle"), array_keys($categories));
            $graph->createGraph();
        } else {
            header("Content-Type: image/png");
            echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="); // transparent
            die;
        }
        die;
    }
}
http_response_code(404);