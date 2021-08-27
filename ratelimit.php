<?php


class MCThrottle
{

    /*
    *   Cache klasörü yolu değişkene atanıyor
    *   Burayı kendinize göre düzenleyin
    *   Sitenizin root yolunda /caches/ klasörü otomatik oluşturacaktır (yazma izniniz varsa)
    */
    private static $PATH = __DIR__ . "/caches/";
    // private static $PATH = $_SERVER['DOCUMENT_ROOT'] . "/caches/";

    /*
     *  Rate limitsiz izin verilen botlar
    */
    private static $ACCEPT_BOTS = ["google.com","googlebot.com","yandex.com","yandex.ru","ahrefs.com","moz.com","semrush.com"];


    public static function cache($action, $NAME, $EXPIRED_TIME = 0)
    {
        $PATH = self::$PATH;

        self::create_path($PATH);

        if (self::has_file($PATH, $NAME)) {
            $output = self::read($EXPIRED_TIME, $PATH, $NAME);
            if ($output) return $output;
        }

        $output = self::save($action, $NAME, $PATH);

        return $output;
    }

    private static function create_path($PATH)
    {
        if (!is_dir($PATH)) mkdir($PATH);
        if (!is_dir($PATH."bots")) mkdir($PATH."bots");
        if (!is_dir($PATH."visitors")) mkdir($PATH."visitors");
    }

    private static function has_file($PATH, $NAME)
    {
        $FILENAME = $PATH."$NAME.json";
        if (file_exists($FILENAME)) return true;
        return false;
    }

    private static function read($EXPIRED_TIME, $PATH, $NAME)
    {
        $FILENAME = $PATH.$NAME.".json";
        if ($EXPIRED_TIME > 0) {
            $REMAIN_TIME = time() - ($EXPIRED_TIME * 60);
            if (filemtime($FILENAME) >= $REMAIN_TIME) {
                $json = file_get_contents($FILENAME);
                return $json;
            }
        }
        return null;
    }

    private static function save($action, $NAME, $PATH)
    {
        $FILENAME = $PATH."$NAME.json";
        $callable = $action();
        if (empty($callable)) return null;
        $json = json_encode($callable);
        if (!empty($json)) file_put_contents($FILENAME, $json);
        return $json;
    }

    private static function getIp()
    {
        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function botDetect()
    {
        $user_agent = $_SERVER["HTTP_USER_AGENT"];

        $ACCEPT_BOTS = self::$ACCEPT_BOTS;
        foreach ($ACCEPT_BOTS as $botName) {
            if (strpos($user_agent, "$botName") !== false) {
                $ip = self::getIP();
                $gethostbyaddr = self::cache(function () use ($ip) {
                    if ($ip) return gethostbyaddr($ip);
                    return null;
                }, "bots/$ip", $EXPIRED_TIME = 525600); // yaklaşık 1 yıl cache
                if (strpos($gethostbyaddr, "$botName") !== false) {
                    return true;
                }
                break;
            }
        }

        return null;
    }


    public static function rateLimit($action, $maxAttempts=5, $decayMinutes=1, $headerDisplay=true)
    {
        $PATH = self::$PATH;
        
        self::create_path($PATH);

        $hasBot = self::botDetect();
        if (!$hasBot) {
            $ip = self::getIP();
            $json = null;
            if (self::has_file($PATH."visitors/", "$ip")) {
                $json = file_get_contents($PATH."visitors/$ip.json");
                if ($json) {
                    $arr = json_decode($json, true);
                    $ARR_TIME = isset($arr["time"]) ? $arr["time"] : 0;
                    $ARR_ATTEMPTS = isset($arr["attempts"]) ? $arr["attempts"] : $maxAttempts;

                    $output = self::rateSave($arr, $ARR_ATTEMPTS, $ARR_TIME, $PATH, $maxAttempts, $decayMinutes, $ip);
                    if ($output) {
                        $ARR_TIME = isset($output["time"]) ? $output["time"] : 0;
                        $ARR_ATTEMPTS = isset($output["attempts"]) ? $output["attempts"] : $maxAttempts;
                    }

                    if ($headerDisplay) {
                        header("x-ratelimit-limit: $maxAttempts");
                        header("x-ratelimit-remaining: ".$ARR_ATTEMPTS);
                        header("x-ratelimit-reset: ". ($ARR_TIME+($decayMinutes*60)));
                    }

                    if ($ARR_ATTEMPTS <= 0) {
                        header("HTTP/1.1 429 Too Many Request");
                        return $action();
                    }

                }
            }
            if (!$json) {
                $arr = ["attempts" => $maxAttempts,"time" => time(),];
                $json = json_encode($arr);
                if (!empty($json)) file_put_contents($PATH."visitors/$ip.json", $json);
            }

        }
    }

    private static function rateSave($arr, $ARR_ATTEMPTS, $ARR_TIME, $PATH, $maxAttempts, $decayMinutes, $ip)
    {
        $decaySeconds = $decayMinutes * 60;
        $REMAIN_TIME = time() - $decaySeconds;
        if ($ARR_TIME < $REMAIN_TIME) {
            $arr = ["attempts" => $maxAttempts,"time" => time(),];
            $json = json_encode($arr);
            if (!empty($json)) file_put_contents($PATH."visitors/$ip.json", $json);
        } else {
            if ($ARR_ATTEMPTS > 0) {
                $arr["attempts"] = $arr["attempts"] - 1;
                $json = json_encode($arr);
                if (!empty($json)) file_put_contents($PATH."visitors/$ip.json", $json);
            }
        }
        return $arr;
    }


}

