<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'encrypted',
    ];

    /**
     * Decrypt-safe lookup. Returns the value, or null when the row is missing
     * or the ciphertext cannot be decrypted (e.g. APP_KEY mismatch).
     */
    public static function safeValue(string $key, $default = null)
    {
        try {
            $value = static::where('key', $key)->value('value');
            return $value !== null ? $value : $default;
        } catch (DecryptException $e) {
            return $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Pluck all settings as [key => value], skipping any rows that fail to decrypt.
     */
    public static function safeAll(): array
    {
        $out = [];
        foreach (static::all(['id', 'key', 'value']) as $row) {
            try {
                $out[$row->key] = $row->value;
            } catch (DecryptException $e) {
                $out[$row->key] = null;
            } catch (\Throwable $e) {
                $out[$row->key] = null;
            }
        }
        return $out;
    }
}
