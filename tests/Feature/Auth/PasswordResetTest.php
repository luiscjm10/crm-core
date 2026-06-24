<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->markTestSkipped('Recuperación de contraseña deshabilitada intencionalmente.');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $this->markTestSkipped('Recuperación de contraseña deshabilitada intencionalmente.');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $this->markTestSkipped('Recuperación de contraseña deshabilitada intencionalmente.');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $this->markTestSkipped('Recuperación de contraseña deshabilitada intencionalmente.');
    }
}
