# MCThrottle

Scriptlerinize Rate limiting ve Caching özelliği kazandırır.

Ayrıca arama motoru botlarını, gerçek zamanlı olarak, ip adreslerinden sorgular ve sahte botları tespit eder. Gerçek botlara ise limitsiz erişim iznini de verir.
Her zaman gerçek zamanlı sorgulama yapmaması için Cache özelliği de ekledim. Böylece ip bir kez sorgulanır ve kaydedilir. Bir daha gerçek zamanlı sorgulanmasına gerek kalmaz.

İzin verdiği botlar;
google, yandex, ahrefs, moz, semrush

Ayrıca içerisin de harici Cache özelliği de mevcuttur.
Cache özelliğini diğer kodlarınız da kullanabilirsiniz. 

Rate Limiting Kullanımı;
```
require_once("ratelimit.php");
 
$rateLimit = MCThrottle::rateLimit(function() {
    // Limit dolarsa döndürülecek içerik
    return "Too Many Request";
}, $maxAttempts=5, $decayMinutes=1, $headerDisplay=true);
```

CACHE kullanımı;
```
$getTime = MCThrottle::cache(function () {
    // Buraya çalıştırılacak kodunuzu ekleyip, return ile sonucu döndürün
    $time = time();
    return $time;
}, "cache-dosya-adi", $EXPIRED_TIME = 10); // 10 dakika cache süre dolunca yeniden istek çalışır
echo "Cache çıktısı : $getTime";
```
