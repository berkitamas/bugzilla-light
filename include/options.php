<?php
$db_host = getenv("DB_HOST") ?? "localhost";
$db_user = getenv("DB_USER") ?? "root";
$db_pass = getenv("DB_PASSWORD") ?? "";
$db_db = getenv("DB_DATABASE") ?? "adatb";
$root_path = getenv("BASE_PATH") ?? "/";