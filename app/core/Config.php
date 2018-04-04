<?php

abstract class Config
{
    private static $cfg;

    public static function loadConfig()
    {
        self::$cfg = require(APP . '/config/config.php');
    }

    public static function getMinLoginLength()
    {
        return self::$cfg['user']['minLoginLength'];
    }

    public static function getMaxLoginLength()
    {
        return self::$cfg['user']['maxLoginLength'];
    }

    public static function getMinPasswordLength()
    {
        return self::$cfg['user']['minPasswordLength'];
    }

    public static function getMaxPasswordLength()
    {
        return self::$cfg['user']['maxPasswordLength'];
    }

    public static function getCookieCryptPassword()
    {
        return self::$cfg['cookie']['cryptPassword'];
    }

    public static function getCookieLiveTime()
    {
        return self::$cfg['cookie']['liveTime'];
    }

    public static function getPhotosFolder()
    {
        return self::$cfg['photosFolder'];
    }

    public static function getCaptchaSiteKey()
    {
        return self::$cfg['captcha']['siteKey'];
    }

    public static function getCaptchaSecretKey()
    {
        return self::$cfg['captcha']['secretKey'];
    }

    public static function getSMTPSettings()
    {
        return self::$cfg['smtp'];
    }
}
