<?php

namespace Tests\Feature;

use App\Services\BinaryBlitzQuestionGenerator;
use Tests\TestCase;

class BinaryBlitzQuestionGeneratorTest extends TestCase
{
    public function test_it_generates_the_requested_number_of_questions(): void
    {
        $questions = (new BinaryBlitzQuestionGenerator)->generate(3);

        $this->assertCount(3, $questions);

        foreach ($questions as $question) {
            $this->assertArrayHasKey('prompt', $question);
            $this->assertArrayHasKey('answer', $question);
            $this->assertIsString($question['prompt']);
            $this->assertNotSame('', $question['prompt']);
            $this->assertIsInt($question['answer']);
        }
    }

    public function test_it_covers_binary_hex_and_multiply_questions(): void
    {
        $questions = (new BinaryBlitzQuestionGenerator)->generate(100);
        $prompts = collect($questions)->pluck('prompt');

        $this->assertTrue($prompts->contains(fn (string $prompt) => str_starts_with($prompt, '0b')));
        $this->assertTrue($prompts->contains(fn (string $prompt) => str_starts_with($prompt, '0x')));
        $this->assertTrue($prompts->contains(fn (string $prompt) => str_contains($prompt, '×')));
    }

    public function test_binary_and_hex_answers_match_their_prompts(): void
    {
        $questions = (new BinaryBlitzQuestionGenerator)->generate(100);

        foreach ($questions as $question) {
            if (str_starts_with($question['prompt'], '0b')) {
                $binary = substr($question['prompt'], 2, strpos($question['prompt'], ' ') - 2);
                $this->assertSame(bindec($binary), $question['answer']);
            }

            if (str_starts_with($question['prompt'], '0x')) {
                $hex = substr($question['prompt'], 2, strpos($question['prompt'], ' ') - 2);
                $this->assertSame(hexdec($hex), $question['answer']);
            }
        }
    }
}
