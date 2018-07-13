<?php 
namespace Plugins\AutoLike;
const IDNAME = "auto-like";

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?"); 


/**
 * Event: plugin.install
 */
function install($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

    $sql = "CREATE TABLE `".TABLE_PREFIX."auto_like_schedule` ( 
                `id` INT NOT NULL AUTO_INCREMENT , 
                `user_id` INT NOT NULL , 
                `account_id` INT NOT NULL , 
                `target` TEXT NOT NULL , 
                `speed` VARCHAR(20) NOT NULL , 
                `is_active` BOOLEAN NOT NULL , 
                `schedule_date` DATETIME NOT NULL , 
                `end_date` DATETIME NOT NULL , 
                `last_action_date` DATETIME NOT NULL , 
                PRIMARY KEY (`id`), 
                INDEX (`user_id`), 
                INDEX (`account_id`)
            ) ENGINE = InnoDB;";

    $sql .= "ALTER TABLE `".TABLE_PREFIX."auto_like_schedule` 
                ADD CONSTRAINT `alschdl_ibfk_1` FOREIGN KEY (`user_id`) 
                REFERENCES `".TABLE_PREFIX."users`(`id`) 
                ON DELETE CASCADE ON UPDATE CASCADE;";

    $sql .= "ALTER TABLE `".TABLE_PREFIX."auto_like_schedule` 
                ADD CONSTRAINT `alschdl_ibfk_2` FOREIGN KEY (`account_id`) 
                REFERENCES `".TABLE_PREFIX."accounts`(`id`) 
                ON DELETE CASCADE ON UPDATE CASCADE;";


    $sql .= "CREATE TABLE `".TABLE_PREFIX."auto_like_log` ( 
                `id` INT NOT NULL AUTO_INCREMENT , 
                `user_id` INT NOT NULL , 
                `account_id` INT NOT NULL , 
                `status` VARCHAR(20) NOT NULL,
                `data` TEXT NOT NULL , 
                `date` DATETIME NOT NULL , 
                PRIMARY KEY (`id`), 
                INDEX (`user_id`), 
                INDEX (`account_id`)
            ) ENGINE = InnoDB;";

    $sql .= "ALTER TABLE `".TABLE_PREFIX."auto_like_log` 
                ADD CONSTRAINT `allg_ibfk_1` FOREIGN KEY (`user_id`) 
                REFERENCES `".TABLE_PREFIX."users`(`id`) 
                ON DELETE CASCADE ON UPDATE CASCADE;";

    $sql .= "ALTER TABLE `".TABLE_PREFIX."auto_like_log` 
                ADD CONSTRAINT `allg_ibfk_2` FOREIGN KEY (`account_id`) 
                REFERENCES `".TABLE_PREFIX."accounts`(`id`) 
                ON DELETE CASCADE ON UPDATE CASCADE;";

    $pdo = \DB::pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
\Event::bind("plugin.install", __NAMESPACE__ . '\install');



/**
 * Event: plugin.remove
 */
function uninstall($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

    $sql = "DROP TABLE `".TABLE_PREFIX."auto_like_schedule`;";
    $sql .= "DROP TABLE `".TABLE_PREFIX."auto_like_log`;";

    $pdo = \DB::pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
\Event::bind("plugin.remove", __NAMESPACE__ . '\uninstall');


/**
 * Add module as a package options
 * Only users with granted permission
 * Will be able to use module
 * 
 * @param array $package_modules An array of currently active 
 *                               modules of the package
 */
function add_module_option($package_modules)
{
    $config = include __DIR__."/config.php";
    ?>
        <div class="mt-15">
            <label>
                <input type="checkbox" 
                       class="checkbox" 
                       name="modules[]" 
                       value="<?= IDNAME ?>" 
                       <?= in_array(IDNAME, $package_modules) ? "checked" : "" ?>>
                <span>
                    <span class="icon unchecked">
                        <span class="mdi mdi-check"></span>
                    </span>
                    <?= __('Auto Like') ?>
                </span>
            </label>
        </div>
    <?php
}
\Event::bind("package.add_module_option", __NAMESPACE__ . '\add_module_option');




/**
 * Map routes
 */
function route_maps($global_variable_name)
{
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/".IDNAME."/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/IndexController.php",
        __NAMESPACE__ . "\IndexController"
    ]);
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/".IDNAME."/[i:id]/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/ScheduleController.php",
        __NAMESPACE__ . "\ScheduleController"
    ]);
}
\Event::bind("router.map", __NAMESPACE__ . '\route_maps');



/**
 * Event: navigation.add_special_menu
 */
function navigation($Nav, $AuthUser)
{
    $idname = IDNAME;
    include __DIR__."/views/fragments/navigation.fragment.php";
}
\Event::bind("navigation.add_special_menu", __NAMESPACE__ . '\navigation');



/**
 * Add cron task to like new posts
 */
