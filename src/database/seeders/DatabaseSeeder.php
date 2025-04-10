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
            'username' => "administrator",
            'password' => Hash::make('Chenwoo010203'),
            'fullname' => "Admin Chenwoo",
            'is_admin' => true,
            'is_head' => true
        ]);

        // User::create([
        //     'username' => "kepala6",
        //     'password' => Hash::make('passwordadmin'),
        //     'fullname' => "Kepala Bagian Produksi",
        //     'is_admin' => false,
        //     'is_head' => true,
        //     'board_id' => 6
        // ]);

        // User::create([
        //     'username' => "user6",
        //     'password' => Hash::make('passwordadmin'),
        //     'fullname' => "User Produksi",
        //     'is_admin' => false,
        //     'is_head' => false,
        //     'board_id' => 6
        // ]);

        // User::create([
        //     'username' => "user8",
        //     'password' => Hash::make('passwordadmin'),
        //     'fullname' => "User IT",
        //     'is_admin' => false,
        //     'is_head' => false,
        //     'board_id' => 8
        // ]);

        // User::create([
        //     'username' => "kepala8",
        //     'password' => Hash::make('passwordadmin'),
        //     'fullname' => "Kepala IT",
        //     'is_admin' => false,
        //     'is_head' => true,
        //     'board_id' => 8
        // ]);

        // $boards = Board::where('id', '>', 2)->get();
        // foreach ($boards as $board) {
        //     User::factory()
        //         ->state(['is_head' => true])
        //         ->state(['board_id' => $board->id])
        //         ->create();
        // }
        // User::factory(20)
        //     ->state(function () {
        //         return ['board_id' => Board::where('id', '>', 2)->inRandomOrder()->first()->id];
        //     })
        //     ->create();

        // // $tag = Tag::factory(3)->create();
        // Post::factory(30)
        //     //->hasAttached($tag)
        //     ->state(function () {
        //         return ['board_id' => Board::inRandomOrder()->first()->id];
        //     })
        //     ->create();
        // Comment::factory(30)->create();
        // Reaction::factory(20)->create();
    }
}
