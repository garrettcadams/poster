<?php
/**
 * Account Controller
 */
class AccountController extends Controller
{
    /**
     * Process
     */
    public function process()
    {
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");
        $EmailSettings = \Controller::model("GeneralData", "email-settings");

        // Auth
        if (!$AuthUser){
            header("Location: ".APPURL."/login");
            exit;
        } else if (
            !$AuthUser->isAdmin() && 
            !$AuthUser->isEmailVerified() &&
            $EmailSettings->get("data.email_verification")) 
        {
            header("Location: ".APPURL."/profile?a=true");
            exit;
        } else if ($AuthUser->isExpired()) {
            header("Location: ".APPURL."/expired");
            exit;
        }


        // Get accounts
        $Accounts = Controller::model("Accounts");
            $Accounts->setPage(Input::get("page"))
                     ->where("user_id", "=", $AuthUser->get("id"))
                     ->fetchData();

        // Account
        if (isset($Route->params->id)) {
            $Account = Controller::model("Account", $Route->params->id);
            if (!$Account->isAvailable() || 
                $Account->get("user_id") != $AuthUser->get("id")) 
            {
                header("Location: ".APPURL."/accounts");
                exit;
            }
        } else {
            $max_accounts = $AuthUser->get("settings.max_accounts");
            if ($Accounts->getTotalCount() >= $max_accounts && $max_accounts != "-1") {
                // Max. limit exceeds
                header("Location: ".APPURL."/accounts");
                exit;
            }

            $Account = Controller::model("Account"); // new account model
        }


        // Set view variables
        $this->setVariable("Accounts", $Accounts)
             ->setVariable("Account", $Account)
             ->setVariable("Settings", Controller::model("GeneralData", "settings"));


        if (Input::post("action") == "save") {
            $this->save();
        } else if (Input::post("action") == "2fa") {
            $this->twofa();
        } else if (Input::post("action") == "resend-2fa") {
            $this->resend2FA();
        } else if (Input::post("action") == "checkpoint") {
            $this->checkpoint();
        }

        $this->view("account");
    }


    /**
     * Save (new|edit)
     * @return void 
     */
    private function save()
    {
        $this->resp->result = 0;

        $AuthUser = $this->getVariable("AuthUser");
        $Account = $this->getVariable("Account");
        $Settings = $this->getVariable("Settings");
        $IpInfo = $this->getVariable("IpInfo");

        // Check if this is new or not
        $is_new = !$Account->isAvailable();

        $username = strtolower(Input::post("username"));
        $password = Input::post("password");

        // Check required data
        if (!$username || !$password) {
            $this->resp->msg = __("Missing some of required data.");
            $this->jsonecho();
        }

        // Check username syntax
        if (!preg_match("/^([a-z0-9_][a-z0-9_\.]{1,28}[a-z0-9_])$/", $username)) {
            $this->resp->msg = __("Please include a valid username.");
            $this->jsonecho();
        }

        // Check username
        $check_username = true;
        if ($Account->isAvailable() && $Account->get("username") == $username) {
            $check_username = false;
        }

        if ($check_username) {
            foreach ($this->getVariable("Accounts")->getData() as $a) {
                if ($a->username == $username) {
                    // This account is already exists (for the current user)
                    $this->resp->msg = __("Account is already exists!");
                    $this->jsonecho();
                    break;
                }
            }
        }


        // Check proxy
        $proxy = null;
        $is_system_proxy = false;
        if ($Settings->get("data.proxy")) {
            if (Input::post("proxy") && $Settings->get("data.user_proxy")) {
                $proxy = Input::post("proxy");

                if (!isValidProxy($proxy)) {
                    $this->resp->msg = __("Proxy is not valid or active!");
                    $this->jsonecho();
                }
            } else {
                $user_country = !empty($IpInfo->countryCode) 
                              ? $IpInfo->countryCode : null;
                $countries = [];
                if (!empty($IpInfo->neighbours)) {
                    $countries = $IpInfo->neighbours;
                }
                array_unshift($countries, $user_country);
                $proxy = ProxiesModel::getBestProxy($countries);
                $is_system_proxy = true;
            }
        }


        // Remove previous session folder to make guarantee full relogin
        if (file_exists(SESSIONS_PATH."/".$AuthUser->get("id")."/".$username)) {
            @delete(SESSIONS_PATH."/".$AuthUser->get("id")."/".$username);
        }   


        // Encrypt the password
        try {
            $passhash = Defuse\Crypto\Crypto::encrypt($password, 
                        Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY));
        } catch (\Exception $e) {
            $this->resp->msg = __("Encryption error");
            $this->jsonecho();
        }


