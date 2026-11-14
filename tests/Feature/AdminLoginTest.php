<?php
namespace Tests\Feature;
use Tests\TestCase;
class AdminLoginTest extends TestCase {
    public function test_admin_login_reads_the_cached_configuration_value(): void {
        config(['auth.admin_passphrase'=>'test-only-config-password']);
        $this->post('/admin/login',['passphrase'=>'test-only-config-password'])
            ->assertRedirect('/admin')->assertSessionHas('admin_authenticated',true);
    }
    public function test_an_unconfigured_password_does_not_allow_login_or_flash_the_password(): void {
        config(['auth.admin_passphrase'=>'']);
        $this->post('/admin/login',['passphrase'=>'test-only-rejected-password'])
            ->assertSessionHasErrors('passphrase')->assertSessionMissing('admin_authenticated')->assertSessionMissing('_old_input.passphrase');
    }
}
