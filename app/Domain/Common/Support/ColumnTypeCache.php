<?php

namespace App\Domain\Common\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ColumnTypeCache
{
    public static function key(string $table): string
    {
        return "column_types_{$table}";
    }

    public static function getForModel(Model $model): array
    {
        $table = $model->getTable();

        return Cache::rememberForever(self::key($table), function () use ($table) {
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            $types = [];

            foreach ($columns as $column) {
                $type = DB::getSchemaBuilder()->getColumnType($table, $column);

                $types[$column] = match ($type) {
                    'string', 'text' => 'string',
                    'integer', 'bigint', 'decimal', 'float', 'double' => 'number',
                    'date', 'datetime', 'timestamp' => 'date',
                    default => 'string',
                };
            }

            return $types;
        });
    }

    public static function clear(string $table): void
    {
        Cache::forget(self::key($table));
    }

    public static function clearAll(): void
    {
        $tables = DB::getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            self::clear($table);
        }
    }
}
