<?php

namespace App\Core;

class Validator
{
    private array $errors = [];

    /**
     * Validate $data against rules. Rules: required, email, numeric, integer,
     * min:n, max:n, date, date_after:field, in:a,b,c, unique:table,column,id
     */
    public function validate(array $data, array $rules): array
    {
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $ruleList = array_map('trim', explode('|', $ruleString));

            foreach ($ruleList as $rule) {
                $name = $rule;
                $params = [];
                if (str_contains($rule, ':')) {
                    [$name, $arg] = explode(':', $rule, 2);
                    $params = explode(',', $arg);
                }

                $this->checkField($field, $value, $name, $params);
            }
        }

        if ($this->errors) {
            $_SESSION['_errors'] = $this->errors;
        }

        return $this->errors;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public static function errorFor(string $field): ?string
    {
        return $_SESSION['_errors'][$field] ?? null;
    }

    public static function clear(): void
    {
        unset($_SESSION['_errors'], $_SESSION['_old_input']);
    }

    private function checkField(string $field, mixed $value, string $rule, array $params): void
    {
        $label = ucwords(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($this->isEmpty($value)) {
                    $this->add($field, "$label is required.");
                }
                break;

            case 'email':
                if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->add($field, "$label must be a valid email.");
                }
                break;

            case 'numeric':
                if (!$this->isEmpty($value) && !is_numeric($value)) {
                    $this->add($field, "$label must be a number.");
                }
                break;

            case 'integer':
                if (!$this->isEmpty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->add($field, "$label must be a whole number.");
                }
                break;

            case 'decimal':
                if (!$this->isEmpty($value) && !is_numeric($value)) {
                    $this->add($field, "$label must be a number.");
                }
                break;

            case 'min':
                if (!$this->isEmpty($value) && is_numeric($value) && (float)$value < (float)$params[0]) {
                    $this->add($field, "$label must be at least {$params[0]}.");
                } elseif (!$this->isEmpty($value) && !is_numeric($value) && mb_strlen((string)$value) < (int)$params[0]) {
                    $this->add($field, "$label must be at least {$params[0]} characters.");
                }
                break;

            case 'max':
                if (!$this->isEmpty($value) && mb_strlen((string)$value) > (int)$params[0]) {
                    $this->add($field, "$label must not exceed {$params[0]} characters.");
                }
                break;

            case 'date':
                if (!$this->isEmpty($value) && !$this->isValidDate((string)$value)) {
                    $this->add($field, "$label must be a valid date.");
                }
                break;

            case 'after_or_equal':
                if (!$this->isEmpty($value) && isset($params[0])) {
                    $other = $_POST[$params[0]] ?? null;
                    if ($other && strtotime($value) < strtotime($other)) {
                        $this->add($field, "$label must not be earlier than " . ucwords(str_replace('_', ' ', $params[0])) . '.');
                    }
                }
                break;

            case 'in':
                if (!$this->isEmpty($value) && !in_array($value, $params, true)) {
                    $this->add($field, "$label has an invalid value.");
                }
                break;

            case 'unique':
                if ($this->isEmpty($value)) {
                    break;
                }
                [$table, $column] = $params;
                $exclude = $params[2] ?? null;
                $sql = "SELECT COUNT(*) c FROM `$table` WHERE `$column` = ?";
                $bind = [$value];
                if ($exclude) {
                    $sql .= " AND id != ?";
                    $bind[] = (int)$exclude;
                }
                $count = (int)\App\Core\Database::first($sql, $bind)['c'];
                if ($count > 0) {
                    $this->add($field, "$label already exists.");
                }
                break;
        }
    }

    private function add(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || (is_array($value) && count($value) === 0);
    }

    private function isValidDate(string $date): bool
    {
        $time = strtotime($date);
        if ($time === false) {
            return false;
        }
        $d = date('Y-m-d', $time);
        return $d === $date || $d === date('Y-m-d', strtotime($date));
    }
}