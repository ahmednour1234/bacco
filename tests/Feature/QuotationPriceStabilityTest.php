<?php

namespace Tests\Feature;

use App\Models\BoqAnswerResult;
use App\Models\BoqParseResult;
use App\Models\QuotationItem;
use App\Models\Unit;
use App\Services\PricingService;
use App\Support\AiCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The same BOQ must produce the same quotation twice.
 *
 * This is the behaviour the whole reuse chain exists for, and it broke in six
 * different ways before it held. Each test below pins one link of that chain, so
 * a regression names itself instead of resurfacing as "the prices changed again".
 */
class QuotationPriceStabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Any real call would make these tests depend on the model's sampling,
        // which is the very thing they exist to insulate against.
        Http::preventStrayRequests();

        AiCache::flushResolved();
    }

    public function test_a_previously_quoted_product_is_priced_from_the_database(): void
    {
        $unit = Unit::create(['name' => 'PCS', 'symbol' => 'pcs']);

        // A row priced on an earlier quotation, still inside the freshness window.
        QuotationItem::create([
            'quotation_request_id' => null,
            'description'          => 'Wooden Office Desk 160 cm',
            'quantity'             => 1,
            'unit_id'              => $unit->id,
            'unit_price'           => 950.00,
            'price_source'         => 'ai',
        ]);

        $priced = app(PricingService::class)->fetchPrices([
            ['description' => 'Wooden Office Desk 160 cm', 'unit' => 'PCS', 'quantity' => 10],
        ]);

        $this->assertSame(950.00, $priced[0]['unit_price']);
        $this->assertSame('previous_quotation', $priced[0]['price_source']);
    }

    public function test_case_and_spacing_differences_still_match(): void
    {
        $unit = Unit::create(['name' => 'PCS', 'symbol' => 'pcs']);

        QuotationItem::create([
            'quotation_request_id' => null,
            'description'          => 'Wooden Office Desk',
            'quantity'             => 1,
            'unit_id'              => $unit->id,
            'unit_price'           => 900.00,
        ]);

        // The extractor does not return identical casing every run, so a price
        // that only matches an exact string would still drift.
        $priced = app(PricingService::class)->fetchPrices([
            ['description' => '  wooden   OFFICE   desk ', 'unit' => 'pcs', 'quantity' => 10],
        ]);

        $this->assertSame(900.00, $priced[0]['unit_price']);
    }

    public function test_a_different_size_is_treated_as_a_different_product(): void
    {
        $unit = Unit::create(['name' => 'PCS', 'symbol' => 'pcs']);

        QuotationItem::create([
            'quotation_request_id' => null,
            'description'          => 'Wooden Office Desk',
            'quantity'             => 1,
            'unit_id'              => $unit->id,
            'unit_price'           => 900.00,
        ]);

        // Quoting a 160 cm desk at the price of an unspecified one would quote
        // the wrong item, so this must NOT match.
        $priced = app(PricingService::class)->fetchPrices([
            ['description' => 'Wooden Office Desk 160 cm', 'unit' => 'PCS', 'quantity' => 10],
        ]);

        $this->assertNull($priced[0]['unit_price']);
    }

    public function test_a_stale_price_is_not_reused(): void
    {
        $unit = Unit::create(['name' => 'PCS', 'symbol' => 'pcs']);

        $item = QuotationItem::create([
            'quotation_request_id' => null,
            'description'          => 'UPS 3 KVA Online',
            'quantity'             => 1,
            'unit_id'              => $unit->id,
            'unit_price'           => 2500.00,
        ]);

        // Past the freshness window the market may genuinely have moved, and a
        // stale figure is worse than a current one.
        $item->forceFill(['updated_at' => now()->subDays(30)])->saveQuietly();

        $priced = app(PricingService::class)->fetchPrices([
            ['description' => 'UPS 3 KVA Online', 'unit' => 'PCS', 'quantity' => 4],
        ]);

        $this->assertNull($priced[0]['unit_price']);
    }

    public function test_the_same_file_reuses_its_parse_and_questions(): void
    {
        $hash = hash('sha256', 'THE-SAME-BOQ-BYTES');

        BoqParseResult::remember($hash, [
            ['description' => 'CAT6 UTP Cable', 'unit' => 'm', 'quantity' => 800],
        ], 'boq.xlsx', 1024);

        BoqParseResult::rememberQuestions($hash, [['question' => 'Which cable grade?']]);

        $stored = BoqParseResult::forHash($hash);

        $this->assertCount(1, $stored->items);
        $this->assertCount(1, $stored->questions);
        $this->assertSame('CAT6 UTP Cable', $stored->items[0]['description']);
    }

    public function test_the_same_answers_produce_the_same_key(): void
    {
        $a = [1 => ['choice' => 'M3', 'custom' => ''], 0 => ['choice' => 'PCS', 'custom' => '']];
        $b = [0 => ['choice' => 'PCS', 'custom' => ''], 1 => ['choice' => 'M3', 'custom' => '']];

        // Order must not matter: the same choices asked in a different sequence
        // are the same answers.
        $this->assertSame(
            BoqAnswerResult::hashAnswers($a),
            BoqAnswerResult::hashAnswers($b),
        );

        // An empty set has to be stable too, or a BOQ with no questions could
        // never be reused — which is exactly what happened in production.
        $this->assertSame(
            BoqAnswerResult::hashAnswers([]),
            BoqAnswerResult::hashAnswers([]),
        );

        $this->assertNotSame(
            BoqAnswerResult::hashAnswers($a),
            BoqAnswerResult::hashAnswers([0 => ['choice' => 'BOX', 'custom' => '']]),
        );
    }

    public function test_a_stored_priced_result_is_found_for_the_same_file_and_answers(): void
    {
        $fileHash = hash('sha256', 'THE-SAME-BOQ-BYTES');
        $ansHash  = BoqAnswerResult::hashAnswers([]);

        BoqAnswerResult::remember($fileHash, $ansHash, [
            ['description' => 'CAT6 UTP Cable', 'unit' => 'm', 'unit_price' => 4.00],
        ]);

        $this->assertNotNull(BoqAnswerResult::lookup($fileHash, $ansHash));

        // A different answer set is a different quotation, and must miss.
        $this->assertNull(BoqAnswerResult::lookup(
            $fileHash,
            BoqAnswerResult::hashAnswers([0 => ['choice' => 'BOX', 'custom' => '']]),
        ));
    }
}
