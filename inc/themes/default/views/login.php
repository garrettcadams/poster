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

        <title><?= __("Login") ?></title>
    </head>

    <body>
        <div id="login" class="twosidepage">
            <div class="leftbg"></div>
            <div class="rightbg"></div>

            <div class="leftside">
                <div class="innerwrp">
                    <form action="<?= APPURL."/login" ?>" method="POST" autocomplete="off">
                        <input type="hidden" name="action" value="login">

                        <?php if (Input::post("action") == "login"): ?>
                            <div class="form-result mb-40">
                                <div class="color-danger">
                                    <span class="mdi mdi-close"></span>
                                    <?= __("Login credentials didn't match!") ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if($Integrations->get("data.facebook.app_id") && $Integrations->get("data.facebook.app_secret")): ?>
                            <a class="fbloginbtn" href="javascript:void(0)">
                                <span class="mdi mdi-facebook-box"></span>
                                <?= __("Login with Facebook") ?>
                            </a>
                        <?php endif; ?>

                        <div class="fancy-field mb-20">
                            <input class="input" 
                                   id="username" 
                                   name="username" 
                                   type="text"
                                   value="<?= htmlchars(Input::Post("username")) ?>">
                            <label for="username"><?= __("Email address") ?></label>
                        </div>

                        <div class="fancy-field mb-10">
                            <input class="input" 
                                   id="password"
                                   name="password"
                                   type="password">
                            <label for="password"><?= __("Password") ?></label>
                        </div>

                        <div class="sub mb-20 clearfix">
                            <label>
                                <input class="checkbox" type="checkbox" name="remember" value="1" <?= Input::post("remember") ? "checked" :"" ?>>
                                <div>
                                    <span class="icon unchecked mdi mdi-checkbox-blank-outline"></span>
                                    <span class="icon checked mdi mdi-checkbox-marked"></span>

                                    <?= __("Remember me") ?>
                                </div>
                            </label>

                            <a href="<?= APPURL."/recovery" ?>"><?= __("Forgot your password?") ?></a>
                        </div>

                        <div>
                            <input type="submit" class="fluid button button--oval" value="<?= __('Sign in') ?>">
                        </div>
                    </form>
                </div>
            </div>


            <div class="rightside">
                <div class="innerwrp">
                    <h2 class="section-title"><?= __("Sign up") ?></h2>
                    <p class="subinfo"><?= __("It’s absolutely free and takes less than 30 seconds.") ?></p>

                    <a class="button button--oval button--outline" href="<?= APPURL."/signup" ?>"><?= __("Sign up") ?></a>
                </div>
            </div>
        </div>
    
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript" charset="utf-8">
            $(function(){
                <?php if($Integrations->get("data.facebook.app_id") && $Integrations->get("data.facebook.app_secret")): ?>
                    NPTheme.FacebookLogin('<?= htmlchars($Integrations->get("data.facebook.app_id")) ?>', '<?= APPURL."/login" ?>');
                <?php endif; ?>
            })
        </script>

        <?php require_once(APPPATH.'/views/fragments/google-analytics.fragment.php'); ?>
    </body>
</html>