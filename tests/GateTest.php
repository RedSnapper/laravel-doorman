<?php

namespace Redsnapper\LaravelDoorman\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Contracts\Auth\Access\Gate;
use Redsnapper\LaravelDoorman\Models\Permission;
use Redsnapper\LaravelDoorman\Models\Role;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Factories\PermissionFactory;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Factories\RoleFactory;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Factories\UserFactory;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Models\User;

class GateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    protected $testUser;

    /**
     * @var Role
     */
    protected $testRole;

    /**
     * @var Role
     */
    protected $testPermission;

    public function setUp(): void
    {
        parent::setUp();
        $this->testUser = UserFactory::new()->create();
        $this->testRole = RoleFactory::new()->create(['name'=>'Test']);
        $this->testPermission = PermissionFactory::new()->create(['name'=>'do-something']);
    }

    #[Test]
    public function it_can_determine_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('do-something'));
    }

    #[Test]
    public function it_allows_other_gate_before_callbacks_to_run_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('do-something'));
        app(Gate::class)->before(function () {
            return true;
        });
        $this->assertTrue($this->testUser->can('do-something'));
    }

    #[Test]
    public function it_can_determine_if_a_user_has_a_permission_through_roles()
    {
        PermissionFactory::new()->create(['name'=>'existing-permission']);

        $this->testRole->givePermissionTo($this->testPermission);
        $this->testUser->assignRole($this->testRole);
        $this->assertTrue($this->testUser->can('do-something'));
        $this->assertFalse($this->testUser->can('existing-permission'));
    }



}