        // Setup Instagram Client
        // Allow web usage
        // Since mentioned risks has been consider internally by Nextpost,
        // setting this property value to the true is not risky as it's name
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        
        $storageConfig = [
            "storage" => "file",
            "basefolder" => SESSIONS_PATH."/".$AuthUser->get("id")."/",
        ];

        $Instagram = new \InstagramAPI\Instagram(false, false, $storageConfig);
        $Instagram->setVerifySSL(SSL_ENABLED);

        if ($proxy) {
            $Instagram->setProxy($proxy);
        }
        
        $logged_in = false;
        try {
            $login_resp = $Instagram->login($username, $password);

            if ($login_resp !== null && $login_resp->isTwoFactorRequired()) {
                $this->resp->result = 2;
                $this->resp->twofa_required = true;
                $this->resp->msg = __(
                    "Enter the code sent to your number ending in %s", 
                    $login_resp->getTwoFactorInfo()->getObfuscatedPhoneNumber());
                $this->resp->identifier = $login_resp->getTwoFactorInfo()->getTwoFactorIdentifier();

                $_SESSION["2FA_".$this->resp->identifier] = [
                    "username" => $username,
                    "passhash" => $passhash,
                    "proxy" => $proxy
                ];
            } else if ($login_resp) {
                $logged_in = true;
            }
        } catch (InstagramAPI\Exception\CheckpointRequiredException $e) {
            $this->_handleCheckpointException($username, $password, $passhash, $proxy);
        } catch (InstagramAPI\Exception\ChallengeRequiredException $e) {
            $this->_handleCheckpointException($username, $password, $passhash, $proxy);
        } catch (InstagramAPI\Exception\AccountDisabledException $e) {
            $this->resp->msg = __(
                "Your account has been disabled for violating Instagram terms. <a href='%s'>Click here</a> to learn how you may be able to restore your account.", 
                "https://help.instagram.com/366993040048856");
        } catch (InstagramAPI\Exception\SentryBlockException $e) {
            $this->resp->msg = __("Your account has been banned from Instagram API for spam behaviour or otherwise abusing.");
        } catch (InstagramAPI\Exception\IncorrectPasswordException $e) {
            $this->resp->msg = __("The password you entered is incorrect. Please try again.");
        } catch (InstagramAPI\Exception\InvalidUserException $e) {
            $this->resp->msg = __("The username you entered doesn't appear to belong to an account. Please check your username and try again.");
        } catch (InstagramAPI\Exception\InstagramException $e) {
            if ($e->hasResponse()) {
                $msg = $e->getResponse()->getMessage();
            } else {
                $msg = explode(":", $e->getMessage(), 2);
                $msg = end($msg);
            }
            $this->resp->msg = $msg;
        } catch (\Exception $e) {
            $this->resp->msg = __("Oops! Something went wrong. Please try again later!");
        }

        if (!$logged_in) {
            // Not logged in
            // Either an error occured or 2FA login required or 
            // checkpoint required 

            if (!$is_new) {
                // Account is not new
                // Since new attempt to login has been made,
                // Account must be marked as not-logged-in for now
                $Account->set("login_required", 1)->save();
            }

            // Output result
            $this->jsonecho();
        }



        // Logged in successfully
        // Process and save data
        $Account->set("user_id", $AuthUser->get("id"))
                ->set("password", $passhash)
                ->set("proxy", $proxy ? $proxy : "")
                ->set("login_required", 0)
                ->set("instagram_id", $login_resp->getLoggedInUser()->getPk())
                ->set("username", $login_resp->getLoggedInUser()->getUsername())
                ->set("login_required", 0)
                ->save();


