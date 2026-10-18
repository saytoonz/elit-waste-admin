<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Website\WebsiteMenuItem;
use App\Models\Website\WebsiteSetting;
use App\Models\WeightLog;
use App\Models\Zone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Helpers shared by the public website templates and the CMS admin.
 */
class Website
{
    /** Cache a value for the current request only (never across requests or tests). */
    protected static function memo(string $key, callable $fn)
    {
        $bag = request()->attributes;
        if (!$bag->has("website.{$key}")) {
            $bag->set("website.{$key}", $fn());
        }
        return $bag->get("website.{$key}");
    }

    public static function settings(): array
    {
        return static::memo('settings', fn () => WebsiteSetting::allWithDefaults());
    }

    public static function setting(string $key, $default = null)
    {
        $value = static::settings()[$key] ?? null;
        return ($value === null || $value === '') ? $default : $value;
    }

    public static function flush(): void
    {
        foreach (array_keys(request()->attributes->all()) as $key) {
            if (str_starts_with($key, 'website.')) {
                request()->attributes->remove($key);
            }
        }
    }

    /**
     * Public URL for an image value: absolute URLs pass through, anything else
     * is treated as a path under public/ (images/site/x.jpg, storage/website/…).
     */
    public static function image(?string $value): ?string
    {
        if (!$value) return null;
        if (Str::startsWith($value, ['http://', 'https://', '//', 'data:'])) return $value;
        return asset(ltrim($value, '/'));
    }

    /**
     * Resolve a link typed in the editor: "/about" stays on this site (and keeps
     * working if the app lives in a sub-folder), full URLs, tel:, mailto: and #anchors pass through.
     */
    public static function href(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') return '#';
        if (Str::startsWith($value, ['http://', 'https://', 'mailto:', 'tel:', '#', '//'])) return $value;
        return url($value);
    }

    public static function isExternal(?string $value): bool
    {
        return Str::startsWith((string) $value, ['http://', 'https://', '//'])
            && !Str::startsWith((string) $value, url('/'));
    }

    /** "#730D9B" → "115 13 155" for Tailwind's rgb(var(--x) / alpha) colours. */
    public static function rgb(?string $hex, string $fallback = '115 13 155'): string
    {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) return $fallback;
        return implode(' ', array_map('hexdec', str_split($hex, 2)));
    }

    /** Plain text with blank-line paragraphs → escaped <p> blocks. */
    public static function paragraphs(?string $text, string $class = ''): HtmlString
    {
        $blocks = preg_split("/\R{2,}/", trim((string) $text)) ?: [];
        $html = '';
        foreach ($blocks as $block) {
            if (trim($block) === '') continue;
            $html .= '<p' . ($class ? ' class="' . e($class) . '"' : '') . '>' . nl2br(e(trim($block))) . '</p>';
        }
        return new HtmlString($html);
    }

    /** Markdown from the editor → safe HTML (raw HTML is escaped, unsafe links dropped). */
    public static function markdown(?string $text): HtmlString
    {
        return new HtmlString(Str::markdown((string) $text, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]));
    }

    public static function menu(string $location)
    {
        return static::memo("menu.{$location}", fn () => WebsiteMenuItem::with('page')
            ->where('location', $location)
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->filter(fn ($item) => !$item->page || $item->page->status === 'Published')
            ->values());
    }

    /**
     * Live numbers editors can drop into stats, e.g. "{customers}+" or "{tonnes} t".
     */
    public static function tokens(): array
    {
        return Cache::remember('website.tokens', 600, function () {
            $safe = function (callable $fn) {
                try { return $fn(); } catch (\Throwable $e) { return 0; }
            };
            return [
                '{customers}' => number_format($safe(fn () => Customer::count())),
                '{active_customers}' => number_format($safe(fn () => Customer::where('is_active', true)->count())),
                '{zones}' => number_format($safe(fn () => Zone::where('is_active', true)->count())),
                '{tonnes}' => number_format($safe(fn () => WeightLog::sum('net_weight')) / 1000, 1),
                '{weight}' => static::formatWeight((float) $safe(fn () => WeightLog::sum('net_weight'))),
            ];
        });
    }

    /** 850 → "850 kg", 12400 → "12.4 t" */
    public static function formatWeight(float $kg): string
    {
        return $kg < 1000 ? number_format($kg) . ' kg' : number_format($kg / 1000, 1) . ' t';
    }

    public static function fill(?string $text): string
    {
        $text = (string) $text;
        if (!str_contains($text, '{')) return $text;
        return strtr($text, static::tokens());
    }

    public static function youtubeId(?string $url): ?string
    {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/))([\w-]{11})~', (string) $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /** Background classes for a section's "background" option. */
    public static function bg(?string $background): string
    {
        return match ($background) {
            'light' => 'bg-brand/5 text-gray-900',
            'dark'  => 'bg-brand-dark text-white',
            'brand' => 'bg-brand text-white',
            default => 'bg-white text-gray-900',
        };
    }

    public static function isDarkBg(?string $background): bool
    {
        return in_array($background, ['dark', 'brand'], true);
    }
}
