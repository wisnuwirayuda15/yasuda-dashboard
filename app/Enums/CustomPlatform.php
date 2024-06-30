<?php

namespace App\Enums;

enum CustomPlatform
{
  case Windows;

  case Linux;

  case Mac;

  case Mobile;

  case Other;

  public static function detect(): CustomPlatform
  {
    $userAgent = request()->userAgent();

    return match (true) {
      str_contains($userAgent, 'Mobile') ||
      str_contains($userAgent, 'iPhone') ||
      str_contains($userAgent, 'Android') ||
      str_contains($userAgent, 'iPad') ||
      str_contains($userAgent, 'BlackBerry') ||
      str_contains($userAgent, 'Windows Phone') => self::Mobile,
      str_contains($userAgent, 'Windows') => self::Windows,
      str_contains($userAgent, 'Mac') => self::Mac,
      str_contains($userAgent, 'Linux') => self::Linux,
      default => self::Other,
    };
  }
}


