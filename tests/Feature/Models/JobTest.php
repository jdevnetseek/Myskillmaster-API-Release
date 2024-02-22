<?php

namespace Tests\Feature\Models;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use App\Models\Comment;
use App\Models\Favorite;
use Tests\Factories\JobFactory;
use Tests\Factories\CommentFactory;
use Tests\Factories\FavoriteFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function hasCorrectCountValue()
    {
        $job = Job::factory()
            ->has(Favorite::factory()->times(10))
            ->has(Comment::factory()->times(5)->has(Comment::factory()->times(5), 'responses'))
            ->create();

        $this->assertCount(30, $job->allComments()->get());
        $this->assertCount(10, $job->favorites()->get());
    }

    /**
     * @test
     */
    public function filterByAuthor()
    {
        $user = User::factory()->create();

        Job::factory()->times(15)
            ->create();

        Job::factory()->times(15)
            ->create(['author_id' => $user->id ]);

        $this->assertCount(15, Job::whereAuthorId($user->id)->get());
    }
}