        // Update proxy use count
        if ($proxy && $is_system_proxy == true) {
            $Proxy = Controller::model("Proxy", $proxy);
            if ($Proxy->isAvailable()) {
                $Proxy->set("use_count", $Proxy->get("use_count") + 1)
                      ->save();
            }
        }


        $this->resp->result = 1;
        if ($is_new) {
            $this->resp->redirect = APPURL."/accounts";
        } else {
            $this->resp->changes_saved = true;
            $this->resp->msg = __("Changes saved!");
        }
        $this->jsonecho();
    }


    /**
     * Finish 2FA
     * @return void 
     */
    protected function twofa()
    {
        $this->resp->result = 0;

        $AuthUser = $this->getVariable("AuthUser");
        $Account = $this->getVariable("Account");

        // Check if this is new or not
        $is_new = !$Account->isAvailable();

        $security_code = Input::post("twofa-security-code");
        $twofaid = Input::post("2faid");

        if (!isset($_SESSION["2FA_".$twofaid]["username"], $_SESSION["2FA_".$twofaid]["passhash"])) {
            $this->resp->msg = __("Oops! Something went wrong. Please try again later!");
            $this->resp->error_code = "account_invalid_identifier";
            $this->jsonecho();
        }

        // These variables have been saved to the session after validation
        // There is no need to validate them again here.
        $username = $_SESSION["2FA_".$twofaid]["username"];
        $passhash = $_SESSION["2FA_".$twofaid]["passhash"];
        $proxy = empty($_SESSION["2FA_".$twofaid]["proxy"]) 
               ? null : $_SESSION["2FA_".$twofaid]["proxy"];


        // Decrypt the password hash
        try {
            $password = \Defuse\Crypto\Crypto::decrypt($passhash, 
                        \Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY));
        } catch (\Exception $e) {
            $this->resp->msg = __("Encryption error");
            $this->jsonecho();
        }


        // Setup Instagram Client
        // Allow web usage
        // Since mentioned risks has been consider internally by Nextpost,
        // setting this property value to the true is not risky as it's name
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        
        $storageConfig = [
            "storage" => "file",
            "basefolder" => SESSIONS_PATH."/".$AuthUser->get("id")."/",
        ];

        $Instagram = new \InstagramAPI\Instagram(false, false, $storageConfig);
        $Instagram->setVerifySSL(SSL_ENABLED);

        if ($proxy) {
            $Instagram->setProxy($proxy);
        }

        try {
            $resp = $Instagram->finishTwoFactorLogin($username, $password, $twofaid, $security_code);
        } catch (InstagramAPI\Exception\CheckpointRequiredException $e) {
            $this->_handleCheckpointException($username, $password, $passhash, $proxy);
            $this->jsonecho();
        } catch (InstagramAPI\Exception\ChallengeRequiredException $e) {
            $this->_handleCheckpointException($username, $password, $passhash, $proxy);
            $this->jsonecho();
        } catch (InstagramAPI\Exception\AccountDisabledException $e) {
            $this->resp->msg = __(
                "Your account has been disabled for violating Instagram terms. <a href='%s'>Click here</a> to learn how you may be able to restore your account.", 
                "https://help.instagram.com/366993040048856");
            $this->jsonecho();
        } catch (InstagramAPI\Exception\SentryBlockException $e) {
            $this->resp->msg = __("Your account has been banned from Instagram API for spam behaviour or otherwise abusing.");
            $this->jsonecho();
        } catch (InstagramAPI\Exception\InvalidSmsCodeException $e) {
            $this->resp->msg = __("Please check the security code sent you and try again.");
            $this->jsonecho();
        } catch (InstagramAPI\Exception\InstagramException $e) {
            if ($e->hasResponse()) {
                $msg = $e->getResponse()->getMessage();
            } else {
                $msg = explode(":", $e->getMessage(), 2);
                $msg = end($msg);
            }
            $this->resp->msg = $msg;
            $this->jsonecho();
        } catch (\Exception $e) {
            $this->resp->msg = __("Oops! Something went wrong. Please try again later!");
            $this->jsonecho();
        }

        $Account->set("user_id", $AuthUser->get("id"))
                ->set("password", $passhash)
                ->set("proxy", $proxy ? $proxy : "")
                ->set("login_required", 0)
                ->set("instagram_id", $resp->getLoggedInUser()->getPk())
                ->set("username", $resp->getLoggedInUser()->getUsername())
                ->set("login_required", 0)
                ->save();


        // Update proxy use count
        if ($proxy && $is_system_proxy == true) {
            $Proxy = Controller::model("Proxy", $proxy);
            if ($Proxy->isAvailable()) {
                $Proxy->set("use_count", $Proxy->get("use_count") + 1)
                      ->save();
            }
        }

        $this->resp->result = 1;
        if ($is_new) {
            $this->resp->redirect = APPURL."/accounts";
        } else {
            $this->resp->changes_saved = true;
            $this->resp->msg = __("Changes saved!");
        }
        $this->jsonecho();
    }


    /**
     * Resend the same SMS code for the 2FA login
     * @return void 
     */
    protected function resend2FA()
    {
        $this->resp->result = 0;

        $AuthUser = $this->getVariable("AuthUser");
        $Account = $this->getVariable("Account");

        $twofaid = Input::post("id");

        if (!isset($_SESSION["2FA_".$twofaid]["username"], $_SESSION["2FA_".$twofaid]["passhash"])) {
            $this->resp->msg = __("Oops! Something went wrong. Please try again later!");
            $this->resp->error_code = "account_invalid_identifier";
            $this->jsonecho();
        }

        $username = $_SESSION["2FA_".$twofaid]["username"];
        $passhash = $_SESSION["2FA_".$twofaid]["passhash"];
        $proxy = empty($_SESSION["2FA_".$twofaid]["proxy"]) 
               ? null : $_SESSION["2FA_".$twofaid]["proxy"];


        // Decrypt the password hash
        try {
            $password = \Defuse\Crypto\Crypto::decrypt($passhash, 
                        \Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY));
        } catch (\Exception $e) {
            $this->resp->msg = __("Encryption error");
            $this->jsonecho();
        }


        // Setup Instagram Client
        // Allow web usage
        // Since mentioned risks has been consider internally by Nextpost,
        // setting this property value to the true is not risky as it's name
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

        $storageConfig = [
            "storage" => "file",
            "basefolder" => SESSIONS_PATH."/".$AuthUser->get("id")."/",
        ];

        $Instagram = new \InstagramAPI\Instagram(false, false, $storageConfig);
        $Instagram->setVerifySSL(SSL_ENABLED);

        if ($proxy) {
            $Instagram->setProxy($proxy);
        }

        try {
            $resp = $Instagram->sendTwoFactorLoginSMS($username, $password, $twofaid);
        } catch (InstagramAPI\Exception\InstagramException $e) {
            if ($e->hasResponse()) {
                $msg = $e->getResponse()->getMessage();
            } else {
                $msg = explode(":", $e->getMessage(), 2);
                $msg = end($msg);
            }
            $this->resp->msg = $msg;
            $this->jsonecho();
        } catch (\Exception $e) {
            $this->resp->msg = __("Oops! Something went wrong. Please try again later!");
            $this->resp->devmsg = $e->getMessage();
            $this->jsonecho();
        }

        $_SESSION["2FA_".$resp->getTwoFactorInfo()->getTwoFactorIdentifier()] = [
            "username" => $username,
            "passhash" => $passhash,
            "proxy" => $proxy
        ];

        $this->resp->msg = __("SMS sent.");
        $this->resp->identifier = $resp->getTwoFactorInfo()->getTwoFactorIdentifier();
        $this->resp->result = 1;
        $this->jsonecho();
    }


    /**
     * Finish checkpoint
     * @return void 
     */
    protected function checkpoint()
    {
        $this->resp->result = 0;

        $AuthUser = $this->getVariable("AuthUser");
        $Account = $this->getVariable("Account");

        // Check if this is new or not
        $is_new = !$Account->isAvailable();

        $security_code = Input::post("checkpoint-security-code");
        $checkpointid = Input::post("checkpointid");

        if (!isset(
            $_SESSION["CHECKPOINT_".$checkpointid]["username"], 
            $_SESSION["CHECKPOINT_".$checkpointid]["passhash"],
            $_SESSION["CHECKPOINT_".$checkpointid]["checkpoint"])) 
        {
            $this->resp->msg = __("Oops! Something went wrong. Please try again later!");
            $this->resp->error_code = "account_invalid_identifier";
            $this->jsonecho();
        }

        // These variables have been saved to the session after validation
        // There is no need to validate them again here.
        require_once APPPATH."/lib/Checkpoint.php";
        $Checkpoint = unserialize($_SESSION["CHECKPOINT_".$checkpointid]["checkpoint"]);
        $username = $_SESSION["CHECKPOINT_".$checkpointid]["username"];
        $passhash = $_SESSION["CHECKPOINT_".$checkpointid]["passhash"];
        $proxy = empty($_SESSION["CHECKPOINT_".$checkpointid]["proxy"]) 
               ? null : $_SESSION["CHECKPOINT_".$checkpointid]["proxy"];

        // Verify security code
        $checkpoint_passed = false;
        $resp = $Checkpoint->sendSecurityCode($security_code);

        if (!isset($resp->status)) {
            $this->resp->msg = __("An error occured while verifying the security code.") ." "
                             . __("Please try again some time later.");
            $this->jsonecho();
        }

        if ($resp->status != "ok") {
            $msg = empty($resp->challenge->errors[0]) 
                 ? __("Couldn't verify the security code.") ." ". 
                   __("Please try again some time later.") 
                 : $resp->challenge->errors[0];

            if (strpos($msg, "check the code we sent") !== false) {
                $msg = __("Please check the code sent to you and try again.");
            }

            $this->resp->msg = $msg;
            $this->jsonecho();
        }

        // Security code for the checkpoint has been verified. 
        // Now process regular login


        // Decrypt the password hash
        try {
            $password = \Defuse\Crypto\Crypto::decrypt($passhash, 
                        \Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY));
        } catch (\Exception $e) {
            $this->resp->msg = __("Encryption error");
            $this->jsonecho();
        }


        // Setup Instagram Client
        // Allow web usage
        // Since mentioned risks has been consider internally by Nextpost,
        // setting this property value to the true is not risky as it's name
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        
        $storageConfig = [
            "storage" => "file",
            "basefolder" => SESSIONS_PATH."/".$AuthUser->get("id")."/",
        ];

        $Instagram = new \InstagramAPI\Instagram(false, false, $storageConfig);
        $Instagram->setVerifySSL(SSL_ENABLED);

        if ($proxy) {
            $Instagram->setProxy($proxy);
        }

        try {
            $login_resp = $Instagram->login($username, $password);

            if ($login_resp !== null && $login_resp->isTwoFactorRequired()) {
                $this->resp->result = 2;
                $this->resp->twofa_required = true;
                $this->resp->msg = __(
                    "Enter the code sent to your number ending in %s", 
                    $login_resp->getTwoFactorInfo()->getObfuscatedPhoneNumber());
                $this->resp->identifier = $login_resp->getTwoFactorInfo()->getTwoFactorIdentifier();

                $_SESSION["2FA_".$this->resp->identifier] = [
                    "username" => $username,
                    "passhash" => $passhash,
                    "proxy" => $proxy
                ];
            }
        } catch (InstagramAPI\Exception\CheckpointRequiredException $e) {
            $this->resp->msg = __("Sorry, we couldn't add this account at the moment.") ." "
                             . __("Please try again some time later.");
            $this->resp->login_failed = true;
            $this->jsonecho();
        } catch (InstagramAPI\Exception\ChallengeRequiredException $e) {
            $this->resp->msg = __("Sorry, we couldn't add this account at the moment.") ." "
                             . __("Please try again some time later.");
            $this->resp->login_failed = true;
            $this->jsonecho();
        } catch (InstagramAPI\Exception\AccountDisabledException $e) {
            $this->resp->msg = __(
                "Your account has been disabled for violating Instagram terms. <a href='%s'>Click here</a> to learn how you may be able to restore your account.", 
                "https://help.instagram.com/366993040048856");
            $this->jsonecho();
        } catch (InstagramAPI\Exception\SentryBlockException $e) {
            $this->resp->msg = __("Your account has been banned from Instagram API for spam behaviour or otherwise abusing.");
            $this->jsonecho();
        } catch (InstagramAPI\Exception\InstagramException $e) {
            if ($e->hasResponse()) {
                $msg = $e->getResponse()->getMessage();
            } else {
                $msg = explode(":", $e->getMessage(), 2);
                $msg = end($msg);
            }
            $this->resp->msg = $msg;
            $this->jsonecho();
        } catch (\Exception $e) {
            $this->resp->msg = __("Oops! Something went wrong. Please try again later!");
            $this->jsonecho();
        }

        $Account->set("user_id", $AuthUser->get("id"))
                ->set("password", $passhash)
                ->set("proxy", $proxy ? $proxy : "")
                ->set("login_required", 0)
                ->set("instagram_id", $login_resp->getLoggedInUser()->getPk())
                ->set("username", $login_resp->getLoggedInUser()->getUsername())
                ->set("login_required", 0)
                ->save();


        // Update proxy use count
        if ($proxy && $is_system_proxy == true) {
            $Proxy = Controller::model("Proxy", $proxy);
            if ($Proxy->isAvailable()) {
                $Proxy->set("use_count", $Proxy->get("use_count") + 1)
                      ->save();
            }
        }

        $this->resp->result = 1;
        if ($is_new) {
            $this->resp->redirect = APPURL."/accounts";
        } else {
            $this->resp->changes_saved = true;
            $this->resp->msg = __("Changes saved!");
        }
        $this->jsonecho();
    }



    /**
     * Handle the Checkpoint Exception
     * @param  string $username Username for the Instagram account
     * @param  string $password Password for the Instagram account
     * @param  string $passhash Hash string of the password
     * @param  string|null $proxy    Proxy for the connection
     * @return void           
     */
    private function _handleCheckpointException($username, $password, $passhash, $proxy = null)
    {
        $AuthUser = $this->getVariable("AuthUser");

        $this->resp->result = 2;
        
        // Login via web
        $chk_web = false;

        $cookies_dir = SESSIONS_PATH."/".$AuthUser->get("id")."/".$username;
        $Checkpoint = new \Checkpoint(false, $proxy, $cookies_dir);
        $Checkpoint->doFirstStep();
        $chk_login_resp = $Checkpoint->login($username, $password);

        if (isset($chk_login_resp->checkpoint_url)) {
            $chk_choice_resp = $Checkpoint->selectChoice(\Checkpoint::EMAIL);

            if (isset($chk_choice_resp->status) && $chk_choice_resp->status == "ok") {
                // Checkpoint URL found
                // Use the new method to bypass the checkpoint with
                // security code send to the email address (or SMS in the future)
                $chk_web = true;
                $this->resp->checkpoint_required = true;
                $this->resp->identifier = uniqid();

                if ($chk_choice_resp->fields->form_type == "phone_number") {
                    $this->resp->msg = __(
                        "Enter the code sent to your number ending in %s", 
                        $chk_choice_resp->fields->contact_point);
                } else {
                    $this->resp->msg = __(
                        "Enter the 6-digit code sent to the email address %s", 
                        $chk_choice_resp->fields->contact_point);
                }

                $_SESSION["CHECKPOINT_".$this->resp->identifier] = [
                    "checkpoint" => serialize($Checkpoint),
                    "username" => $username,
                    "passhash" => $passhash,
                    "proxy" => $proxy
                ];
            }
        }

        if (!$chk_web) {
            // Checkpoint URL not found
            // Use the classic method to bypass the checkpoint
            $this->resp->msg = __("Please goto <a href='http://instagram.com' target='_blank'>instagram.com</a> and pass checkpoint!");
        }
    }
}