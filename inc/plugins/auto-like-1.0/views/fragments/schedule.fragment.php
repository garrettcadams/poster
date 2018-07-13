<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>

<div class="skeleton skeleton--full" id="auto-like-schedule">
    <div class="clearfix">
        <aside class="skeleton-aside hide-on-medium-and-down">
            <div class="aside-list js-loadmore-content" data-loadmore-id="1"></div>

            <div class="loadmore pt-20 none">
                <a class="fluid button button--light-outline js-loadmore-btn js-autoloadmore-btn" data-loadmore-id="1" href="<?= APPURL."/e/".$idname."?aid=".$Account->get("id") ?>">
                    <span class="icon sli sli-refresh"></span>
                    <?= __("Load More") ?>
                </a>
            </div>
        </aside>

        <section class="skeleton-content">
            <form class="js-auto-like-schedule-form"
                  action="<?= APPURL."/e/".$idname."/".$Account->get("id") ?>"
                  method="POST">

                <input type="hidden" name="action" value="save">

                <div class="section-header clearfix">
                    <h2 class="section-title"><?= htmlchars($Account->get("username")) ?></h2>
                </div>

                <div class="section-content">
                    <div class="form-result"></div>

                    <div class="clearfix">
                        <div class="col s12 m10 l8">
                            <div class="mb-5 clearfix">
                                <label class="inline-block mr-50 mb-15">
                                    <input class="radio" name='type' type="radio" value="hashtag" checked>
                                    <span>
                                        <span class="icon"></span>
                                        #<?= __("Hashtags") ?>
                                    </span>
                                </label>

                                <label class="inline-block mr-50 mb-15">
                                    <input class="radio" name='type' type="radio" value="location">
                                    <span>
                                        <span class="icon"></span>
                                        <?= __("Places") ?>
                                    </span>
                                </label>

                                <label class="inline-block mb-15">
                                    <input class="radio" name='type' type="radio" value="people">
                                    <span>
                                        <span class="icon"></span>
                                        <?= __("People") ?>
                                    </span>
                                </label>
                            </div>

                            <div class="clearfix mb-20 pos-r">
                                <label class="form-label"><?= __('Search') ?></label>
                                <input class="input rightpad" name="search"  type="text" value="" data-url="<?= APPURL."/e/".$idname."/".$Account->get("id") ?>">
                                <span class="field-icon--right pe-none none js-search-loading-icon">
                                    <img src="<?= APPURL."/assets/img/round-loading.svg" ?>" alt="Loading icon">
                                </span>
                            </div>

                            <div class="tags clearfix mt-20 mb-20">
                                <?php 
                                    $targets = $Schedule->isAvailable()
                                             ? json_decode($Schedule->get("target")) 
                                             : []; 
                                    $icons = [
                                        "hashtag" => "mdi mdi-pound",
                                        "location" => "mdi mdi-map-marker",
                                        "people" => "mdi mdi-instagram"
                                    ];
                                ?>
                                <?php foreach ($targets as $t): ?>
                                    <span class="tag pull-left <?= $t->type == "hashtag" ? "" : "none" ?>"
                                          data-type="<?= htmlchars($t->type) ?>" 
                                          data-id="<?= htmlchars($t->id) ?>" 
                                          data-value="<?= htmlchars($t->value) ?>" 
                                          style="margin: 0px 2px 3px 0px;">
                                        <?php if (isset($icons[$t->type])): ?>
                                              <span class="<?= $icons[$t->type] ?>"></span>
                                          <?php endif ?>  

                                          <?= htmlchars($t->value) ?>
                                          <span class="mdi mdi-close remove"></span>
                                      </span>
                                <?php endforeach ?>
                            </div>


                            <div class="clearfix mb-40">
                                <div class="col s6 m6 l6">
                                    <label class="form-label"><?= __("Speed") ?></label>

                                    <select class="input" name="speed">
                                        <option value="0" <?= $Schedule->get("speed") == 0 ? "selected" : "" ?>><?= __("Auto"). " (".__("Recommended").")" ?></option>
                                        <option value="1" <?= $Schedule->get("speed") == 1 ? "selected" : "" ?>><?= __("Very slow") ?></option>
                                        <option value="2" <?= $Schedule->get("speed") == 2 ? "selected" : "" ?>><?= __("Slow") ?></option>
                                        <option value="3" <?= $Schedule->get("speed") == 3 ? "selected" : "" ?>><?= __("Medium") ?></option>
                                        <option value="4" <?= $Schedule->get("speed") == 4 ? "selected" : "" ?>><?= __("Fast") ?></option>
                                        <option value="5" <?= $Schedule->get("speed") == 5 ? "selected" : "" ?>><?= __("Very Fast") ?></option>
                                    </select>
                                </div>

                                <div class="col s6 s-last m6 m-last l6 l-last">
                                    <label class="form-label"><?= __("Status") ?></label>

                                    <select class="input" name="is_active">
                                        <option value="0" <?= $Schedule->get("is_active") == 0 ? "selected" : "" ?>><?= __("Deactive") ?></option>
                                        <option value="1" <?= $Schedule->get("is_active") == 1 ? "selected" : "" ?>><?= __("Active") ?></option>
                                    </select>
                                </div>
                            </div>


                            <div class="clearfix">
                                <div class="col s12 m6 l6">
                                    <input class="fluid button" type="submit" value="<?= __("Save") ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>