<?php

class Eventife_Model_Custom_Miscellaneous
{

    public function randomPassword($password_length = 7)
    {

        $chars = "abcdefghijklmnopqrstuvwxyz123456789";
        srand((double)microtime() * 1000000);
        $i = 0;
        $pass = '';

        while ($i <= $password_length) {

            $num = rand() % 34;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }

        return $pass;
    }

    public function detectUserAgent($property = null)
    {

        $clientProps = array();
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        // detect Operating System
        // array of Possible Operating Systems
        $OSList = array(
            'Windows 3.11' => 'Win16', 'Windows 95' => 'Windows 95', 'Windows 95' => 'Win95', 'Windows 95' => 'Windows_95',
            'Windows 98' => 'Windows 98', 'Windows 98' => 'Win98', 'Windows 2000' => 'Windows NT 5.0', 'Windows 2000' => 'Windows 2000',
            'Windows XP' => 'Windows NT 5.1', 'Windows XP' => 'Windows XP', 'Windows Server 2003' => 'Windows NT 5.2',
            'Windows Vista' => 'Windows NT 6.0', 'Windows 7' => 'Windows NT 7.0', 'Windows NT 4.0' => 'Windows NT 4.0',
            'Windows NT 4.0' => 'WinNT4.0', 'Windows NT 4.0' => 'WinNT', 'Windows NT 4.0' => 'Windows NT', 'Windows ME' => 'Windows ME',
            'Open BSD' => 'OpenBSD', 'Sun OS' => 'SunOS', 'Linux' => 'Linux', 'Linux' => 'X11', 'Mac OS' => 'Mac_PowerPC', 'Mac OS' => 'Macintosh',
            'QNX' => 'QNX', 'BeOS' => 'BeOS', 'OS/2' => 'OS/2', 'Search Bot' => 'nuhk', 'Search Bot' => 'Googlebot', 'Search Bot' => 'Yammybot',
            'Search Bot' => 'Openbot', 'Search Bot' => 'Slurp', 'Search Bot' => 'MSNBot', 'Search Bot' => 'Ask Jeeves/Teoma', 'Search Bot' => 'ia_archiver'
        );

        foreach ($OSList as $CurrOS => $Match) {
            if (stristr($userAgent, $Match)) {
                $clientProps['platform'] = $CurrOS;
                break;
            }
        }

        // detect browser
        if (strpos($userAgent, 'Gecko')) {
            if (strpos($userAgent, 'Netscape')) $clientProps['browser'] = 'Netscape (Gecko/Netscape)';
            elseif (strpos($userAgent, 'Firefox')) $clientProps['browser'] = 'Mozilla Firefox (Gecko/Firefox)';
            else $clientProps['browser'] = 'Mozilla (Gecko/Mozilla)';
        } elseif (strpos($userAgent, 'MSIE')) {
            if (strpos($userAgent, 'Opera')) $clientProps['browser'] = 'Opera (MSIE/Opera/Compatible)';
            else $clientProps['browser'] = 'Internet Explorer (MSIE/Compatible)';
        } else $clientProps['browser'] = 'Others browsers';

        // detect client Ip-Address
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) $clientProps['ip'] = '' . $_SERVER["HTTP_CLIENT_IP"] . ' ';
        elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) $clientProps['ip'] = '' . $_SERVER["HTTP_X_FORWARDED_FOR"] . ' ';
        elseif (!empty($_SERVER["REMOTE_ADDR"])) $clientProps['ip'] = '' . $_SERVER["REMOTE_ADDR"] . ' ';

        $clientProps['device_type'] = self::getDeviceType();
        if (isset($property)) {
            return $clientProps[$property];
        } else {
            return $clientProps;
        }
    }

    public function getCurrentUrl()
    {

        $pageURL = 'http';

        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }

    public function getDeviceType()
    {
        $tablet_browser = 0;
        $mobile_browser = 0;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }

        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');

        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }

        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                $tablet_browser++;
            }
        }

        if ($tablet_browser > 0) {
            // do something for tablet devices
            return 'tablet';
        } else if ($mobile_browser > 0) {
            // do something for mobile devices
            return 'mobile';
        } else {
            // do something for everything else
            return 'desktop';
        }

    }
}