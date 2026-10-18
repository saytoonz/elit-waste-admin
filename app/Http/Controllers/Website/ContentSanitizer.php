<?php

namespace App\Http\Controllers\Website;

/**
 * Cleans editor JSON against the field definitions in config/website.php so only
 * known keys with the expected shape are stored.
 */
class ContentSanitizer
{
    public static function clean(array $fields, $input): array
    {
        $input = is_array($input) ? $input : [];
        $out = [];

        foreach ($fields as $key => $field) {
            $value = $input[$key] ?? ($field['default'] ?? null);

            if (($field['type'] ?? 'text') === 'repeater') {
                $rows = [];
                foreach (is_array($value) ? $value : [] as $row) {
                    if (!is_array($row)) continue;
                    $rows[] = static::clean($field['fields'] ?? [], $row);
                }
                $out[$key] = array_values($rows);
                continue;
            }

            $out[$key] = static::scalar($field, $value);
        }

        return $out;
    }

    /** Empty row for each repeater, used by the editor's "Add item" button. */
    public static function blanks(array $fields): array
    {
        $blanks = [];
        foreach ($fields as $key => $field) {
            if (($field['type'] ?? 'text') === 'repeater') {
                $blanks[$key] = static::clean($field['fields'] ?? [], []);
            }
        }
        return $blanks;
    }

    protected static function scalar(array $field, $value)
    {
        if (is_array($value) || is_object($value)) return null;
        if ($value === null) return null;

        $value = (string) $value;
        $limit = in_array($field['type'] ?? 'text', ['markdown', 'textarea'], true) ? 20000 : 1000;
        $value = mb_substr($value, 0, $limit);

        return match ($field['type'] ?? 'text') {
            'number' => is_numeric($value) ? $value + 0 : null,
            'select' => array_key_exists($value, $field['options'] ?? []) ? $value : ($field['default'] ?? array_key_first($field['options'] ?? [])),
            'icon'   => in_array($value, config('website.icons'), true) ? $value : null,
            'color'  => preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : ($field['default'] ?? null),
            'url'    => static::url($value),
            default  => $value,
        };
    }

    /** Block javascript:/data: links; allow site paths, http(s), mailto, tel and anchors. */
    protected static function url(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') return null;
        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        if ($scheme && !in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) return null;
        return $value;
    }
}
