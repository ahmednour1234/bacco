<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The priced result of one (file, answer-set) combination.
 *
 * Looked up before pricing runs: if this exact file has been priced with this
 * exact set of answers before, the stored prices are reused instead of calling
 * the AI again. A different answer set for the same file is a different row and
 * a fresh price.
 */
class BoqAnswerResult extends Model
{
    protected $fillable = [
        'file_hash',
        'answers_hash',
        'questions',
        'answers',
        'priced_items',
        'hit_count',
        'last_used_at',
    ];

    protected $casts = [
        'questions'    => 'array',
        'answers'      => 'array',
        'priced_items' => 'array',
        'hit_count'    => 'integer',
        'last_used_at' => 'datetime',
    ];

    /**
     * Turn a set of answers into a stable hash.
     *
     * Sorted by question index and reduced to just the choice and any custom
     * text, so the same answers in a different in-memory order still match, and
     * unrelated fields never change the key. An empty set hashes consistently,
     * so "answered nothing" is itself a reusable outcome.
     *
     * @param  array<int, array{choice?:string, custom?:string}>  $answers
     */
    public static function hashAnswers(array $answers): string
    {
        ksort($answers);

        $normalised = [];
        foreach ($answers as $index => $answer) {
            $normalised[$index] = [
                'choice' => trim((string) ($answer['choice'] ?? '')),
                'custom' => trim((string) ($answer['custom'] ?? '')),
            ];
        }

        return hash('sha256', json_encode($normalised));
    }

    /**
     * Fetch the priced result for this file + answer set, if one exists.
     */
    public static function lookup(string $fileHash, string $answersHash): ?self
    {
        $result = static::where('file_hash', $fileHash)
            ->where('answers_hash', $answersHash)
            ->first();

        if ($result) {
            $result->forceFill([
                'hit_count'    => $result->hit_count + 1,
                'last_used_at' => now(),
            ])->saveQuietly();
        }

        return $result;
    }

    /**
     * Store the priced rows for this file + answer set.
     *
     * @param  array<int, array<string, mixed>>  $pricedItems
     * @param  array<int, mixed>                 $questions
     * @param  array<int, mixed>                 $answers
     */
    public static function remember(
        string $fileHash,
        string $answersHash,
        array $pricedItems,
        array $questions = [],
        array $answers = [],
    ): void {
        if ($pricedItems === []) {
            return;
        }

        static::updateOrCreate(
            ['file_hash' => $fileHash, 'answers_hash' => $answersHash],
            [
                'priced_items' => $pricedItems,
                'questions'    => $questions,
                'answers'      => $answers,
                'last_used_at' => now(),
            ],
        );
    }
}
