<?php
/**
 * Checkpoint fix
 *
 * @license https://opensource.org/licenses/RPL-1.5
 */
class Checkpoint
{
    // User Agent.
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:52.0) Gecko/20100101 Firefox/52.0';
    
    // Select option to receive security code.
    const EMAIL = 1;
    const SMS = 0;

    protected $debug; // Prints debug information to screen (CLI).
    protected $token; // csrftoken.
    protected $proxy; // user:pass@ip:port or ip:port.
    protected $ssl = false; // SSL verification disabled by default.
    protected $checkpointURL;
    protected $user;
    protected $cookieFile; // File to save cookies

    /**
     * Constructor.
     *
     * @param $debug    Enable/Disable debug log.
     * @param $proxy    Use the following format: user:pass@ip:port or ip:port.
     * @param $cookies_dir    Directory to store the cookie files
     *
     */
    public function __construct($debug = false, $proxy = null, $cookies_dir = null)
    {
        $this->debug = $debug;
        $this->proxy = $proxy;

        if (is_string($cookies_dir) && file_exists($cookies_dir)) {
            @mkdir($cookies_dir, 0777, true);
        }

        $this->cookieFile = ($cookies_dir ? rtrim($cookies_dir, "/") : ".")
                          . "/cookies.dat";

        if (file_exists($this->cookieFile))
            unlink($this->cookieFile);
    }

    public function login($username, $password) {
        $post = [
            'username'     => $username,
            'password'     => $password
        ];

        $headers = [
            'Connection: keep-alive',
            'Proxy-Connection: keep-alive',
            'Accept-Language: en-US,en',
            'x-csrftoken: '.$this->token,
            'x-instagram-ajax: 1',
            'Referer: https://www.instagram.com/',
            'x-requested-with: XMLHttpRequest',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ];

        $resp = $this->request('https://www.instagram.com/accounts/login/ajax/', $headers, $post);

        if (isset($resp->checkpoint_url)) {
            $this->checkpointURL = 'https://www.instagram.com'.$resp->checkpoint_url;
        }

        return $resp;
    }

    public function doFirstStep()
    {
        $response = $this->request('https://www.instagram.com/', null, null, 0);

        preg_match('#Set-Cookie: csrftoken=([^;]+)#i', $response, $token);

        $this->token = $token[1];
    }

    /**
     * Select choice to receive security code.
     *
     * @param $choice   1 - Email, 2 - SMS. Use class constants. Checkpoint::EMAIL.
     *
     */
    public function selectChoice($choice)
    {
        $post = [
            'choice'     => $choice
        ];

        $headers = [
            'Connection: keep-alive',
            'Proxy-Connection: keep-alive',
            'Accept-Language: en-US,en',
            'Referer: '.$this->checkpointURL,
            'x-csrftoken: '.$this->token,
            'x-instagram-ajax: 1',
            'x-requested-with: XMLHttpRequest',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ];

        return $this->request($this->checkpointURL, $headers, $post);
    }

    public function sendSecurityCode($code)
    {
        $post = [
            'security_code'       => $code,
        ];

        $headers = [
            'Connection: keep-alive',
            'Proxy-Connection: keep-alive',
            'Accept-Language: en-US,en',
            'Referer: '.$this->checkpointURL,
            'x-csrftoken: '.$this->token,
            'x-instagram-ajax: 1',
            'x-requested-with: XMLHttpRequest',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ];

        return $this->request($this->checkpointURL, $headers, $post);
    }

    public function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    public function request($endpoint, $headers = null, $post = null, $return = 1)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        if (!is_null($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->ssl);
        if ($this->proxy !== null) {
            $proxyData = explode('@', $this->proxy);
            if (count($proxyData) > 1) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyData[0]);

                $ipport = explode(':', $proxyData[1]);
                curl_setopt($ch, CURLOPT_PROXY, $ipport[0]);
                curl_setopt($ch, CURLOPT_PROXYPORT, $ipport[1]);
            } else {
                $ipport = explode(':', $this->proxy);
                curl_setopt($ch, CURLOPT_PROXY, $ipport[0]);
                curl_setopt($ch, CURLOPT_PROXYPORT, $ipport[1]);
            }
        }
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, count($post));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);

        curl_close($ch);

        if ($this->debug) {
            echo "REQUEST: $endpoint\n";
            if (!is_null($post)) {
                if (!is_array($post)) {
                    echo 'DATA: '.urldecode($post)."\n";
                }
            }
            echo "RESPONSE: $body\n\n";
        }

        $body = json_decode($body);

        switch ($return) {
            case 0:
                return $header;
                break;

            case 1:
                return $body;
                break;

            case 2:
                return [$header, $body];
                break;
            
            default:
                return $body;
                break;
        }
        return $body;
    }

    public function setSSL($enable) {
        $this->ssl = $enable;
    }
}