function addCronTask()
{
    // Get auto like schedules
    require_once __DIR__."/models/SchedulesModel.php";
    $Schedules = new SchedulesModel;
    $Schedules->where("is_active", "=", 1)
              ->where("schedule_date", "<=", date("Y-m-d H:i:s"))
              ->where("end_date", ">=", date("Y-m-d H:i:s"))
              ->orderBy("last_action_date", "ASC")
              ->setPageSize(10) // required to prevent server overload
              ->setPage(1)
              ->fetchData();

    if ($Schedules->getTotalCount() < 1) {
        // There is not any active schedule
        return false;
    }

    $as = [__DIR__."/models/ScheduleModel.php", __NAMESPACE__."\ScheduleModel"];
    foreach ($Schedules->getDataAs($as) as $sc) {
        // Set next schedule date
        $reqcount = [
            // speed => number of requests per hour
            "1" => 1, // Very Slow
            "2" => 2, // Slow
            "3" => 3, // Medium
            "4" => 4, // Fast
            "5" => 5  // Very Fast
        ];
        $speed = (int)$sc->get("speed");
        $delta = isset($reqcount[$speed]) && $reqcount[$speed] > 0 
               ? round(3600/$reqcount[$speed]) : rand(720, 7200);
        $next_schedule_timestamp = time() + $delta;
        $sc->set("schedule_date", date("Y-m-d H:i:s", $next_schedule_timestamp))
           ->set("last_action_date", date("Y-m-d H:i:s"))
           ->save();


        // Start data validation
        $Account = \Controller::model("Account", $sc->get("account_id"));
        if (!$Account->isAvailable() || $Account->get("login_required")) {
            // Account is either removed (unexected, external factors)
            // Or login required for this account
            // Deactivate schedule
            $sc->set("is_active", 0)->save();
            continue;
        }

        $User = \Controller::model("User", $sc->get("user_id"));
        if (!$User->isAvailable() || !$User->get("is_active") || $User->isExpired()) {
            // User is not valid
            // Deactivate schedule
            $sc->set("is_active", 0)->save();
            continue;
        }

        if ($User->get("id") != $Account->get("user_id")) {
            // Unexpected, data modified by external factors
            // Deactivate schedule
            $sc->set("is_active", 0)->save();
            continue;
        }

        $targets = @json_decode($sc->get("target"));
        if (!$targets) {
            // Unexpected, data modified by external factors
            // Deactivate schedule
            $sc->set("is_active", 0)->save();
            continue;
        }

        // Select random target from the defined target collection
        $i = rand(0, count($targets) - 1);
        $target = $targets[$i];

        if (empty($target->type) || empty($target->id) ||
            !in_array($target->type, ["hashtag", "location", "people"])) 
        {
            // Unexpected invalid target, 
            // data modified by external factors
            $sc->set("is_active", 0)->save();
            continue;   
        }

        // Create a action log (not save yet)
        require_once __DIR__."/models/LogModel.php";
        $Log = new LogModel;
        $Log->set("user_id", $User->get("id"))
            ->set("account_id", $Account->get("id"))
            ->set("status", "error");


        // Login into the account
        try {
            $Instagram = \InstagramController::login($Account);
        } catch (\Exception $e) {
            // Couldn't login into the account

            // Deactivate schedule
            $sc->set("is_active", 0)->save();

            // Log data
            $Log->set("data", $e->getMessage())
                ->save();

            continue;
        }


        // Logged in successfully
        // Now script will try to get feed and like new post
        // And will log result


        // Find username to follow
        $media_id = null;
        $code = null;
        $media_type = null;
        $media_thumb = null;
        
        if ($target->type == "hashtag") {
            try {
                $feed = $Instagram->hashtag->getFeed(str_replace("#", "", $target->id));
            } catch (\Exception $e) {
                // Couldn't get instagram feed related to the hashtag

                // Log data
                $Log->set("data", $e->getMessage())->save();
                continue;
            }

            if (isset($feed->items[0]->id)) {
                foreach ($feed->items as $item) {
                    if (!empty($item->id) && !$item->has_liked)  {
                        // Found the media to like
                        $media_id = $item->id;
                        $code = $item->code;
                        $media_type = $item->media_type;
                        if (isset($item->image_versions2->candidates[0]->url)) {
                            $media_thumb = $item->image_versions2->candidates[0]->url;
                        }
                        break;
                    }
                }
            }
        } else if ($target->type == "location") {
            try {
                $feed = $Instagram->location->getFeed($target->id);
            } catch (\Exception $e) {
                // Couldn't get instagram feed related to the location id

                // Log data
                $Log->set("data", $e->getMessage())->save();
                continue;
            }

            if (isset($feed->items[0]->id)) {
                foreach ($feed->items as $item) {
                    if (!empty($item->id) && !$item->has_liked)  {
                        // Found the media to like
                        $media_id = $item->id;
                        $code = $item->code;
                        $media_type = $item->media_type;
                        if (isset($item->image_versions2->candidates[0]->url)) {
                            $media_thumb = $item->image_versions2->candidates[0]->url;
                        }
                        break;
                    }
                }
            }
        } else if ($target->type == "people") {
            try {
                $feed = $Instagram->timeline->getUserFeed($target->id);
            } catch (\Exception $e) {
                // Couldn't get instagram feed related to the user id

                // Log data
                $Log->set("data", $e->getMessage())->save();
                continue;
            }

            $items = $feed->items;
            shuffle($items);

            foreach ($items as $item) {
                if (!empty($item->id) && !$item->has_liked)  {
                    // Found the media to like
                    $media_id = $item->id;
                    $code = $item->code;
                    $media_type = $item->media_type;
                    if (isset($item->image_versions2->candidates[0]->url)) {
                        $media_thumb = $item->image_versions2->candidates[0]->url;
                    }
                    break;
                }
            }
        }

        if (empty($media_id)) {
            $Log->set("data", "Couldn't find the new media to like")->save();
            continue;
        }


        // New media found
        // Like it!
        try {
            $res = $Instagram->media->like($media_id);
        } catch (\Exception $e) {
            $Log->set("data", $e->getMessage())->save();
            continue;
        }


        // Liked new media successfully
        // Save log
        $data = [
            "trigger" => $target,
            "liked" => [
                "media_id" => $media_id,
                "code" => $code,
                "media_type" => $media_type,
                "media_thumb" => $media_thumb
            ]
        ];

        $Log->set("status", isset($res->status) && $res->status == "ok" ?  "success" : "error")
            ->set("data", json_encode($data))
            ->save();
    }
}
\Event::bind("cron.add", __NAMESPACE__."\addCronTask");
