<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation;

use RuntimeException;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * Http utility functions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IpUtils
{
    /** @var bool[] $checkedIps */
    private static array $checkedIps = [];

    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Checks if an IPv4 or IPv6 address is contained in the list of given IPs or subnets.
     *
     * @param string|string[] $ips List of IPs or subnets (can be a string if only a single one)
     */
    public static function checkIp(string $requestIp, $ips): bool
    {
        /** @var string[] $ips_array */
        $ips_array = [];
        if (!\is_array($ips)) {
            $ips_array = [(string)$ips];
        } else {
            $ips_array = array_map('strval', $ips);
        }

        $method = substr_count($requestIp, ':') > 1 ? 'checkIp6' : 'checkIp4';

        foreach ($ips_array as $ip) {
            if ($method === 'checkIp4' && self::checkIp4($requestIp, $ip)) {
                    return true;
            } elseif (self::checkIp6($requestIp, $ip)) {
                    return true;
            }
        }

        return false;
    }

    /**
     * Compares two IPv4 addresses.
     * In case a subnet is given, it checks if it contains the request IP.
     *
     * @param string $ip IPv4 address or subnet in CIDR notation
     *
     * @return bool Whether the request IP matches the IP, or whether the request IP is within the CIDR subnet
     */
    public static function checkIp4(string $requestIp, string $ip): bool
    {
        $cacheKey = $requestIp . '-' . $ip;
        if (isset(self::$checkedIps[$cacheKey])) {
            return self::$checkedIps[$cacheKey];
        }

        $regexIpV4 = '([0-9]{1,3}[\.]){3}[0-9]{1,3}';

        if (!(bool)preg_match("/^{$regexIpV4}$/", $requestIp)) {
            return self::$checkedIps[$cacheKey] = false;
        }

        if ((bool)strpos($ip, '/')) {
            [$address, $netmask] = explode('/', $ip, 2);

            if ($netmask === '0') {
                $validateIp4 = (bool)preg_match("/^{$regexIpV4}$/", $address);
                return self::$checkedIps[$cacheKey] = $validateIp4;
            }

            if ($netmask < 0 || $netmask > 32) {
                return self::$checkedIps[$cacheKey] = false;
            }
        } else {
            $address = $ip;
            $netmask = 32;
        }

        if (ip2long($address) === false) {
            return self::$checkedIps[$cacheKey] = false;
        }
        $compareRequestIp = sprintf('%032b', ip2long($requestIp));
        $compareAddress = sprintf('%032b', ip2long($address));

        $getNetmask = substr_compare($compareRequestIp, $compareAddress, 0, (int)$netmask);

        return self::$checkedIps[$cacheKey] = ($getNetmask === 0);
    }

    /**
     * Compares two IPv6 addresses.
     * In case a subnet is given, it checks if it contains the request IP.
     *
     * @author David Soria Parra <dsp at php dot net>
     *
     * @see https://github.com/dsp/v6tools
     *
     * @param string $ip IPv6 address or subnet in CIDR notation
     *
     * @throws RuntimeException When IPV6 support is not enabled
     */
    public static function checkIp6(string $requestIp, string $ip): bool
    {
        $cacheKey = $requestIp . '-' . $ip;
        if (isset(self::$checkedIps[$cacheKey])) {
            return self::$checkedIps[$cacheKey];
        }

        if (
            !((\extension_loaded('sockets') && \defined('AF_INET6'))
            || (bool)@inet_pton('::1'))
        ) {
            throw new RuntimeException(
                'Unable to check Ipv6. Check that PHP was not compiled with option "disable-ipv6".'
            );
        }

        $regexIpV6 = '(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))';

        // Check to see if we were given a IP4 $requestIp or $ip by mistake
        if (!(bool)preg_match("/^{$regexIpV6}$/", $requestIp)) {
            return self::$checkedIps[$cacheKey] = false;
        }

        if ((bool)strpos($ip, '/')) {
            [$address, $netmask] = explode('/', $ip, 2);

            if (!(bool)preg_match("/^{$regexIpV6}$/", $address)) {
                return self::$checkedIps[$cacheKey] = false;
            }

            if ($netmask === '0') {
                return (bool)unpack('n*', (string)@inet_pton($address));
            }

            if ((int)$netmask < 1 || (int)$netmask > 128) {
                return self::$checkedIps[$cacheKey] = false;
            }
        } else {
            if (!(bool)preg_match("/^{$regexIpV6}$/", $ip)) {
                return self::$checkedIps[$cacheKey] = false;
            }

            $address = $ip;
            $netmask = 128;
        }

        $bytesAddr = unpack('n*', (string)@inet_pton($address));
        $bytesTest = unpack('n*', (string)@inet_pton($requestIp));

        if (!(bool)$bytesAddr || !(bool)$bytesTest) {
            return self::$checkedIps[$cacheKey] = false;
        }

        for ($i = 1, $ceil = ceil((int)$netmask / 16); $i <= $ceil; $i++) {
            $left = (int)$netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xFFFF >> $left) & 0xFFFF;
            // KPHP use mixed[] type for $bytesAddr and $bytesTest
            if (
                is_array($bytesAddr) && is_array($bytesTest) &&
                ($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)
            ) {
                return self::$checkedIps[$cacheKey] = false;
            }
        }

        return self::$checkedIps[$cacheKey] = true;
    }

    /**
     * Anonymizes an IP/IPv6.
     *
     * Removes the last byte for v4 and the last 8 bytes for v6 IPs
     */
    public static function anonymize(string $ip): string
    {
        $ipv4Mapped = false;
        $ipv4Compatible = false;

        $regexIpV4 = '([0-9]{1,3}[\.]){3}[0-9]{1,3}';

        if (str_starts_with($ip, '::ffff:')) {
            $ipv4Mapped = true;
            $ip = (string)substr($ip, strlen('::ffff:'));
        } elseif ((bool)preg_match("/^::{$regexIpV4}$/", $ip)) {
            $ipv4Compatible = true;
            $ip = (string)substr($ip, strlen('::'));
        }

        $wrappedIPv6 = false;
        if (str_starts_with($ip, '[') && str_ends_with($ip, ']')) {
            $wrappedIPv6 = true;
            $ip = (string)substr($ip, 1, -1);
        }

        if ((bool)preg_match("/^{$regexIpV4}$/", $ip)) {
            $ip = self::replaceLastIPv4OctetWithZero($ip);
        } else {
            $ip = self::expandIPv6Address($ip);
            $ip = self::replaceLastHexetsWithZero($ip);
        }

        if ($ipv4Mapped) {
            $ip = '::ffff:' . $ip;
        } elseif ($ipv4Compatible) {
            $ip = '::' . $ip;
        }

        if ($wrappedIPv6) {
            $ip = '[' . $ip . ']';
        }

        return $ip;
    }

    private static function replaceLastIPv4OctetWithZero(string $ipAddress): string
    {
        // Разбиваем адрес на октеты
        $octets = explode('.', $ipAddress);

        // Получаем количество октетов
        $numOctets = count($octets);

        // Заменяем последний октет на 0
        $octets[$numOctets - 1] = '0';

        // Собираем адрес обратно
        return implode('.', $octets);
    }

    private static function expandIPv6Address(string $address): string
    {
        if (!is_bool(strpos($address, '::'))) {
            $expandedAddress = '';
            $parts = explode('::', $address);
            $firstHalf = $parts[0] != '' ? explode(':', $parts[0]) : [];
            $secondHalf = $parts[1] != '' ? explode(':', $parts[1]) : [];
            $missingZeros = [];

            if (count($firstHalf) + count($secondHalf) < 8) {
                $missingZerosCount = 8 - (count($firstHalf) + count($secondHalf));
                $missingZeros = array_fill(0, $missingZerosCount, '0000');
            }

            if (count($firstHalf) !== 0 && count($secondHalf) !== 0) {
                $expandedAddress .= implode(':', $firstHalf) . ':'
                    . implode(':', $missingZeros) . ':'
                    . implode(':', $secondHalf);
            } elseif (count($firstHalf) !== 0) {
                $expandedAddress .= implode(':', $firstHalf) . ':'
                    . implode(':', $missingZeros);
            } elseif (count($secondHalf) !== 0) {
                $expandedAddress .= implode(':', $missingZeros) . ':'
                    . implode(':', $secondHalf);
            } else {
                $expandedAddress .= implode(':', $missingZeros);
            }

            return $expandedAddress;
        }

        return $address;
    }

    private static function replaceLastHexetsWithZero(string $ipv6Address): string
    {
        // Разделяем IPv6-адрес на хекстеты
        $hexets = explode(':', $ipv6Address);

        for ($i = 0; $i < 8; $i++) {
            if ($hexets[$i] === '0000') {
                $hexets[$i] = '0';
            }
        }

        // Заменяем последние 4 хекстета на 0
        for ($i = 4; $i < 8; $i++) {
            $hexets[$i] = '0';
        }

        // Собираем IPv6-адрес с замененными хекстетами и сокращаем
        return self::shortenIPv6(implode(':', $hexets));
    }

