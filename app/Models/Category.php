<?php

namespace App\Models;

class Category extends Model
{
    protected static string $table = 'asset_categories';
    protected static array $fillable = ['name', 'code', 'description'];

    public static function assetCount(int $id): int
    {
        return Asset::count(['category_id' => $id]);
    }

    public static function nextCode(int $id): string
    {
        $cat = static::find($id);
        $code = strtoupper($cat['code'] ?? 'IT');
        $last = Asset::whereFirst(['category_id' => $id]);
        // Use last asset id in this category to build the sequence.
        $row = \App\Core\Database::first("SELECT MAX(id) max_id FROM assets WHERE category_id = ?", [$id]);
        $seq = (int)($row['max_id'] ?? 0) + 1;
        return $code . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    /** Full asset code scaffold for a new category. */
    public static function pickupPrefix(): string
    {
        return 'IT';
    }
}