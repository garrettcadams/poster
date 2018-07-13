<?php 
    if (!defined('APP_VERSION')) 
        die("Yo, what's up?");  

    if (!isset($GLOBALS["_PLUGINS_"][$idname]["config"]))
          return null;

    $config = $GLOBALS["_PLUGINS_"][$idname]["config"]; 
    $user_modules = $AuthUser->get("settings.modules");
    if (empty($user_modules)) {
        $user_modules = [];
    }

    if (!in_array($idname, $user_modules)) {
        return null;
    }
?>
<li class="<?= $Nav->activeMenu == $idname ? "active" : "" ?>">
    <a href="<?= APPURL."/e/".$idname ?>">
        <span class="special-menu-icon" style="<?= empty($config["icon_style"]) ? "" : $config["icon_style"] ?>">
            <span class="mdi mdi-heart"></span>
        </span>

        <?php $name = empty($config["plugin_name"]) ? $idname : $config["plugin_name"]; ?>
        <span class="label"><?= $name ?></span>

        <span class="tooltip tippy" 
              data-position="right"
              data-delay="100" 
              data-arrow="true"
              data-distance="-1"
              title="<?= $name ?>"></span>
    </a>
</li>