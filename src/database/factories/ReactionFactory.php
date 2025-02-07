<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reaction>
 */
class ReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $emojis = [
            '👍',
            '❤️',
            '😂',
            '😍',
            '😭',
            '😡',
            '😱',
            '🤔',
            '🙏',
            '👏',
            '💪',
            '👌',
            '💯',
            '🤷‍♂️',
            '🙃',
            '🙄'
        ];

        return [
            'post_id' => Post::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'emoji' => $emojis[array_rand($emojis)]
        ];
    }
}
