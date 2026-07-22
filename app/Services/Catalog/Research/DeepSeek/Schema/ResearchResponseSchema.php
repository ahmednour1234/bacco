<?php

namespace App\Services\Catalog\Research\DeepSeek\Schema;


/**
 * The JSON schema (as a PHP structure) that every research response must match.
 * Kept declarative so the validator stays generic and the contract is readable.
 *
 * Supported node forms:
 *   ['type' => 'object', 'properties' => [...], 'required' => [...]]
 *   ['type' => 'array', 'items' => <node>]
 *   ['type' => 'string'|'number'|'integer'|'boolean']
 *   ['type' => ['string','null']]         (union — null allowed)
 *   ['type' => 'string', 'enum' => [...]] (allowed values)
 */
class ResearchResponseSchema
{
    /** @return array<string,mixed> */
    public static function definition(): array
    {
        $nullableString = ['type' => ['string', 'null']];
        $nullableNumber = ['type' => ['number', 'null']];

        $source = [
            'type'       => 'object',
            'required'   => ['title', 'url', 'source_type', 'is_official'],
            'properties' => [
                'title'           => ['type' => 'string'],
                'url'             => ['type' => 'string'],
                'source_type'     => ['type' => 'string'],
                'is_official'     => ['type' => 'boolean'],
                'supports_fields' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ];

        $approval = [
            'type'       => 'object',
            'required'   => ['name'],
            'properties' => [
                'name'  => ['type' => 'string'],
                'code'  => $nullableString,
                'scope' => $nullableString,
            ],
        ];

        $variant = [
            'type'       => 'object',
            'required'   => ['verification_level'],
            'properties' => [
                'manufacturer_sku'         => $nullableString,
                'manufacturer_part_number' => $nullableString,
                'size'                     => $nullableString,
                'dn_size'                  => $nullableString,
                'connection'               => $nullableString,
                'connection_standard'      => $nullableString,
                'pressure_rating'          => $nullableString,
                'temperature_min'          => $nullableNumber,
                'temperature_max'          => $nullableNumber,
                'temperature_unit'         => $nullableString,
                'approvals'                => ['type' => 'array', 'items' => $approval],
                'standards'                => ['type' => 'array', 'items' => ['type' => 'string']],
                // These are coerced to valid enum values by the parser before
                // validation, so we accept any string here rather than reject an
                // otherwise-good product list over a wording difference.
                'verification_level'       => ['type' => 'string'],
                'availability_status'      => ['type' => ['string', 'null']],
                'sources'                  => ['type' => 'array', 'items' => $source],
            ],
        ];

        $model = [
            'type'       => 'object',
            'properties' => [
                'model_number'   => $nullableString,
                'body_material'  => $nullableString,
                'ball_material'  => $nullableString,
                'seat_material'  => $nullableString,
                'port_type'      => $nullableString,
                'pieces'         => ['type' => ['integer', 'null']],
                'operation_type' => $nullableString,
                'variants'       => ['type' => 'array', 'items' => $variant],
            ],
        ];

        $series = [
            'type'       => 'object',
            'required'   => ['series_name'],
            'properties' => [
                'series_name'           => ['type' => 'string'],
                'official_product_name' => $nullableString,
                'official_page_url'     => $nullableString,
                'models'                => ['type' => 'array', 'items' => $model],
            ],
        ];

        return [
            'type'       => 'object',
            // `series` is not required at the schema level — the parser defaults
            // a missing/empty series to [] so a "found nothing" answer is a valid
            // empty result, not a rejected one.
            'required'   => [],
            'properties' => [
                'product_family' => [
                    'type'       => ['object', 'null'],
                    'properties' => [
                        'name'            => ['type' => 'string'],
                        'normalized_name' => ['type' => 'string'],
                    ],
                ],
                'manufacturer' => [
                    'type'       => ['object', 'null'],
                    'properties' => [
                        'name'             => ['type' => 'string'],
                        'official_website' => $nullableString,
                        'country'          => $nullableString,
                    ],
                ],
                'series'           => ['type' => 'array', 'items' => $series],
                'unverified_items' => ['type' => 'array', 'items' => ['type' => 'object']],
                'warnings'         => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ];
    }
}
