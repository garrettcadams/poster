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

        <title><?= __("Password Recovery") ?></title>
    </head>

    <body class="lightbg">
        <div id="recovery">
            <?php if (empty($success)): ?>
                <div class="simpleform">
                    <h1 class="title"><?= __("Password Recovery") ?></h1>

                    <form action="<?= APPURL."/recovery" ?>" method="POST" autocomplete="off">
                            <input type="hidden" name="action" value="recover">

                            <?php if (!empty($error)): ?>
                                <div class="form-result mb-40">
                                    <div class="color-danger">
                                        <span class="mdi mdi-close"></span>
                                        <?= $error ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="fancy-field mb-20">
                                <input class="input js-required" 
                                       id="email" 
                                       name="email" 
                                       type="text"
                                       value="<?= htmlchars(Input::Post("email")) ?>">
                                <label for="email"><?= __('Your e-mail address') ?></label>
                            </div>

                            <div>
                                <input class="fluid button button--oval" type="submit" value="<?= __("Submit") ?>">
                            </div>
                    </form>

                    <div class="sub">
                        <div><?= __("Enter your registration email address to receive password reset instructions.") ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="minipage">
                    <div class="inner">
                        <span class="icon">
                            <span class="mdi mdi-check color-success"></span>
                        </span>

                        <h1 class="minipage-title"><?= __('Email Sent!') ?></h1>
                        <p><?= __('Password reset instruction sent to your email address.') ?></p>
                    </div>
                </div>
            <?php endif ?>
        </div>
    
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <script type="text/javascript" src="<?= active_theme("url")."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript" charset="utf-8">
            $(function(){
            })
        </script>

        <?php require_once(APPPATH.'/views/fragments/google-analytics.fragment.php'); ?>
    </body>
</html>