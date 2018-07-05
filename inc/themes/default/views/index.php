<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<!DOCTYPE html>
<html lang="<?= ACTIVE_LANG ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta name="theme-color" content="#fff">

        <meta name="description" content="<?= site_settings("site_description") ?>">
        <meta name="keywords" content="<?= site_settings("site_keywords") ?>">

        <link rel="icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">
        <link rel="shortcut icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">

        <link rel="stylesheet" type="text/css" href="<?= active_theme("url")."/assets/css/plugins.css?v=".VERSION ?>">
        <link rel="stylesheet" type="text/css" href="<?= active_theme("url")."/assets/css/core.css?v=".VERSION ?>">

        <title><?= site_settings("site_name") ?></title>
    </head>

    <body>
        <header>
            <div class="container-1100">
                <div class="row clearfix">
                    <a href="<?= APPURL ?>" class="header-logo">
                        <img src="<?= site_settings("logotype") ? site_settings("logotype") : APPURL."/assets/img/logo.png" ?>" 
                             alt="<?= site_settings("site_name") ?>">
                    </a>

                    <nav class="header-nav clearfix">
                        <ul class="clearfix">
                            <li><a href="<?= APPURL."/#about" ?>"><?= __("About") ?></a></li>
                            <li><a href="<?= APPURL."/#features" ?>"><?= __("Features") ?></a></li>
                            <li><a href="<?= APPURL."/#packages" ?>"><?= __("Prices") ?></a></li>
                        </ul>

                        <?php if ($AuthUser): ?>
                            <a class="link fw-600" href="<?= APPURL."/profile/" ?>"><?= __("Hi, %s", htmlchars($AuthUser->get("firstname"))) ?></a>
                        <?php else: ?>
                            <a class="link" href="<?= APPURL."/login" ?>"><?= __("Login") ?></a>
                            <a href="<?= APPURL."/signup" ?>" class="button button--small button--outline"><?= __("Sign up") ?></a>
                        <?php endif ?>
                    </nav>
                </div>
            </div>
        </header>



        <div class="cover">
            <div class="container-1100">
                <div class="row clearfix">
                    <h1 class="cover-title">
                        <?= __("A Smarter way to <br> Auto Post on Instagram") ?>    
                    </h1>

                    <p class="cover-summary">
                        <?= __("Save time managing your Instagram accounts, publish and analyze all your posts in one panel.") ?>
                    </p>

                    <a href="<?= APPURL."/signup" ?>" class="button button--oval"><?= __("Get started"); ?></a>

                    <?php if ($TrialPackage->get("data.size") > 0): ?>
                        <span class="trial">*
                            <?php 
                                $days = $TrialPackage->get("data.size"); 
                                if ($days > 45) {
                                    echo __("Free trial available");
                                } else {
                                    echo n__("%s day free trial", "%s days free trial", $days, $days);
                                }
                            ?>
                        </span>
                    <?php endif ?>
                </div>
            </div>
        </div>
        


        <div id="features" class="section">
            <div class="container-1100">
                <div class="row">
                    <h2 class="section-title text-c"><?= __("Here is features for you") ?></h2>

                    <div class="feature-list clearfix">
                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/1.png" ?>" alt="<?= __("Unique Design") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Unique Design") ?></span>
                            </div>
                        </div>

                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/2.png" ?>" alt="<?= __("Multi Accounts") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Multi Accounts") ?></span>
                            </div>
                        </div>

                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/3.png" ?>" alt="<?= __("Auto Post Content") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Auto Post Content") ?></span>
                            </div>
                        </div>

                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/4.png" ?>" alt="<?= __("Schedule Calendar") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Schedule Calendar") ?></span>
                            </div>
                        </div>

                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/5.png" ?>" alt="<?= __("Free Trial") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Free Trial") ?></span>
                            </div>
                        </div>

                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/6.png" ?>" alt="<?= __("Safe to use") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Safe to use") ?></span>
                            </div>
                        </div>

                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/7.png" ?>" alt="<?= __("Mobile Responsive") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Mobile Responsive") ?></span>
                            </div>
                        </div>

                        <div class="feature-list-item">
                            <div>
                                <div class="imgbox">
                                    <div>
                                        <img src="<?= active_theme("url") . "/assets/img/features/8.png" ?>" alt="<?= __("Could Import") ?>">
                                    </div>
                                </div>

                                <span class="label"><?= __("Could Import") ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    
        <?php 
            $currency = $Settings->get("data.currency");
            $iszdc = isZeroDecimalCurrency($currency);
        ?>
        <div id="packages" class="section">
            <div class="container-1100">
                <div class="row clearfix pos-r">
                    <h2 class="section-title text-c"><?= __("Relevant prices for you") ?></h2>

                    <div class="package-list clearfix">
                        <?php 
                            $show_modules = false;

                            $available_modules = [];
                            foreach ($Plugins->getDataAs("Plugin") as $p) {
                                $available_modules[] = $p->get("idname");
                            }

                            foreach ($Packages->getDataAs("Package") as $p) {
                                $package_modules = $p->get("settings.modules");
                                if ($package_modules &&
                                    is_array($package_modules) &&
                                    array_intersect($package_modules, $available_modules)) 
                                {
                                    $show_modules = true;
                                    break;
                                }
                            }
                        ?>

                        <?php foreach ($Packages->getDataAs("Package") as $p): ?>
                            <div class="package-list-item">
                                <div>
                                    <div class="price">
                                        <span class="number"><?= format_price($p->get("monthly_price"), $iszdc) ?></span>
                                        <span class="per">
                                            <?= htmlchars($currency) ?>/<?= __("per month") ?>    
                                        </span>       
                                    </div>

                                    <div class="title"><?= htmlchars($p->get("title")) ?></div>

                                    <div class="annual">
                                        <?= __("Annual Price") ?>:
                                        <?php 
                                            if ($p->get("annual_price") > 0) {
                                                $annual = $p->get("annual_price");
                                            } else {
                                                $annual = 12 * $p->get("monthly_price");
                                            }
                                        ?>
                                        <?= format_price($annual, $iszdc) ?>
                                        <?= htmlchars($currency) ?>

                                        <div class="save">
                                            <?php 
                                                if ($iszdc) {
                                                    $total = 12 * round($p->get("monthly_price"));
                                                    $dif = $total - round($annual);
                                                } else {
                                                    $total = 12 * $p->get("monthly_price");
                                                    $dif = $total - $annual; 
                                                }

                                                if ($dif > 0) {
                                                    echo __("You save %s", "<span>" . format_price($dif, $iszdc) ." ".htmlchars($currency)."</span>");
                                                }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="features">
                                        <div class="feature">
                                            <?php 
                                                $max = (int)$p->get("settings.max_accounts");
                                                if ($max > 0) {
                                                    echo n__("Only 1 account", "Up to %s accounts", $max, $max);
                                                } else if ($max == "-1") {
                                                    echo __("Unlimited accounts");
                                                } else {
                                                    echo __("Zero accounts");
                                                }
                                            ?>
                                        </div>

                                        <div class="feature">
                                            <div class="feature-title"><?= __("Post Types") ?>:</div>

                                            <div>
                                                <span class="<?= $p->get("settings.post_types.timeline_photo") ? "" : "not" ?>"><?= __("Photo") ?></span>, 
                                                <span class="<?= $p->get("settings.post_types.timeline_video") ? "" : "not" ?>"><?= __("Video") ?></span>, 

                                                <br>
                                                
                                                <?php 
                                                    $story_photo = $p->get("settings.post_types.story_photo");
                                                    $story_video = $p->get("settings.post_types.story_video");
                                                ?>
                                                <?php if ($story_photo && $story_video): ?>
                                                    <span><?= __("Story")." (".__("Photo+Video").")" ?></span>,
                                                <?php elseif ($story_photo): ?>
                                                    <span><?= __("Story")." (".__("Photo only").")" ?></span>,
                                                <?php elseif ($story_video): ?>
                                                    <span><?= __("Story")." (".__("Video only").")" ?></span>,
                                                <?php else: ?>
                                                    <span class="not"><?= __("Story")." (".__("Photo+Video").")" ?></span>,
                                                <?php endif ?>

                                                <br>

                                                <?php 
                                                    $album_photo = $p->get("settings.post_types.album_photo");
                                                    $album_video = $p->get("settings.post_types.album_video");
                                                ?>
                                                <?php if ($album_photo && $album_video): ?>
                                                    <span><?= __("Album")." (".__("Photo+Video").")" ?></span>
                                                <?php elseif ($album_photo): ?>
                                                    <span><?= __("Album")." (".__("Photo only").")" ?></span>
                                                <?php elseif ($album_video): ?>
                                                    <span><?= __("Album")." (".__("Video only").")" ?></span>
                                                <?php else: ?>
                                                    <span class="not"><?= __("Album")." (".__("Photo+Video").")" ?></span>
                                                <?php endif ?>
                                            </div>
                                        </div>

                                        <div class="feature">
                                            <div class="feature-title"><?= __("Cloud Import") ?>:</div>

                                            <div style="height: 35px;">
                                                <?php $none = true; ?>
                                                <?php if ($p->get("settings.file_pickers.google_drive")): ?>
                                                    <?php $none = false; ?>
                                                    <span class="icon m-5 mdi mdi-google-drive" title="Google Drive"></span>
                                                <?php endif ?>

                                                <?php if ($p->get("settings.file_pickers.dropbox")): ?>
                                                    <?php $none = false; ?>
                                                    <span class="icon m-5 mdi mdi-dropbox" title="Dropbox"></span>
                                                <?php endif ?>

                                                <?php if ($p->get("settings.file_pickers.onedrive")): ?>
                                                    <?php $none = false; ?>
                                                    <span class="icon m-5 mdi mdi-onedrive" title="OneDrive"></span>
                                                <?php endif ?>

                                                <?php if ($none): ?>
                                                    <span class="m-5 inline-block" style="line-height: 24px;"><?= __("Not available") ?></span>
                                                <?php endif ?>
                                            </div>
                                        </div>

                                        <?php if ($show_modules): ?>
                                            <div class="feature">
                                                <div class="feature-title"><?= __("Modules") ?></div>
                                                <div class="modules clearfix">
                                                    <?php $package_modules = $p->get("settings.modules") ?>
                                                    <?php foreach ($Plugins->getDataAs("Plugin") as $m): ?>
                                                        <?php 
                                                            $idname = $m->get("idname");

                                                            $config_path = PLUGINS_PATH . "/" . $idname . "/config.php"; 
                                                            if (!file_exists($config_path)) {
                                                                continue;
                                                            }
                                                            $config = include $config_path;
                                                            $name = empty($config["plugin_name"]) ? $idname : $config["plugin_name"];
                                                        ?>
                                                        <span class="module tooltip tippy <?= in_array($m->get("idname"), $package_modules) ? "" : "disabled" ?>"
                                                              style="<?= empty($config["icon_style"]) ? "" : $config["icon_style"] ?>"
                                                              title="<?= htmlchars($name) ?>"
                                                              data-size="small">
                                                                
                                                            <?php if (in_array($m->get("idname"), ["auto-follow", "auto-unfollow"])): ?>
                                                                <?php 
                                                                    $name = empty($config["plugin_name"]) ? $idname : $config["plugin_name"];
                                                                    echo textInitials($name, 2);
                                                                ?>
                                                            <?php elseif($m->get("idname") == "auto-like") : ?>
                                                                <span class="mdi mdi-heart"></span>
                                                            <?php elseif($m->get("idname") == "auto-comment") : ?>
                                                                <span class="mdi mdi-comment-processing"></span>
                                                            <?php elseif($m->get("idname") == "welcomedm") : ?>
                                                                <span class="sli sli-paper-plane"></span>
                                                            <?php elseif($m->get("idname") == "auto-repost") : ?>
                                                                <span class="sli sli-reload"></span>
                                                            <?php endif ?>
                                                        </span>
                                                    <?php endforeach ?>
                                                </div>
                                            </div>
                                        <?php endif ?>

                                        <div class="feature">
                                            <span class="<?= $p->get("settings.spintax") ? "" : "not" ?>"><?= __("Spintax Support") ?></span>
                                        </div>

                                        <div class="feature">
                                            <?= __("Storage") ?>:
                                            <span class="color-primary fw-500">
                                                <?= readableFileSize($p->get("settings.storage.total") * 1024 * 1024) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="choose">
                                        <a href="<?= APPURL."/".($AuthUser ? "renew" : "signup")."?package=".$p->get("id") ?>" class="button button--dark button--oval"><?= __("Get started") ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>



        <footer>
            <div class="container-1100">
                <div class="row clearfix">
                    <a href="<?= APPURL ?>" class="footer-logo">
                        <img src="<?= site_settings("logotype") ? site_settings("logotype") : APPURL."/assets/img/logo.png" ?>" 
                             alt="<?= site_settings("site_name") ?>">
                    </a>

                    <?php if (!$AuthUser): ?>
                        <select class="footer-lang-selector" data-base="<?= APPURL ?>">
                            <?php foreach (Config::get("applangs") as $l): ?>
                                <option value="<?= $l["code"] ?>" <?= $l["code"] == ACTIVE_LANG ? "selected" : "" ?>><?= $l["localname"] ?></option>
                            <?php endforeach ?>
                        </select>
                    <?php endif; ?>

                    <div class="copyright">
                        &copy; <?= __("Copyright")." ".date("Y") ?> 
                    </div>
                </div>
            </div>
        </footer>
        
        
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript" charset="utf-8">
            $(function(){
            })
        </script>
        <?php require_once(APPPATH.'/views/fragments/google-analytics.fragment.php'); ?>
    </body>
</html>