<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);

    $response = $this
        ->actingAs($user)
        ->get(route('student.profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);

    $response = $this
        ->actingAs($user)
        ->patch(route('student.profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('student.profile.edit'));

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);

    $response = $this
        ->actingAs($user)
        ->patch(route('student.profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('student.profile.edit'));

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);

    $response = $this
        ->actingAs($user)
        ->delete(route('student.profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);

    $response = $this
        ->actingAs($user)
        ->from(route('student.profile.edit'))
        ->delete(route('student.profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect(route('student.profile.edit'));

    $this->assertNotNull($user->fresh());
});
