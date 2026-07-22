<?php

namespace App\Services\Catalog\Research\DeepSeek\Schema;

/**
 * A small, dependency-free JSON-schema validator supporting exactly the subset
 * used by ResearchResponseSchema (object/array/scalar, nullable unions, enums,
 * required keys). No composer package is added — this keeps the module runnable
 * with the currently-installed dependencies only.
 *
 * Returns a list of human-readable error strings; empty means valid.
 */
class JsonSchemaValidator
{
    /** @var list<string> */
    private array $errors = [];

    /**
     * @param  array<string,mixed>  $schema
     * @return list<string>  empty when valid
     */
    public function validate(mixed $data, array $schema): array
    {
        $this->errors = [];
        $this->check($data, $schema, '$');

        return $this->errors;
    }

    /** @param array<string,mixed> $schema */
    private function check(mixed $data, array $schema, string $path): void
    {
        $types = (array) ($schema['type'] ?? []);

        if ($types !== [] && ! $this->matchesAnyType($data, $types)) {
            $this->errors[] = sprintf(
                '%s: expected %s, got %s',
                $path,
                implode('|', array_map(fn ($t) => $t ?? 'null', $types)),
                get_debug_type($data)
            );

            return; // no point descending on a type mismatch
        }

        // null is a valid terminal when allowed.
        if ($data === null) {
            return;
        }

        // enum
        if (isset($schema['enum']) && ! in_array($data, $schema['enum'], true)) {
            $this->errors[] = sprintf('%s: value "%s" not in allowed set', $path, is_scalar($data) ? $data : get_debug_type($data));
        }

        if (in_array('object', $types, true) && is_array($data)) {
            foreach (($schema['required'] ?? []) as $req) {
                if (! array_key_exists($req, $data)) {
                    $this->errors[] = sprintf('%s: missing required key "%s"', $path, $req);
                }
            }
            foreach (($schema['properties'] ?? []) as $key => $sub) {
                if (array_key_exists($key, $data)) {
                    $this->check($data[$key], $sub, $path . '.' . $key);
                }
            }
        }

        if (in_array('array', $types, true) && is_array($data) && isset($schema['items'])) {
            foreach ($data as $i => $item) {
                $this->check($item, $schema['items'], $path . '[' . $i . ']');
            }
        }
    }

    /** @param list<?string> $types */
    private function matchesAnyType(mixed $data, array $types): bool
    {
        foreach ($types as $type) {
            $ok = match ($type) {
                null      => $data === null,
                'null'    => $data === null,
                'string'  => is_string($data),
                'number'  => is_int($data) || is_float($data),
                'integer' => is_int($data),
                'boolean' => is_bool($data),
                'array'   => is_array($data),
                'object'  => is_array($data), // JSON objects decode to assoc arrays
                default   => false,
            };
            if ($ok) {
                return true;
            }
        }

        return false;
    }
}
