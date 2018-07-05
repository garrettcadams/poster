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

        <?php if ($recaptcha_enabled): ?>
            <script src="https://www.google.com/recaptcha/api.js?hl=<?= active_lang("shortcode") ?>" async defer></script>
        <?php endif ?>

        <link rel="stylesheet" type="text/css" href="<?= active_theme("url")."/assets/css/plugins.css?v=".VERSION ?>">
        <link rel="stylesheet" type="text/css" href="<?= active_theme("url")."/assets/css/core.css?v=".VERSION ?>">

        <title><?= __("Signup") ?></title>
    </head>

    <body class="lightbg">
        <div id="signup" class="simpleform">
            <h1 class="title"><?= __("Sign up to <br> managing Instagram!") ?></h1>

            <form action="<?= APPURL."/signup" ?>" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="signup">
                <?php if ($Package->isAvailable()): ?>
                    <input type="hidden" name="package" value="<?= $Package->isAvailable() ? $Package->get("id") : "" ?>">
                <?php else: ?>
                    <input type="hidden" name="package" value="<?= htmlchars(Input::post("package")) ?>">
                <?php endif ?>

                <?php if (!empty($FormErrors)): ?>
                    <div class="form-result text-l mb-40">
                        <?php foreach ($FormErrors as $error): ?>
                            <div class="color-danger">
                                <span class="mdi mdi-close"></span>
                                <?= $error ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="fancy-field mb-20">
                    <input class="input js-required" 
                           id="firstname" 
                           name="firstname" 
                           type="text"
                           value="<?= htmlchars(Input::Post("firstname")) ?>">
                    <label for="firstname"><?= __('Firstname') ?></label>
                </div>

                <div class="fancy-field mb-20">
                    <input class="input js-required" 
                           id="lastname" 
                           name="lastname" 
                           type="text"
                           value="<?= htmlchars(Input::Post("lastname")) ?>">
                    <label for="lastname"><?= __('Lastname') ?></label>
                </div>
               

                <div class="fancy-field mb-20">
                    <input class="input js-required" 
                           id="email" 
                           name="email" 
                           type="text"
                           value="<?= htmlchars(Input::Post("email")) ?>">
                    <label for="email"><?= __("Email") ?></label>
                </div>

                <div class="fancy-field mb-20">
                    <input class="input js-required" 
                           id="password" 
                           name="password" 
                           type="password">
                    <label for="password"><?= __("Password") ?></label>
                </div>

                <div class="fancy-field mb-20">
                    <input class="input js-required" 
                           id="password-confirm" 
                           name="password-confirm" 
                           type="password">
                    <label for="password-confirm"><?= __("Confirm password") ?></label>
                </div>

                <div class="mb-40">
                    <select class="input js-required" name="timezone">
                        <option value=""><?= __("Select your timezone") ?></option>
                        <?php 
                            $tz = $IpInfo->timezone;
                            if (Input::post("timezone")) {
                                $tz = Input::post("timezone");
                            }
                        ?>
                        <?php foreach ($TimeZones as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $k == $tz ? "selected" : "" ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($recaptcha_enabled): ?>
                    <input type="hidden" name="recaptcha" value="1">
                    
                    <div class="mb-40 recaptcha">
                        <div class="g-recaptcha" 
                             data-sitekey="<?= htmlchars(get_option("np_recaptcha_site_key")) ?>"></div>

                        <div class="recaptcha-error"></div>
                    </div>
                <?php endif ?>

                <div>
                    <input class="fluid button button--oval" type="submit" value="<?= __("Get Started") ?>">
                </div>
            </form>

            <div class="sub">
                <div><?= __("By creating an acount you agree to our terms of service.") ?></div>
                <a href="<?= APPURL."/login".($Package->isAvailable() ? "?package=".$Package->get("id") : "") ?>"><?= __("Already have an account?") ?></a>
            </div>
        </div>
    
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript" charset="utf-8">
            $(function(){
                NPTheme.Signup();
            })
        </script>

        <?php require_once(APPPATH.'/views/fragments/google-analytics.fragment.php'); ?>
    </body>
</html>