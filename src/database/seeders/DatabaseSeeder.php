<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class DatabaseSeeder extends Seeder
{
    protected static ?string $password;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Board::insert([
            ['name' => "Publik"],
            ['name' => "Pengumuman"],
            ['name' => "Direksi"],
            ['name' => "HRD"],
            ['name' => "GA"],
            ['name' => "Produksi"],
            ['name' => "Internal"],
            ['name' => "IT"],
            ['name' => "Security"],
            ['name' => "Gudang"],
            ['name' => "QC"],
            ['name' => "Koperasi"],
            ['name' => "Laundry"],
            ['name' => "Keuangan"],
            ['name' => "Teknisi"],
            ['name' => "Accounting"]
        ]);

        User::create([
            'username' => "admin",
            'password' => static::$password ??= Hash::make('passwordadmin'),
            'fullname' => "ADMIN",
            'is_admin' => true
        ]);

        $boards = Board::where('id', '>', 2)->get();
        foreach ($boards as $board) {
            User::factory()
                ->state(['head_board_id' => $board->id])
                ->state(['board_id' => $board->id])
                ->create();
        }
        User::factory(20)
            ->state(function () {
                return ['board_id' => Board::where('id', '>', 2)->inRandomOrder()->first()->id];
            })
            ->create();

        $tag = Tag::factory(3)->create();
        Post::factory(30)
            ->hasAttached($tag)
            ->state(function () {
                return ['board_id' => Board::inRandomOrder()->first()->id];
            })
            ->create();
        Comment::factory(30)->create();
        Reaction::factory(20)->create();
    }
}
