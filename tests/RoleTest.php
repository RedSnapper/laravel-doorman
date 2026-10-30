<?php

namespace Redsnapper\LaravelDoorman\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Redsnapper\LaravelDoorman\Exceptions\PermissionDoesNotExist;
use Redsnapper\LaravelDoorman\Models\Permission;
use Redsnapper\LaravelDoorman\Models\Role;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Factories\PermissionFactory;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Factories\RoleFactory;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Factories\UserFactory;
use Redsnapper\LaravelDoorman\Tests\Fixtures\Models\User;

class RoleTest extends TestCase
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
    public function a_role_can_have_users()
    {
        $this->testUser->assignRole($this->testRole);
        $this->assertTrue($this->testRole->users->first()->is($this->testUser));
    }

    #[Test]
    public function can_be_given_a_permission()
    {
        $permissionA = PermissionFactory::new()->create(["name" => "can-see-the-ground"]);
        $this->testRole->givePermissionTo($permissionA);
        $this->assertTrue($this->testRole->hasPermission($permissionA->name));
    }

    #[Test]
    public function it_throws_an_exception_when_given_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testRole->givePermissionTo('act-superbly');
    }

    #[Test]
    public function can_be_given_multiple_permissions_using_an_array()
    {
        $permission = PermissionFactory::new()->create();

        $this->testRole->givePermissionTo(['do-something',$permission]);
        $this->assertTrue($this->testRole->hasPermissionTo('do-something'));
        $this->assertTrue($this->testRole->hasPermissionTo($permission));
    }

    #[Test]
    public function it_can_be_given_multiple_permissions_using_multiple_arguments()
    {
        $permission = PermissionFactory::new()->create();

        $this->testRole->givePermissionTo('do-something', $permission);
        $this->assertTrue($this->testRole->hasPermissionTo('do-something'));
        $this->assertTrue($this->testRole->hasPermissionTo($permission));
    }

    #[Test]
    public function it_can_sync_permissions()
    {
        $permission = PermissionFactory::new()->create();
        $this->testRole->givePermissionTo('do-something');
        $this->testRole->syncPermissions($permission);
        $this->assertFalse($this->testRole->hasPermissionTo('do-something'));
        $this->assertTrue($this->testRole->hasPermissionTo($permission));
    }

    #[Test]
    public function it_throws_an_exception_when_syncing_permissions_that_do_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testRole->syncPermissions('permission-does-not-exist');
    }

    #[Test]
    public function it_can_remove_a_permission()
    {
        $this->testRole->givePermissionTo('do-something');
        $this->assertTrue($this->testRole->hasPermissionTo('do-something'));
        $this->testRole->removePermissionTo('do-something');
        $this->assertFalse($this->testRole->hasPermissionTo('do-something'));
    }

    #[Test]
    public function it_can_give_and_remove_multiple_permissions()
    {
        $permission = PermissionFactory::new()->create();
        $this->testRole->givePermissionTo(['do-something', $permission]);
        $this->assertEquals(2, $this->testRole->permissions()->count());
        $this->testRole->removePermissionTo(['do-something', $permission]);
        $this->assertEquals(0, $this->testRole->permissions()->count());
    }

    #[Test]
    public function it_returns_false_if_it_does_not_have_the_permission()
    {
        $this->assertFalse($this->testRole->hasPermissionTo('do-something'));
    }

    #[Test]
    public function it_throws_an_exception_if_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testRole->hasPermissionTo('doesnt-exist');
    }

    #[Test]
    public function it_does_not_remove_already_associated_permissions_when_assigning_new_permissions()
    {
        $permission = PermissionFactory::new()->create();
        $this->testRole->givePermissionTo('do-something');
        $this->testRole->givePermissionTo($permission);
        $this->assertTrue($this->testRole->hasPermissionTo('do-something'));
    }

    #[Test]
    public function it_does_not_throw_an_exception_when_assigning_a_permission_that_is_already_assigned()
    {
        $this->testRole->givePermissionTo('do-something');
        $this->testRole->givePermissionTo('do-something');
        $this->assertTrue($this->testRole->hasPermissionTo('do-something'));
    }



}
