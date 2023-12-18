<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Wishlist;
use App\Services\WishlistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with a wishlist and 5 items
        $this->user = User::factory()
            ->hasAttached(
                Wishlist::factory()
                    ->hasItems(5),
                ['role' => 'owner'],
            )
            ->create();
    }

    /**
     * Test the index page
     */
    public function test_index_page_for_normal_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('wishlists.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Wishlist/Index')
            ->has('lists')
        );
    }

    /**
     * Test the index page for a guest
     */
    public function test_index_page_for_guest()
    {
        $response = $this->get(route('wishlists.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Test the destroy route
     */
    public function test_destroy_route_for_normal_user()
    {
        $wishlist = $this->user->wishlists()->first();

        $response = $this->actingAs($this->user)
            ->delete(route('wishlists.destroy', $wishlist));

        $response->assertStatus(302);

        // Assert that the wishlist was deleted
        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlist->id,
        ]);
    }

    /**
     * Test the destroy route for a guest
     */
    public function test_destroy_route_for_guest()
    {
        $wishlist = $this->user->wishlists()->first();

        $response = $this->delete(route('wishlists.destroy', $wishlist));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        // Assert that the wishlist was not deleted
        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
        ]);
    }

    /**
     * Test the update route
     */
    public function test_update_route_for_normal_user()
    {
        $wishlist = $this->user->wishlists()->first();

        $response = $this->actingAs($this->user)
            ->put(route('wishlists.update', $wishlist), [
                'title' => 'Updated Wishlist',
                'public' => false,
            ]);

        $response->assertStatus(302);

        // Assert that the wishlist was updated
        $this->assertDatabaseHas('wishlists', [
            'title' => 'Updated Wishlist',
            'public' => false,
            'id' => $wishlist->id,
        ]);
    }

    /**
     * Test the update route for a guest
     */
    public function test_update_route_for_guest()
    {
        $wishlist = $this->user->wishlists()->first();

        $response = $this->put(route('wishlists.update', $wishlist), [
            'title' => 'Updated Wishlist',
            'public' => false,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        // Assert that the wishlist was not updated
        $this->assertDatabaseMissing('wishlists', [
            'title' => 'Updated Wishlist',
            'public' => false,
            'id' => $wishlist->id,
        ]);
    }

    /**
     * Test the update route validation
     */
    public function test_update_route_validation()
    {
        $wishlist = $this->user->wishlists()->first();

        $this->actingAs($this->user);

        // Test without a title
        $response = $this->put(route('wishlists.update', $wishlist), [
            'public' => false,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');

        // Test without a public value
        $response = $this->put(route('wishlists.update', $wishlist), [
            'title' => 'Updated Wishlist',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('public');

        // Test without any data
        $response = $this->put(route('wishlists.update', $wishlist));

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title', 'public']);
    }

    /**
     * Test the store route for a normal user
     */
    public function test_store_route_for_normal_user()
    {
        $response = $this->actingAs($this->user)
            ->post(route('wishlists.store'), [
                'title' => 'Test Wishlist',
                'public' => true,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Wishlist created');

        // Assert that the wishlist was created
        $this->assertDatabaseHas('wishlists', [
            'title' => 'Test Wishlist',
            'public' => true,
        ]);
    }


    /**
     * Test the store route for a guest
     */
    public function test_store_route_for_guest()
    {
        $response = $this->post(route('wishlists.store'), [
            'title' => 'Test Wishlist',
            'public' => true,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        // Assert that the wishlist was not created
        $this->assertDatabaseMissing('wishlists', [
            'title' => 'Test Wishlist',
            'public' => true,
        ]);
    }

    /**
     * Test store route without a title
     */
    public function test_store_route_validation()
    {

        $this->actingAs($this->user);

        // Test without a title
        $response = $this->post(route('wishlists.store'), [
            'public' => true,
        ]);


        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');

        // Test without a public value
        $response = $this->post(route('wishlists.store'), [
            'title' => 'Test Wishlist',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('public');

        // Test without any data
        $response = $this->post(route('wishlists.store'));

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title', 'public']);
    }





}
