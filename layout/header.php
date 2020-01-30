<?php
if (!defined("PROTECT")) {
    header("Location: ./");
    die;
}
?><!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$root_path?>assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=$root_path?>assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?=$root_path?>assets/img/favicon/site.webmanifest">
    <link rel="mask-icon" href="<?=$root_path?>assets/img/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="<?=$root_path?>assets/img/favicon/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="BugZilla Light">
    <meta name="application-name" content="BugZilla Light">
    <meta name="msapplication-TileColor" content="#3498db">
    <meta name="msapplication-config" content="<?=$root_path?>assets/img/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <title>BugZilla Light</title>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.2/css/all.css" integrity="sha384-/rXc/GQVaYpyDdyxK+ecHPVYJSN9bmVFBvjA/9eOB+pb3F2w2N6fc5qB9Ew5yIns" crossorigin="anonymous">
    <link rel="stylesheet" href="<?=$root_path?>assets/css/styles.css">
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover()
        })
    </script>
</head>
<body class="bg-light">