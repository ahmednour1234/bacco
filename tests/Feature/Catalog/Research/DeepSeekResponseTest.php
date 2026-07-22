<?php

namespace Tests\Feature\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use App\Services\Catalog\Research\DeepSeek\FakeResearchProvider;
use App\Services\Catalog\Research\DeepSeek\ResearchResponseParser;
use App\Services\Catalog\Research\DeepSeek\Schema\JsonSchemaValidator;
use App\Services\Catalog\Research\DeepSeek\Schema\ResearchResponseSchema;
use Tests\TestCase;

/**
 * Parser, JSON-schema validation and fake-provider coverage for Phase 3.
 * No real API calls are ever made.
 */
class DeepSeekResponseTest extends TestCase
{
    /** The canonical example from the spec must validate cleanly. */
    private function sampleJson(): string
    {
        return json_encode([
            'product_family' => ['name' => 'Ball Valve (Brass)', 'normalized_name' => 'brass ball valve'],
            'manufacturer'   => ['name' => 'NIBCO', 'official_website' => 'https://www.nibco.com', 'country' => 'United States'],
            'series' => [[
                'series_name'           => 'KT-585-70-UL',
                'official_product_name' => 'Two-Piece Bronze Ball Valve',
                'official_page_url'     => 'https://example.com/kt585',
                'models' => [[
                    'model_number'   => 'KT-585-70-UL',
                    'body_material'  => 'Bronze ASTM B584 C84400',
                    'ball_material'  => 'Chrome-Plated Brass',
                    'seat_material'  => 'Reinforced PTFE',
                    'port_type'      => 'Full Port',
                    'pieces'         => 2,
                    'operation_type' => 'Quarter-Turn Lever',
                    'variants' => [[
                        'manufacturer_sku'    => 'NL95046',
                        'size'                => '1/2 inch',
                        'dn_size'             => 'DN15',
                        'connection'          => 'Female NPT',
                        'connection_standard' => 'NPT',
                        'pressure_rating'     => '300 PSI',
                        'approvals' => [
                            ['name' => 'UL Listed', 'code' => 'UL 258', 'scope' => 'Automatic Sprinkler Trim'],
                            ['name' => 'FM Approved', 'code' => null, 'scope' => 'Automatic Sprinkler Trim'],
                        ],
                        'standards'           => ['MSS SP-110'],
                        'verification_level'  => 'exact_manufacturer_sku',
                        'availability_status' => 'current',
                        'sources' => [[
                            'title' => 'Official NIBCO Product Page', 'url' => 'https://example.com/kt585',
                            'source_type' => 'official_product_page', 'is_official' => true,
                            'supports_fields' => ['manufacturer_sku', 'size', 'pressure_rating', 'approvals'],
                        ]],
                    ]],
                ]],
            ]],
            'unverified_items' => [],
            'warnings'         => [],
        ], JSON_UNESCAPED_SLASHES);
    }

    public function test_valid_sample_response_parses_and_validates(): void
    {
        $parser   = app(ResearchResponseParser::class);
        $response = $parser->parse($this->sampleJson());

        $this->assertTrue($response->valid, implode('; ', $response->validationErrors));
        $this->assertCount(1, $response->series());
        $variant = $response->data['series'][0]['models'][0]['variants'][0];
        $this->assertSame('NL95046', $variant['manufacturer_sku']);
        $this->assertSame('exact_manufacturer_sku', $variant['verification_level']);
    }

    public function test_ul258_and_ul842_are_kept_distinct_in_payload(): void
    {
        $parser = app(ResearchResponseParser::class);
        $json   = json_decode($this->sampleJson(), true);
        // Add a UL 842 approval alongside UL 258 on the same variant.
        $json['series'][0]['models'][0]['variants'][0]['approvals'][] =
            ['name' => 'UL Listed', 'code' => 'UL 842', 'scope' => 'Flammable Fluids'];

        $response = $parser->parse(json_encode($json));
        $codes = array_column($response->data['series'][0]['models'][0]['variants'][0]['approvals'], 'code');

        $this->assertContains('UL 258', $codes);
        $this->assertContains('UL 842', $codes);
    }

    public function test_markdown_fenced_json_is_still_parsed(): void
    {
        $parser   = app(ResearchResponseParser::class);
        $response = $parser->parse("```json\n" . $this->sampleJson() . "\n```");

        $this->assertTrue($response->valid);
    }

    public function test_non_json_is_rejected(): void
    {
        $parser   = app(ResearchResponseParser::class);
        $response = $parser->parse('Sorry, I could not find any products.');

        $this->assertFalse($response->valid);
        $this->assertNotEmpty($response->validationErrors);
    }

    public function test_missing_verification_level_fails_schema(): void
    {
        $validator = app(JsonSchemaValidator::class);
        $data = [
            'series' => [[
                'series_name' => 'X',
                'models' => [[
                    'variants' => [[
                        // verification_level intentionally omitted
                        'size' => '1/2 inch',
                    ]],
                ]],
            ]],
        ];

        $errors = $validator->validate($data, ResearchResponseSchema::definition());
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('verification_level', implode(' ', $errors));
    }

    public function test_invalid_verification_level_enum_fails_schema(): void
    {
        $validator = app(JsonSchemaValidator::class);
        $data = [
            'series' => [[
                'series_name' => 'X',
                'models' => [[
                    'variants' => [[
                        'verification_level' => 'totally_made_up',
                    ]],
                ]],
            ]],
        ];

        $errors = $validator->validate($data, ResearchResponseSchema::definition());
        $this->assertNotEmpty($errors);
    }

    public function test_fake_provider_returns_queued_response_without_network(): void
    {
        /** @var FakeResearchProvider $fake */
        $fake = app(FakeResearchProvider::class);
        $fake->queueRaw($this->sampleJson());

        $request = ResearchRequest::make(
            ResearchJobTypeEnum::DiscoverVariants,
            'Ball Valve (Brass)',
            'brass ball valve',
        );

        $response = $fake->research($request);

        $this->assertTrue($response->valid);
        $this->assertCount(1, $fake->received);
        $this->assertSame('Ball Valve (Brass)', $fake->received[0]->familyName);
    }

    public function test_fake_provider_defaults_to_empty_valid_response(): void
    {
        /** @var FakeResearchProvider $fake */
        $fake = app(FakeResearchProvider::class);

        $response = $fake->research(ResearchRequest::make(
            ResearchJobTypeEnum::DiscoverManufacturers, 'Gate Valve', 'gate valve'
        ));

        $this->assertTrue($response->valid);
        $this->assertSame([], $response->series());
    }
}
