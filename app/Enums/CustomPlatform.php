<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum CustomPlatform
{
  case Windows;
  case Linux;
  case Mac;
  case Mobile;
  case Other;

  public static function detect(): self
  {
    $userAgent = request()->userAgent();

    $platformPatterns = [
      'Mobile' => ['Mobile', 'iPhone', 'Android', 'iPad', 'BlackBerry', 'Windows Phone'],
      'Windows' => ['Windows'],
      'Mac' => ['Mac'],
      'Linux' => ['Linux'],
    ];

    foreach ($platformPatterns as $platform => $patterns) {
      if (Str::contains($userAgent, $patterns)) {
        return self::getPlatform($platform);
      }
    }

    return self::Other;
  }

  private static function getPlatform(string $platform): self
  {
    return match ($platform) {
      'Mobile' => self::Mobile,
      'Windows' => self::Windows,
      'Mac' => self::Mac,
      'Linux' => self::Linux,
      default => self::Other,
    };
  }
}