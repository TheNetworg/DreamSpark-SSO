<?php
class baseHTML {
    static function header($type = []) {
        ?>
        <!DOCTYPE HTML>
        <html>
        <head>
            <title>DreamSpark SSO</title>
    
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <link rel="shortcut icon" href="/assets/img/favicon.png" type="image/png" />
            
            <?php $cache = json_decode(file_get_contents('assets/cache/cache.json')); ?>
            <link rel="stylesheet" type="text/css" href="/assets/cache/<?=$cache->css?>">
            <script type="text/javascript" src="/assets/cache/<?=$cache->js?>"></script>
        </head>
        <body>
            <div id="header"></div>
        <?php
    }
    static function footer() {
        $me = API::me();
        ?>
            <script>
                $(document).ready(function() {
                    <?php if($me) { ?>
                    DreamSparkSSO.User = {
                        accountName: "<?=$me['userPrincipalName']?>",
                        displayName: "<?=$me['displayName']?>",
                        imgSrc: "<?=(isset($me['thumbnailPhoto']) ? $me['thumbnailPhoto'] : "")?>"
                    };
                    <?php } ?>
                    DreamSparkSSO.AppLauncher.Load("#header");
                });
            </script>
        </body>
        </html>
        <?php
    }
    static function navBar() {
        global $app;
        $requestPath = $app->request->getResourceUri();
        ?>
        <div class="ms-NavBar">
            <div class="ms-NavBar-openMenu js-openMenu">
                <i class="ms-Icon ms-Icon--menu"></i>
            </div>
            <ul class="ms-NavBar-items">
                <li class="ms-NavBar-item"><a class="ms-NavBar-link" href="/SSOPassThrough">Continue to DreamSpark</a></li>
                <li class="ms-NavBar-item <?=($requestPath == "/settings" ? "is-selected" : "")?>"><a class="ms-NavBar-link" href="/settings">Home</a></li>
                <li class="ms-NavBar-item <?=($requestPath == "/settings/webstore" ? "is-selected" : "")?>"><a class="ms-NavBar-link" href="/settings/webstore">Webstore</a></li>
                <li class="ms-NavBar-item <?=($requestPath == "/settings/permissions" ? "is-selected" : "")?>"><a class="ms-NavBar-link" href="/settings/permissions">Permissions</a></li>
                <li class="ms-NavBar-item <?=($requestPath == "/settings/organization" ? "is-selected" : "")?>"><a class="ms-NavBar-link" href="/settings/organization">Organization</a></li>
                <li class="ms-NavBar-item ms-NavBar-item--right"><a class="ms-NavBar-link" href="https://go.thenetw.org/dreamsparksso"><i class="ms-Icon ms-Icon--question"></i>Help</a></li>
            </ul>
        </div>
        <script>
            $(document).ready(function() {
                $('.ms-NavBar').NavBar();
            });
        </script>
        <?php
    }
}
?>