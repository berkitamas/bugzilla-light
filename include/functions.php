<?php

function breadcrumb_add_level($name, $url) {
    global $breadcrumb;
    if (empty($breadcrumb)) $breadcrumb = [["Főoldal", "/"]];
    array_push($breadcrumb, [$name, $url]);
}

function breadcrumb_get() {
    global $breadcrumb;
    if (empty($breadcrumb)) {
        $breadcrumb = [["Főoldal", "/"]];
    }
    return $breadcrumb;
}

function session_msg_place($msg) {
    $_SESSION["sessmsg"] = $msg;
}

function session_msg_exists() {
    return !empty($_SESSION["sessmsg"]);
}

function session_msg_take() {
    $msg = (session_msg_exists())?$_SESSION["sessmsg"]:"";
    unset($_SESSION["sessmsg"]);
    return htmlspecialchars($msg);
}

function get_user() {
    if (!logged_in()) {
        return null;
    }
    return $_SESSION["user"];
}

function set_user_props($key, $value) {
    if (!logged_in()) {
        return null;
    }
    $_SESSION["user"][$key] = $value;
}

function is_admin($project = null, $category = null) {
    if (!logged_in()) return false;
    if (get_user()["super"]) return true;
    if (!empty($project) && !empty($category)) {
        if (in_array($project, get_user()["projektek"])) {
            return true;
        }
        elseif (!empty(get_user()["kategoriak"][$project])) {
            return in_array($category, get_user()["kategoriak"][$project]);
        } else {
            return false;
        }
    }
    elseif (!empty($project)) {
        return in_array($project, get_user()["projektek"]);
    } else {
        return false;
    }
}

function logged_in() {
    return !empty($_SESSION["user"]);
}