//    public static function anonymize(string $ip): string
//    {
//        $ipv4Mapped = false;
//        $ipv4Compatible = false;
//
//        if (str_starts_with($ip, '::ffff:')) {
//            $ipv4Mapped = true;
//            $ip = (string)substr($ip, strlen('::ffff:'));
//        } elseif ((bool)preg_match('/^::\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
//            $ipv4Compatible = true;
//            $ip = (string)substr($ip, strlen('::'));
//        }
//
//        $wrappedIPv6 = false;
//        if (str_starts_with($ip, '[') && str_ends_with($ip, ']')) {
//            $wrappedIPv6 = true;
//            $ip = (string)substr($ip, 1, -1);
//        }
//
//        $packedAddress = inet_pton($ip);
//        if (strlen($packedAddress) === 4) {
//            $mask = '255.255.255.0';
//        } elseif (inet_pton($ip) === ($packedAddress & inet_pton('::ffff:ffff:ffff'))) {
//            $mask = '::ffff:ffff:ff00';
//        } elseif (inet_pton($ip) === ($packedAddress & inet_pton('::ffff:ffff'))) {
//            $mask = '::ffff:ff00';
//        } else {
//            $mask = 'ffff:ffff:ffff:ffff:0000:0000:0000:0000';
//        }
//        $ip = self::inet_ntop((string)($packedAddress & inet_pton($mask)));
//
//        if ($ipv4Mapped) {
//            $ip = '::ffff:' . $ip;
//        } elseif ($ipv4Compatible) {
//            $ip = '::' . $ip;
//        }
//
//        if ($wrappedIPv6) {
//            $ip = '[' . $ip . ']';
//        }
//
//        return $ip;
//    }
//
//    private static function inet_ntop(string $ip): string
//    {
//        if (strlen($ip) === 4) {
//            // IPv4 address
//            return self::inet_ntop_ipv4($ip);
//        } elseif (strlen($ip) === 16) {
//            // IPv6 address
//            return self::inet_ntop_ipv6($ip);
//        } else {
//            // Invalid IP address
//            return '';
//        }
//    }
//
//    private static function inet_ntop_ipv4(string $ip): string
//    {
//
//        return implode('.', unpack('C4', $ip));
//    }
//
//    private static function inet_ntop_ipv6(string $ip): string
//    {
//        $chunks = str_split($ip, 2);
//        $ipv6 = [];
//        foreach ($chunks as $chunk) {
//            $hextet = sprintf('%02x', ord($chunk[0])) . sprintf('%02x', ord($chunk[1]));
//            if ($hextet === '0000') {
//                $hextet = '0';
//            } else {
//                $hextet = ltrim($hextet, '0');
//            }
//            $ipv6[] = $hextet;
//        }
//        $ipString = implode(':', $ipv6);
//        // Replace longest groups of zeros with '::'
//        $ipString = self::shortenIPv6($ipString);
//        return $ipString;
//    }

    private static function shortenIPv6(string $ip): string
    {
        // Поиск самой длинной последовательности нулей в IPv6 адресе, которую можно сократить
        $pattern = '/(?:^|:)(?:0(?::0)+)(?::|$)/';

        // Поиск всех совпадений в строке IPv6
        preg_match_all($pattern, $ip, $matches);

        // Если найдены совпадения, заменяем самую длинную последовательность на два двоеточия
        if (!empty($matches[0])) {
            $longestMatch = array_reduce($matches[0], static function ($carry, $item) {
                if (strlen($item) > strlen($carry)) {
                    return $item;
                }
                return $carry;
            }, '');

            $ip = str_replace($longestMatch, '::', $ip);
        }

        return $ip;
    }
}
