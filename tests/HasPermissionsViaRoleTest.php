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

class HasPermissionsViaRoleTest extends TestCase
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
    protected $testRole2;

    /**
     * @var Permission
     */
    protected $testPermission;

    public function setUp(): void
    {
        parent::setUp();

        $this->testUser = UserFactory::new()->create();
        $this->testRole = RoleFactory::new()->create(['name'=>'Test']);
        $this->testRole2 = RoleFactory::new()->create(['name'=>'Test 2']);
        $this->testPermission = PermissionFactory::new()->create(['name'=>'do-something']);
    }

    #[Test]
    public function can_check_if_user_has_a_role()
    {
        $this->assertFalse($this->testUser->hasRole($this->testRole));
        $role = RoleFactory::new()->create();
        $this->assertFalse($this->testUser->hasRole($role));
        $this->testUser->assignRole($role);

        $this->assertTrue($this->testUser->hasRole($role));
        $this->assertTrue($this->testUser->hasRole($role->name));
        $this->assertTrue($this->testUser->hasRole([$role->name, 'fakeRole']));
        $this->assertTrue($this->testUser->hasRole($role->id));
        $this->assertTrue($this->testUser->hasRole([$role->id, 'fakeRole']));

    }

    #[Test]
    public function can_remove_a_role()
    {
        $this->testUser->assignRole($this->testRole,$this->testRole2);
        $this->testUser->removeRole($this->testRole2);

        $this->assertTrue($this->testUser->hasRole($this->testRole));
        $this->assertFalse($this->testUser->hasRole($this->testRole2));

    }

    #[Test]
    public function it_can_assign_a_role_using_a_model()
    {
        $this->testUser->hasRole($this->testRole);
        $this->testUser->assignRole($this->testRole);
        $this->assertTrue($this->testUser->hasRole($this->testRole));
    }

    #[Test]
    public function can_assign_a_role_using_an_id()
    {
        $this->testUser->assignRole($this->testRole->id);
        $this->assertTrue($this->testUser->hasRole($this->testRole));
    }

    #[Test]
    public function can_assign_a_role_by_name()
    {
        $this->testUser->assignRole('Test');
        $this->assertTrue($this->testUser->hasRole($this->testRole));
    }

    #[Test]
    public function can_assign_multiple_roles()
    {
        $this->testUser->assignRole($this->testRole->id, 'Test 2');
        $this->assertTrue($this->testUser->hasRole($this->testRole));
        $this->assertTrue($this->testUser->hasRole($this->testRole2));
    }

    #[Test]
    public function can_assign_multiple_roles_using_an_array()
    {
        $this->testUser->assignRole([$this->testRole->id, 'Test 2']);
        $this->assertTrue($this->testUser->hasRole($this->testRole));
        $this->assertTrue($this->testUser->hasRole($this->testRole2));
    }

    #[Test]
    public function it_does_not_remove_already_associated_roles_when_assigning_new_roles()
    {
        $this->testUser->assignRole($this->testRole);
        $this->testUser->assignRole(RoleFactory::new()->create());
        $this->assertTrue($this->testUser->fresh()->hasRole($this->testRole));
    }

    #[Test]
    public function can_sync_roles_using_a_model()
    {
        $this->testUser->assignRole($this->testRole);
        $this->testUser->syncRoles($this->testRole2);
        $this->assertFalse($this->testUser->hasRole($this->testRole));
        $this->assertTrue($this->testUser->hasRole($this->testRole2));
    }

    #[Test]
    public function can_sync_roles_using_a_string()
    {
        $this->testUser->assignRole($this->testRole);
        $this->testUser->syncRoles('Test 2');
        $this->assertFalse($this->testUser->hasRole($this->testRole));
        $this->assertTrue($this->testUser->hasRole($this->testRole2));
    }

    #[Test]
    public function it_can_sync_multiple_roles()
    {
        $this->testUser->syncRoles($this->testRole, $this->testRole2);
        $this->assertTrue($this->testUser->hasRole($this->testRole));
        $this->assertTrue($this->testUser->hasRole($this->testRole2));
    }
    #[Test]
    public function it_can_sync_multiple_roles_from_an_array()
    {
        $this->testUser->syncRoles([$this->testRole, $this->testRole2]);
        $this->assertTrue($this->testUser->hasRole($this->testRole));
        $this->assertTrue($this->testUser->hasRole($this->testRole2));
    }

    #[Test]
    public function role_can_grant_permission_to_a_user()
    {
        $this->testRole->givePermissionTo($this->testPermission);
        $this->testUser->assignRole($this->testRole);
        $this->assertTrue($this->testUser->hasPermission($this->testPermission));
    }

    #[Test]
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_an_invalid_type()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testUser->hasPermissionTo(new \stdClass());
    }

    #[Test]
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_null()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testUser->hasPermissionTo(null);
    }

    #[Test]
    public function it_throws_an_exception_when_a_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testUser->hasPermissionTo("does-not-exist");
    }

    #[Test]
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('do-something'));
    }

    #[Test]
    public function it_can_list_all_the_permissions_via_roles_of_user()
    {
        // This permission should not be included for the testUser
        $user = UserFactory::new()->create();
        $userPermission = PermissionFactory::new()->create();
        $userRole = RoleFactory::new()->create();
        $userRole->givePermissionTo($userPermission);
        $user->assignRole($userRole);

        $permission = PermissionFactory::new()->create();
        $this->testRole->givePermissionTo($this->testPermission,$permission);
        $this->testRole2->givePermissionTo($this->testPermission);
        $this->testUser->assignRole($this->testRole,$this->testRole2);

        $this->assertCount(2,$this->testUser->permissions);
        $this->assertTrue($this->testUser->permissions->contains($this->testPermission));
        $this->assertTrue($this->testUser->permissions->contains($permission));
        $this->assertFalse($this->testUser->permissions->contains($userPermission));

    }

    #[Test]
    public function can_eager_load_user_permissions()
    {
        $userA = $this->testUser;
        $userB = UserFactory::new()->create();

        $permissionB = PermissionFactory::new()->create();

        $this->testRole->givePermissionTo($this->testPermission);
        $this->testRole2->givePermissionTo($permissionB);
        $this->testRole2->givePermissionTo($this->testPermission);

        $userA->assignRole($this->testRole);
        $userB->assignRole($this->testRole2);
        $userB->assignRole($this->testRole);

        $users = User::with('permissions')->get();

        tap($users->firstWhere('id',$userA->id)->permissions,function($permissions){
            $this->assertCount(1,$permissions);
            $this->assertTrue($permissions->first()->is($this->testPermission));
        });

        tap($users->firstWhere('id',$userB->id)->permissions,function($permissions) use($permissionB){
            $this->assertCount(2,$permissions);
            $this->assertTrue($permissions->contains($this->testPermission));
            $this->assertTrue($permissions->contains($permissionB));
        });

    }



}
