<?php

// Domain typo detection.

final class DomainTypos
{
    // takes a pair of values [$x, $y]
    // and returns a boolean value indicating if they
    // are not the same.
    public static function notSame($elem) {
        list ($a, $b) = $elem;
        return $a != $b;
    }

    // takes a value and a number of times to repeat it
    // and returns an array of $times length
    // containing the value.
    public static function repeat($x, $times) {
        $xs = [];

        if ($times < 0) { return $xs; }

        for ($i = 0; $i < $times; $i++) {
            $xs []= $x;
        }
        return $xs;
    }

    // returns a boolean value indicating whether $string
    // ends with $test.
    public static function endsWith($string, $test) {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen) return false;
        return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
    }

    // takes a pair of values [$x, $y] and
    // returns a boolean value indicating whether
    // x ends with y.
    public static function matchEnd($elem) {
        list ($string, $test) = $elem;
        return self::endsWith($string, $test);
    }

    // if $tlds contains a TLD that matches the TLD
    // of $domain, it is returned. otherwise, an
    // empty string is returned.
    public static function matchTLD($domain, $tlds) {
        $pairs = array_map(null, self::repeat($domain, count($tlds)), $tlds);
        $matchTLD = array_filter($pairs, 'self::matchEnd');

        if (empty($matchTLD)) {
            return '';
        } else {
            $result = reset($matchTLD);
            list ($domain, $tld) = $result;
            return $tld;
        }
    }

    // returns the hostname for the domain provided
    // by removing $tld from the end.
    public static function extractHost($domain, $tld) {
        $pos = strpos($domain, $tld);
        if (!$pos) { return ''; }
        return substr($domain, 0, $pos);
    }

    // returns the hamming distance between two strings.
    // https://en.wikipedia.org/wiki/Hamming_distance
    public static function hammingDistance($s1, $s2) {
        if (strlen($s1) != strlen($s2)) {
            return -1; // error
        }
        $xs = array_map(null, str_split($s1, 1), str_split($s2, 1));
        return count(array_filter($xs, 'self::notSame'));
    }

    // returns the domain for the provided email address
    // or an empty string if no domain can be extracted.
    public static function domain($email) {
        $result = substr(strrchr($email, "@"), 1);
        return $result ? $result : '';
    }

    // returns a boolean value indicating whether the email address
    // provided is a typo. parameters are as follows:
    // $email = the email address
    // $threshold = the number of typos allowed (fewer = more strict)
    // $domains = a list of ISP domains to check against the $email provided
    // $tlds = a list of TLDs we want to consider (.com, .co.uk, etc)
    public static function isTypo($email, $threshold, $domains, $tlds) {
        $emailDomain = self::domain($email);
        $tld = self::matchTLD($emailDomain, $tlds);

        // not a typo because the TLD doesn't match any of the TLD provided
        if (empty($tld)) {
            return false;
        }

        // get the host from the email address provided
        $host = self::extractHost($emailDomain, $tld);
        // create a function for filtering domains matching the tld
        $endsWithTLD = function ($domain) use ($tld) {
            return self::endsWith($domain, $tld);
        };
        // constrain domains to those matching the tld
        $domains = array_filter($domains, $endsWithTLD);

        // indicates if a match is found
        $matchFound = false;

        // typically all domains with the same TLD
        // have to be analyzed so that we can rule out
        // exact matches. this handles situations like
        // ymail.com (a valid domain) reporting as a typo
        // on gmail.com.
        foreach ($domains as $domain) {
            $domain = self::extractHost($domain, $tld);

            // if an exact match is found, it's not a typo
            if ($host === $domain) {
                return false;
            }

            if (strlen($host) === strlen($domain)) {
                $distance = self::hammingDistance($host, $domain);
                if ($distance <= $threshold && $distance > 0) {
                    $matchFound = true;
                }
            }
        }
        return $matchFound;
    }
}
?>
