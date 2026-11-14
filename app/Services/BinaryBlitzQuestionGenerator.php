<?php

namespace App\Services;

class BinaryBlitzQuestionGenerator
{
    /**
     * @return array<int, array{prompt: string, answer: int}>
     */
    public function generate(int $count): array
    {
        $types = ['binary', 'hex', 'multiply'];
        $questions = [];

        for ($i = 0; $i < $count; $i++) {
            $type = $types[random_int(0, count($types) - 1)];

            $questions[] = match ($type) {
                'binary' => $this->binaryQuestion(),
                'hex' => $this->hexQuestion(),
                'multiply' => $this->multiplyQuestion(),
            };
        }

        return $questions;
    }

    /**
     * @return array{prompt: string, answer: int}
     */
    private function binaryQuestion(): array
    {
        $number = random_int(1, 255);

        return [
            'prompt' => '0b'.decbin($number).' em decimal?',
            'answer' => $number,
        ];
    }

    /**
     * @return array{prompt: string, answer: int}
     */
    private function hexQuestion(): array
    {
        $number = random_int(1, 255);

        return [
            'prompt' => '0x'.strtoupper(dechex($number)).' em decimal?',
            'answer' => $number,
        ];
    }

    /**
     * @return array{prompt: string, answer: int}
     */
    private function multiplyQuestion(): array
    {
        $left = random_int(2, 12);
        $right = random_int(2, 12);

        return [
            'prompt' => "{$left} × {$right} = ?",
            'answer' => $left * $right,
        ];
    }
}
