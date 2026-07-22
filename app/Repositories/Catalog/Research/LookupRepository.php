<?php

namespace App\Repositories\Catalog\Research;

use App\Models\Catalog\Research\Approval;
use App\Models\Catalog\Research\CatalogDivision;
use App\Models\Catalog\Research\CatalogResearchCategory;
use App\Models\Catalog\Research\ConnectionStandard;
use App\Models\Catalog\Research\ConnectionType;
use App\Models\Catalog\Research\Manufacturer;
use App\Models\Catalog\Research\Material;
use App\Models\Catalog\Research\PortType;
use App\Models\Catalog\Research\PressureRating;
use App\Models\Catalog\Research\ProductSize;
use App\Models\Catalog\Research\Standard;
use App\Models\Catalog\Research\Unit;
use Illuminate\Support\Str;

/**
 * Resolves (find-or-create) the shared dictionary records used during import
 * and research. Every lookup is idempotent by its normalized key so repeated
 * imports never create duplicates. Aliases seeded on dictionaries let raw
 * variants (NPT / N.P.T. / FNPT) resolve to a single canonical record.
 */
class LookupRepository
{
    /** @var array<string,int> in-request memo: "type|key" => id */
    private array $memo = [];

    public function division(string $name): CatalogDivision
    {
        $slug = Str::slug($name) ?: 'division';

        return CatalogDivision::firstOrCreate(
            ['slug' => $slug],
            ['name' => trim($name)]
        );
    }

    public function category(int $divisionId, string $name, ?int $parentId = null): CatalogResearchCategory
    {
        $slug = Str::slug($name) ?: 'category';

        return CatalogResearchCategory::firstOrCreate(
            ['division_id' => $divisionId, 'slug' => $slug],
            ['name' => trim($name), 'parent_id' => $parentId]
        );
    }

    public function unit(string $raw): ?Unit
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $norm = $this->norm($raw);

        return Unit::whereRaw('LOWER(code) = ?', [$norm])
            ->orWhereRaw('LOWER(name) = ?', [$norm])
            ->first()
            ?? Unit::create([
                'code' => Str::upper(Str::limit($raw, 20, '')),
                'name' => $raw,
                'type' => 'count',
            ]);
    }

    public function material(string $raw): ?Material
    {
        return $this->resolveByAlias(Material::class, $raw, fn ($v) => [
            'name'            => $v,
            'normalized_name' => $this->norm($v),
        ]);
    }

    public function connectionType(string $raw): ?ConnectionType
    {
        return $this->resolveByAlias(ConnectionType::class, $raw, fn ($v) => [
            'name'            => $v,
            'normalized_name' => $this->norm($v),
        ]);
    }

    public function connectionStandard(string $raw): ?ConnectionStandard
    {
        return $this->resolveByAlias(ConnectionStandard::class, $raw, fn ($v) => [
            'name'            => $v,
            'normalized_name' => $this->norm($v),
        ]);
    }

    /**
     * Size resolution keeps the raw display value but keys on a normalized form
     * so 1 1/4", 1¼", 1.25 inch and DN32 collapse to one record.
     */
    public function size(string $raw, string $normalizedValue, array $attrs = []): ?ProductSize
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        return ProductSize::firstOrCreate(
            ['normalized_value' => $normalizedValue],
            array_merge(['display_value' => $raw], $attrs)
        );
    }

    public function pressure(string $raw, string $normalizedValue, array $attrs = []): ?PressureRating
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        return PressureRating::firstOrCreate(
            ['normalized_value' => $normalizedValue],
            array_merge(['rating_name' => $raw], $attrs)
        );
    }

    public function portType(string $raw): ?PortType
    {
        return $this->resolveByAlias(PortType::class, $raw, fn ($v) => [
            'name'            => $v,
            'normalized_name' => $this->norm($v),
        ]);
    }

    /**
     * Resolve an approval keyed by issuing_body + code so UL 258 (sprinkler
     * trim) and UL 842 (flammable fluids) are never conflated, even though both
     * share the name "UL Listed".
     */
    public function approval(string $name, ?string $code): ?Approval
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $body = $this->guessIssuingBody($name, $code);
        $key  = $this->norm(trim($body . ' ' . ($code ?? $name)));

        return Approval::firstOrCreate(
            ['normalized_key' => $key],
            [
                'name'          => $name,
                'issuing_body'  => $body,
                'approval_code' => $code,
            ]
        );
    }

    public function standard(string $code): ?Standard
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        return Standard::firstOrCreate(
            ['code' => $code],
            ['name' => $code]
        );
    }

    private function guessIssuingBody(string $name, ?string $code): string
    {
        $haystack = Str::lower($name . ' ' . ($code ?? ''));

        return match (true) {
            str_contains($haystack, 'ul')   => 'UL',
            str_contains($haystack, 'fm')   => 'FM',
            str_contains($haystack, 'lpcb') => 'LPCB',
            str_contains($haystack, 'wras') => 'WRAS',
            str_contains($haystack, 'nsf')  => 'NSF',
            str_contains($haystack, 'saso') => 'SASO',
            default                          => Str::title(Str::before($name, ' ')),
        };
    }

    public function manufacturer(string $raw, string $type = 'unknown'): ?Manufacturer
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $norm = $this->norm($raw);

        return Manufacturer::firstOrCreate(
            ['normalized_name' => $norm],
            [
                'name'              => $raw,
                'slug'             => Str::slug($raw),
                'manufacturer_type' => $type,
            ]
        );
    }

    /**
     * Generic alias-aware resolver for dictionary models that carry a JSON
     * `aliases` column and a unique `normalized_name`.
     *
     * @param  class-string  $modelClass
     */
    private function resolveByAlias(string $modelClass, string $raw, callable $makeAttrs)
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $norm = $this->norm($raw);
        $key  = $modelClass . '|' . $norm;

        if (isset($this->memo[$key])) {
            return $modelClass::find($this->memo[$key]);
        }

        // Exact normalized match first.
        $model = $modelClass::where('normalized_name', $norm)->first();

        // Then alias membership (JSON contains the normalized raw).
        if (! $model) {
            $model = $modelClass::query()
                ->get()
                ->first(fn ($m) => in_array($norm, array_map(
                    fn ($a) => $this->norm((string) $a),
                    (array) ($m->aliases ?? [])
                ), true));
        }

        if (! $model) {
            $model = $modelClass::create($makeAttrs($raw));
        }

        $this->memo[$key] = $model->id;

        return $model;
    }

    private function norm(string $value): string
    {
        return Str::of($value)->lower()->squish()->value();
    }
}
