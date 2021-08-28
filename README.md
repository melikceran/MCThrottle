# MCThrottle


It gives your website Speed limiting and Cache features.

It also queries search engine bots from ip addresses in real time and detects fake bots. It also provides unlimited access to real bots. I've also added the Cache feature so it doesn't always query in real time. So the ip is queried once and saved.

Allowed bots; google, yandex, ahrefs, moz, semrush

It also has an external cache feature. You can also use the cache feature in your other codes.

#### Use of Rate Limiting;
```php
require_once("ratelimit.php");
 
$rateLimit = MCThrottle::rateLimit(function() {

    // Text to return if limit is reached
    return "Too Many Request";
    
}, $maxAttempts=5, $decayMinutes=1, $headerDisplay=true);

// $maxAttempts=5 ---> how many requests can it make
// $decayMinutes=1 ---> timeout of request limits
// $headerDisplay=true ---> send limit info to browser

if ($rateLimit) {
    print_r($rateLimit);
    exit();
}
```

#### Browser Limit information 
![alt text](/rate1.png?raw=true)

### Status 429 Too Many Request  
![alt text](/rate2.png?raw=true)

#### Use of CACHE;
```php
$getTime = MCThrottle::cache(function () {

    // Add your code to be executed here and return the result
    // Your database queries etc. anything can happen. Just return the result.
    $time = time();
    
    return $time;
    
}, "cache-file-name", $EXPIRED_TIME = 10); // After 10 minutes of cache time expires, the request runs again

echo "Cache output : $getTime";
```

#### Use of real-time bot detection; (optional)
```php
$botDetect = MCThrottle::botDetect();
if ($botDetect) {
 echo "This is a bot we allow.";
} else {
 echo "This visitor and others...";
}
```

#### Search engine bot permissions and PATH settings
ratelimit.php
```php
class MCThrottle
{
     /*
     * Cache folder path is assigned to variable
     * Customize this place yourself
     * It will automatically create /caches/ folder in the root of your site (if you have write permission)
     */
    private static $PATH = __DIR__ . "/caches/";
    // private static $PATH = $_SERVER['DOCUMENT_ROOT'] . "/caches/";
 
    /*
     *  Bots with no rate limit allowed
    */
    private static $ACCEPT_BOTS = ["google.com","googlebot.com","yandex.com","yandex.ru","ahrefs.com","moz.com","semrush.com"];
```

