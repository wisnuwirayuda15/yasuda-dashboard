{{-- PushableButton.blade.php --}}
@props([
    'backgroundColor' => '#6C5CE7',
    'edgeColor' => '#483D8B',
    'shadowColor' => '#A29BFE',
    'textColor' => '#FFFFFF',
    'icon' => null,
])

@php
  $colorFunctions = new class {
      public function hexToRgb($hex)
      {
          $hex = ltrim($hex, '#');
          if (strlen($hex) == 3) {
              $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
          }
          $r = hexdec(substr($hex, 0, 2));
          $g = hexdec(substr($hex, 2, 2));
          $b = hexdec(substr($hex, 4, 2));
          return [$r, $g, $b];
      }

      public function rgbToHex($r, $g, $b)
      {
          return sprintf('#%02x%02x%02x', $r, $g, $b);
      }

      public function hslToRgb($h, $s, $l)
      {
          $h /= 60;
          $s /= 100;
          $l /= 100;

          $c = (1 - abs(2 * $l - 1)) * $s;
          $x = $c * (1 - abs(fmod($h, 2) - 1));
          $m = $l - $c / 2;

          if ($h < 1) {
              $r = $c;
              $g = $x;
              $b = 0;
          } elseif ($h < 2) {
              $r = $x;
              $g = $c;
              $b = 0;
          } elseif ($h < 3) {
              $r = 0;
              $g = $c;
              $b = $x;
          } elseif ($h < 4) {
              $r = 0;
              $g = $x;
              $b = $c;
          } elseif ($h < 5) {
              $r = $x;
              $g = 0;
              $b = $c;
          } else {
              $r = $c;
              $g = 0;
              $b = $x;
          }

          return [round(($r + $m) * 255), round(($g + $m) * 255), round(($b + $m) * 255)];
      }

      public function rgbToHsl($r, $g, $b)
      {
          $r /= 255;
          $g /= 255;
          $b /= 255;
          $max = max($r, $g, $b);
          $min = min($r, $g, $b);
          $l = ($max + $min) / 2;

          if ($max == $min) {
              $h = $s = 0;
          } else {
              $d = $max - $min;
              $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
              switch ($max) {
                  case $r:
                      $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                      break;
                  case $g:
                      $h = ($b - $r) / $d + 2;
                      break;
                  case $b:
                      $h = ($r - $g) / $d + 4;
                      break;
              }
              $h /= 6;
          }

          return [round($h * 360), round($s * 100), round($l * 100)];
      }

      public function adjustLightness($color, $amount)
      {
          if (strpos($color, '#') === 0) {
              $rgb = $this->hexToRgb($color);
              $hsl = $this->rgbToHsl(...$rgb);
          } elseif (preg_match('/^hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)$/', $color, $matches)) {
              $hsl = [intval($matches[1]), intval($matches[2]), intval($matches[3])];
          } else {
              return $color; // Return original color if format is not recognized
          }

          $hsl[2] = max(0, min(100, $hsl[2] + $amount));
          $rgb = $this->hslToRgb(...$hsl);
          return $this->rgbToHex(...$rgb);
      }

      public function lightenColor($color, $amount)
      {
          return $this->adjustLightness($color, $amount);
      }

      public function darkenColor($color, $amount)
      {
          return $this->adjustLightness($color, -$amount);
      }
  };
@endphp

<button {{ $attributes->merge(['class' => 'pushable-button']) }}>
  <span class="pushable-button__shadow" style="background: {{ $shadowColor }};"></span>
  <span class="pushable-button__edge" style="background: linear-gradient(
        to right,
        {{ $edgeColor }} 0%,
        {{ $colorFunctions->lightenColor($edgeColor, 10) }} 8%,
        {{ $edgeColor }} 92%,
        {{ $colorFunctions->darkenColor($edgeColor, 10) }} 100%
    );"></span>
  <span class="pushable-button__front space-x-3" style="background: {{ $backgroundColor }}; color: {{ $textColor }};">
    @if ($icon)
      <span>
        {{ svg($icon) }}
      </span>
    @endif
    <span>
      {{ $slot }}
    </span>
  </span>
</button>
