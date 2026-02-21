<?php

declare(strict_types=1);

class AppConfig
{
    public static function nutritionAppId(): string
    {
        $value = getenv('EDAMAM_APP_ID');
        return $value !== false ? $value : 'YOUR_EDAMAM_APP_ID';
    }

    public static function nutritionAppKey(): string
    {
        $value = getenv('EDAMAM_APP_KEY');
        return $value !== false ? $value : 'YOUR_EDAMAM_APP_KEY';
    }
}
