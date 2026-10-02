<?php

namespace App\Models;

use App\Core\Database;

/**
 * Base active-record style model. Rows are returned as Model instances that
 * support both property access ($row->name) and array access ($row['name']),
 * so existing views and controllers keep working untouched.
 */
abstract class Model implements \ArrayAccess, \JsonSerializable
{
    protected static string $table = '';
    protected static array $fillable = [];

    protected array $attributes = [];

    public function __construct(?array $attributes = null)
    {
        $this->attributes = $attributes ?? [];
    }

    public static function table(): string
    {
        return static::$table;
    }

    /** Hydrate a set of rows (plain arrays) into Model instances. */
    protected static function hydrate(array $rows): array
    {
        $models = [];
        foreach ($rows as $row) {
            $models[] = new static($row);
        }
        return $models;
    }

    protected static function scoped(array $data): array
    {
        $fillable = static::$fillable;
        $allowed = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fillable, true)) {
                $allowed[$key] = $value;
            }
        }
        return $allowed;
    }

    public static function find(int $id): static|null
    {
        $row = Database::first("SELECT * FROM `" . static::$table . "` WHERE id = ?", [$id]);
        return $row ? new static($row) : null;
    }

    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        if (!$model) {
            app_response()->abort(404, 'Record not found.');
        }
        return $model;
    }

    public static function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $rows = Database::select(
            "SELECT * FROM `" . static::$table . "` ORDER BY `" . $orderBy . "` " . ($direction === 'ASC' ? 'ASC' : 'DESC')
        );
        return static::hydrate($rows);
    }

    public static function whereFirst(array $conditions): static|null
    {
        [$where, $bind] = static::buildWhere($conditions);
        $row = Database::first("SELECT * FROM `" . static::$table . "` WHERE " . $where . " LIMIT 1", $bind);
        return $row ? new static($row) : null;
    }

    public static function where(array $conditions, string $orderBy = 'id', string $direction = 'ASC'): array
    {
        [$where, $bind] = static::buildWhere($conditions);
        $rows = Database::select(
            "SELECT * FROM `" . static::$table . "` WHERE " . $where
            . " ORDER BY `" . $orderBy . "` " . ($direction === 'ASC' ? 'ASC' : 'DESC'),
            $bind
        );
        return static::hydrate($rows);
    }

    public static function create(array $data): int
    {
        $data = static::scoped($data);
        $data['created_at'] = $data['created_at'] ?? now();
        $fields = array_keys($data);
        $columns = implode(',', array_map(fn($f) => "`$f`", $fields));
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO `" . static::$table . "` ($columns) VALUES ($placeholders)";
        Database::run($sql, array_values($data));
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data = static::scoped($data);
        if (empty($data)) {
            return;
        }
        $sets = implode(',', array_map(fn($f) => "`$f` = ?", array_keys($data)));
        $bind = array_values($data);
        $data['updated_at'] = now();
        $sets .= ', `updated_at` = ?';
        $bind[] = $data['updated_at'];
        Database::run("UPDATE `" . static::$table . "` SET " . $sets . " WHERE id = ?", [...$bind, $id]);
    }

    public static function delete(int $id): void
    {
        Database::run("DELETE FROM `" . static::$table . "` WHERE id = ?", [$id]);
    }

    public static function count(array $conditions = []): int
    {
        [$where, $bind] = static::buildWhere($conditions);
        $row = Database::first("SELECT COUNT(*) c FROM `" . static::$table . "` WHERE " . $where, $bind);
        return (int)($row['c'] ?? 0);
    }

    public static function sum(string $column, array $conditions = []): int|float
    {
        [$where, $bind] = static::buildWhere($conditions);
        $row = Database::first("SELECT SUM(`$column`) s FROM `" . static::$table . "` WHERE " . $where, $bind);
        return (float)($row['s'] ?? 0);
    }

    public static function paginate(string $sql, array $bind, int $perPage = 15, int $page = 1): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $countSql = preg_replace('#SELECT\s+.+?\s+FROM#i', 'SELECT COUNT(*) c FROM', $sql, 1);
        $total = (int)(Database::first($countSql, $bind)['c'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $rows = Database::select($sql . " LIMIT $perPage OFFSET $offset", $bind);
        $items = static::hydrate($rows);
        $lastPage = (int)ceil($total / $perPage);

        $base = preg_replace('/[?&]page=\d+/', '', $_SERVER['REQUEST_URI'] ?? '/');
        $query = parse_url($base, PHP_URL_QUERY);
        $base = strtok($base, '?');
        $separator = $query ? '&' : '?';

        return compact('items', 'total', 'page', 'perPage', 'lastPage', 'base', 'query', 'separator');
    }

    protected static function buildWhere(array $conditions): array
    {
        if (empty($conditions)) {
            return ['1', []];
        }
        $clauses = [];
        $bind = [];
        foreach ($conditions as $column => $value) {
            $clauses[] = "`$column` = ?";
            $bind[] = $value;
        }
        return [implode(' AND ', $clauses), $bind];
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function fill(array $data): static
    {
        $this->attributes = array_merge($this->attributes, $data);
        return $this;
    }

    /* ------------------------------------------------------------------ */
    /* ArrayAccess                                                         */
    /* ------------------------------------------------------------------ */

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /* ------------------------------------------------------------------ */
    /* Magic property access                                               */
    /* ------------------------------------------------------------------ */

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /* ------------------------------------------------------------------ */
    /* JsonSerializable                                                    */
    /* ------------------------------------------------------------------ */

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }
}