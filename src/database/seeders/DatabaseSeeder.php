<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

        User::create([
            'username' => "admin",
            'password' => static::$password ??= Hash::make('passwordadmin'),
            'fullname' => "ADMIN",
            'remember_token' => Str::random(10),
            'is_admin' => true
        ]);
        User::factory(10)->create();
        Tag::create([
            'name' => "Publik"
        ]);
        Tag::create([
            'name' => "Pengumuman"
        ]);
        $tag = Tag::factory(3)->create();
        Post::factory(30)->hasAttached($tag)->create();
        Comment::factory(30)->create();
        Reaction::factory(20)->create();
    }
}
