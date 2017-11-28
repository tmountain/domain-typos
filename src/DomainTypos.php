<?php

final class DomainTypos {
    private static function notSame($elem) {
        list ($a, $b) = $elem;
        return $a != $b;
    }

    public static function hammingDistance($s1, $s2) {
        if (strlen($s1) != strlen($s2)) {
            return -1; // error
        }
        $xs = array_map(null, str_split($s1, 1), str_split($s2, 1));
        return count(array_filter($xs, 'self::notSame'));
    }

    public static function domain($email) {
        $result = substr(strrchr($email, "@"), 1);
        return $result ? $result : '';
    }

    public static function isTypo($email, $threshold = 1, $domains) {
        $emailDomain = self::domain($email);
        foreach ($domains as $domain) {
            if (strlen($emailDomain) == strlen($domain)) {
                $distance = self::hammingDistance($emailDomain, $domain);
                if ($distance <= $threshold && $distance > 0) {
                    return true;
                }
            }
        }
        return false;
    }
}
?>
