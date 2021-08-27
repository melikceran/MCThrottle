# MCThrottle

Scriptlerinize Rate limiting ve Cache özelliği kazandırır.

Ayrıca arama motoru botlarını, gerçek zamanlı olarak, ip adreslerinden sorgular ve sahte botları tespit eder. Gerçek botlara ise limitsiz erişim iznini de verir.
Her zaman gerçek zamanlı sorgulama yapmaması için Cache özelliği de ekledim. Böylece ip bir kez sorgulanır ve kaydedilir. Bir daha gerçek zamanlı sorgulanmasına gerek kalmaz.

İzin verdiği botlar;
google, yandex, ahrefs, moz, semrush

Ayrıca içerisin de harici Cache özelliği de mevcuttur.
Cache özelliğini diğer kodlarınız da kullanabilirsiniz. 

#### Rate Limiting Kullanımı;
```
require_once("ratelimit.php");
 
$rateLimit = MCThrottle::rateLimit(function() {

    // Limit dolarsa döndürülecek içerik
    return "Too Many Request";
    
}, $maxAttempts=5, $decayMinutes=1, $headerDisplay=true);

// $maxAttempts=5 ---> kaç istekte bulunabilir
// $decayMinutes=1 ---> istek limitlerinin zaman aşımı süresi
// $headerDisplay=true  ---> browser'a limit bilgisi gönderilsin mi

if ($rateLimit) {
    print_r($rateLimit);
    exit();
}
```

#### Browser Limit bilgisi  
![alt text](/rate1.png?raw=true)

Status 429 Too Many Request  
![alt text](/rate2.png?raw=true)



#### CACHE kullanımı;
```
$getTime = MCThrottle::cache(function () {

    // Buraya çalıştırılacak kodunuzu ekleyip, return ile sonucu döndürün
    $time = time();
    
    return $time;
    
}, "cache-dosya-adi", $EXPIRED_TIME = 10); // 10 dakika cache süre dolunca yeniden istek çalışır

echo "Cache çıktısı : $getTime";
```


#### Gerçek zamanlı bot tespiti kullanımı; (isteğe bağlı) 
```
$botDetect = MCThrottle::botDetect();
if ($botDetect) {
 echo "Bu izin verdiğimiz bir bottur.";
} else {
 echo "Bu ziyaretçi ve diğerleri...";
}
```



#### BOT izinleri ve PATH ayarları
ratelimit.php
```
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
```

