<?php
define("PROTECT", true);
require_once "include/init.php";
include("layout/header.php");
include("layout/nav.php");
?>
<div class="jumbotron">
    <h1 class="display-4">BugZilla Light</h1>
    <p class="lead">Ez az oldal bugok menedzselését hivatott megoldani. Több projekt egyidejű kezelésére is alkalmas.</p>
    <hr class="my-4">
    <p>Ez az oldal a Szegedi Tudományegyetem Informatikai Intézete által szervezett Adatbázisok kurzushoz készült beadandó.</p>
    <p class="lead">
        <a class="btn btn-primary btn-lg" href="https://www.inf.u-szeged.hu/~gnemeth/kurzusok/adatbazisok.html" role="button">Kurzus oldala</a>
    </p>
</div>
<?php
include("layout/footer.php");