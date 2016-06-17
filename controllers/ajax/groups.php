<?php
$query = isset($_GET["q"]) ? $_GET["q"] : "";
$results = API::searchGroupByName($query);
$app->render(200, $results);