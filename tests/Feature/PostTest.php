<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow();  // Reset Carbon's test time
        $this->user = User::factory()->create();
    }

    /** @test */
    public function guests_can_view_posts_list()
    {
        Post::factory(25)->create();

        $response = $this->get(route('posts.index'));

        $response->assertStatus(200)
            ->assertViewIs('posts.index')
            ->assertViewHas('posts');
    }

    /** @test */
    public function guests_can_view_single_post()
    {
        $post = Post::factory()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertStatus(200)
            ->assertViewIs('posts.show')
            ->assertViewHas('post');
    }

    /** @test */
    public function guests_cannot_create_posts()
    {
        $response = $this->get(route('posts.create'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'Test Content'
        ]);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_users_can_create_posts()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('posts.create'));
        $response->assertStatus(200);

        $response = $this->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'Test Content'
        ]);

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function post_requires_title_and_content()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('posts.store'), []);

        $response->assertSessionHasErrors(['title', 'content']);
    }

    /** @test */
    public function post_generates_unique_slug()
    {
        $this->actingAs($this->user);

        // Create two posts with the same title
        $this->post(route('posts.store'), [
            'title' => 'Same Title',
            'content' => 'First Content'
        ]);

        $this->post(route('posts.store'), [
            'title' => 'Same Title',
            'content' => 'Second Content'
        ]);

        $this->assertDatabaseHas('posts', ['slug' => 'same-title']);
        $this->assertDatabaseHas('posts', ['slug' => 'same-title-1']);
    }

    /** @test */
    public function users_can_update_their_own_posts()
    {
        $this->actingAs($this->user);
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('posts.edit', $post));
        $response->assertStatus(200);

        $response = $this->put(route('posts.update', $post), [
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ]);

        $response->assertRedirect(route('posts.show', $post->fresh()))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ]);
    }

    /** @test */
    public function users_cannot_update_others_posts()
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('posts.edit', $post));
        $response->assertStatus(403);

        $response = $this->put(route('posts.update', $post), [
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function users_can_delete_their_own_posts()
    {
        $this->actingAs($this->user);
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    /** @test */
    public function users_cannot_delete_others_posts()
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->delete(route('posts.destroy', $post));
        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    /** @test */
    public function users_are_limited_to_three_posts_per_day()
    {
        $this->actingAs($this->user);

        // Create 3 posts
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('posts.store'), [
                'title' => "Post {$i}",
                'content' => 'Content'
            ]);
            $response->assertSessionHas('success');
        }

        // Try to create a 4th post
        $response = $this->post(route('posts.store'), [
            'title' => 'Fourth Post',
            'content' => 'Content'
        ]);

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('posts', 3);
    }

    /** @test */
    public function daily_post_limit_resets_each_day()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);

        // Create 3 posts yesterday
        Carbon::setTestNow(Carbon::yesterday());
        
        for ($i = 0; $i < 3; $i++) {
            Post::factory()->create([
                'user_id' => $this->user->id,
                'created_at' => Carbon::now()
            ]);
        }

        // Move to today
        Carbon::setTestNow(Carbon::now());

        // Should be able to create a new post today
        $response = $this->post(route('posts.store'), [
            'title' => 'New Day Post',
            'content' => 'Content'
        ]);

        $response->assertSessionHas('success', 'Post created successfully.');
        $this->assertDatabaseCount('posts', 4);
    }

    /** @test */
    public function posts_are_paginated_with_twenty_posts_per_page()
    {
        Post::factory(25)->create();

        $response = $this->get(route('posts.index'));

        $response->assertViewHas('posts', function ($posts) {
            return $posts->count() === 20 && $posts->total() === 25;
        });
    }
}
