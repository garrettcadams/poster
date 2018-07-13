<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>

<div class="skeleton skeleton--full" id="auto-like-all">
    <div class="clearfix">
        <aside class="skeleton-aside">
            <?php if ($Accounts->getTotalCount() > 0): ?>
                <?php $active_item_id = Input::get("aid"); ?>
                <div class="aside-list js-loadmore-content" data-loadmore-id="1">
                    <?php foreach ($Accounts->getDataAs("Account") as $a): ?>
                        <div class="aside-list-item js-list-item <?= $active_item_id == $a->get("id") ? "active" : "" ?>">
                            <div class="clearfix">
                                <?php $title = htmlchars($a->get("username")); ?>
                                <span class="circle">
                                    <span><?= textInitials($title, 2); ?></span>
                                </span>

                                <div class="inner">
                                    <div class="title"><?= $title ?></div>
                                    <div class="sub"><?= __("Instagram user") ?></div>
                                </div>

                                <a class="full-link js-ajaxload-content" href="<?= APPURL."/e/".$idname."/".$a->get("id") ?>"></a>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

                <?php if($Accounts->getPage() < $Accounts->getPageCount()): ?>
                    <div class="loadmore mt-20">
                        <?php 
                            $url = parse_url($_SERVER["REQUEST_URI"]);
                            $path = $url["path"];
                            if(isset($url["query"])){
                                $qs = parse_str($url["query"], $qsarray);
                                unset($qsarray["page"]);

                                $url = $path."?".(count($qsarray) > 0 ? http_build_query($qsarray)."&" : "")."page=";
                            }else{
                                $url = $path."?page=";
                            }
                        ?>
                        <a class="fluid button button--light-outline js-loadmore-btn" data-loadmore-id="1" href="<?= $url.($Accounts->getPage()+1) ?>">
                            <span class="icon sli sli-refresh"></span>
                            <?= __("Load More") ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    <?php if ($AuthUser->get("settings.max_accounts") == -1 || $AuthUser->get("settings.max_accounts") > 0): ?>
                        <p><?= __("You haven't add any Instagram account yet. Click the button below to add your first account.") ?></p>
                        <a class="small button" href="<?= APPURL."/accounts/new" ?>">
                            <span class="sli sli-user-follow"></span>
                            <?= __("New Account") ?>
                        </a>
                    <?php else: ?>
                        <p><?= __("You don't have a permission to add any Instagram account.") ?></p>
                    <?php endif; ?>
                </div>
            <?php endif ?>
        </aside>

        <section class="skeleton-content hide-on-medium-and-down">
            <div class="no-data">
                <span class="no-data-icon sli sli-social-instagram"></span>
                <p><?= __("Please select an account from left side list.") ?></p>
            </div>
        </section>
    </div>
</div>