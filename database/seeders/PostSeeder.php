<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

final class PostSeeder extends Seeder
{
    private const PLACEHOLDER_IMAGE_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAA8ElEQVR4nO3QsQmDMBAF0exE4nWjBE7RRL3sLFOTxB9VRLpMWaBt55z/nM8AhIsfADAAwDMAwzAMAzDMAzDMAzDMAzDwN4E7n/BTu+BSH8CIfwIh/AgfEJ4BwiH8CCP8CBMQvgXCIjwAwDMAwzAMAzDMAzDMAzDwDwQngHCIfwII/wIEwRvgcAiP8CBD+D8ARwiL8CwCMMwzAMAzDMAzDMAzD8NMBPEI4BMKHP7x36V95gDBuFZ7nbwQEAAQIECBAgACB/wE3AbQqPp+xbSMAAAAASUVORK5CYII=';

    public function run(): void
    {
        // Get or create a user for posts
        $user = User::first() ?? User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        Post::factory()
            ->for($user)
            ->count(20)
            ->create()
            ->each(fn (Post $post, int $index) => $this->attachPlaceholderMedia($post, $index + 1));

        Post::factory()
            ->for($user)
            ->featured()
            ->published()
            ->count(5)
            ->create()
            ->each(fn (Post $post, int $index) => $this->attachPlaceholderMedia($post, $index + 1));

        Post::factory()
            ->for($user)
            ->pinned()
            ->published()
            ->count(3)
            ->create()
            ->each(fn (Post $post, int $index) => $this->attachPlaceholderMedia($post, $index + 1));

        Post::factory()
            ->for($user)
            ->draft()
            ->count(3)
            ->create()
            ->each(fn (Post $post, int $index) => $this->attachPlaceholderMedia($post, $index + 1));

        Post::factory()
            ->for($user)
            ->archived()
            ->count(2)
            ->create()
            ->each(fn (Post $post, int $index) => $this->attachPlaceholderMedia($post, $index + 1));
    }

    private function attachPlaceholderMedia(Post $post, int $sequence): void
    {
        if (! $post->hasMedia('images')) {
            $this->addPlaceholderMedia($post, "post-{$post->id}-featured-{$sequence}.png", 'images');
        }

        if ($post->getMedia('gallery')->isEmpty()) {
            foreach (range(1, 3) as $index) {
                $this->addPlaceholderMedia(
                    $post,
                    "post-{$post->id}-gallery-{$sequence}-{$index}.png",
                    'gallery'
                );
            }
        }
    }

    private function addPlaceholderMedia(Post $post, string $fileName, string $collection): void
    {
        $temporaryFile = tmpfile();

        if ($temporaryFile === false) {
            return;
        }

        $bytesWritten = fwrite($temporaryFile, base64_decode(self::PLACEHOLDER_IMAGE_BASE64, true) ?: '');

        if ($bytesWritten === false || $bytesWritten === 0) {
            fclose($temporaryFile);

            return;
        }

        $meta = stream_get_meta_data($temporaryFile);

        if (! empty($meta['uri'])) {
            $post
                ->addMedia($meta['uri'])
                ->usingFileName($fileName)
                ->withCustomProperties(['placeholder' => true])
                ->toMediaCollection($collection);
        }

        fclose($temporaryFile);
    }
}
