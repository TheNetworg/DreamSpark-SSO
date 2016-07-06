<?php
$post_logout_redirect_uri = $_GET['post_logout_redirect_uri'];

$app->redirect($post_logout_redirect_uri);
