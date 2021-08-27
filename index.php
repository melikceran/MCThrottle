<?php

require_once("ratelimit.php");



$rateLimit = MCThrottle::rateLimit(function() {
    // Limit dolarsa döndürülecek içerik
    return "Çok fazla istekte bulunuyorsunuz";
}, $maxAttempts=5, $decayMinutes=1, $headerDisplay=true);
if ($rateLimit) {
    print_r($rateLimit);
    exit();
}


// Bu ise caching kullanımı örneği
$getTime = MCThrottle::cache(function () {
    // Buraya çalıştırılacak kodunuzu ekleyip, return ile sonucu döndürün
    $time = time();
    return $time;
}, "cache-dosya-adi", $EXPIRED_TIME = 10); // 10 dakika cache süre dolunca yeniden istek çalışır
echo "Cache çıktısı : $getTime";

echo "<hr>";

echo "Burası içeriğimiz. Eğer istek sayısı dolarsa erişim engellenir. Ama izin verilen botlar erişebilir.";