<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $colors = ['red', 'green', 'blue', 'yellow'];
        $cards = [];

        foreach ($colors as $color) {
            // One 0 card per color
            $cards[] = ['type' => 'number', 'color' => $color, 'value' => '0'];

            // Two of each 1-9 per color
            for ($i = 1; $i <= 9; $i++) {
                $cards[] = ['type' => 'number', 'color' => $color, 'value' => (string)$i];
                $cards[] = ['type' => 'number', 'color' => $color, 'value' => (string)$i];
            }

            // Two skip cards per color
            $cards[] = ['type' => 'skip', 'color' => $color, 'value' => 'skip'];
            $cards[] = ['type' => 'skip', 'color' => $color, 'value' => 'skip'];

            // Two reverse cards per color
            $cards[] = ['type' => 'reverse', 'color' => $color, 'value' => 'reverse'];
            $cards[] = ['type' => 'reverse', 'color' => $color, 'value' => 'reverse'];

            // Two draw2 cards per color
            $cards[] = ['type' => 'draw2', 'color' => $color, 'value' => '+2'];
            $cards[] = ['type' => 'draw2', 'color' => $color, 'value' => '+2'];
        }

        // Four wild cards
        for ($i = 0; $i < 4; $i++) {
            $cards[] = ['type' => 'wild', 'color' => 'black', 'value' => 'wild'];
        }

        // Four wild draw4 cards
        for ($i = 0; $i < 4; $i++) {
            $cards[] = ['type' => 'draw4', 'color' => 'black', 'value' => '+4'];
        }

        foreach ($cards as $card) {
            Card::create($card);
        }

        // ===== STANDARD DECK (52 cards) for Remi 41 =====
        $suits = ['spade', 'heart', 'club', 'diamond'];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                Card::create([
                    'type' => 'standard',
                    'color' => $suit,
                    'value' => $value,
                ]);
            }
        }
    }
}
