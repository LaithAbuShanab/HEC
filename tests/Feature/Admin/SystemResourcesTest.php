<?php

use App\Domain\Access\Role;
use App\Filament\Admin\Resources\Activities\Pages\ListActivities;
use App\Filament\Admin\Resources\Redirects\Pages\ManageRedirects;
use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\Product;
use App\Models\Redirect;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Auth\Events\Login;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

function member(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    if ($role === Role::Admin) {
        $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    }

    return $user;
}

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

describe('redirects', function () {
    beforeEach(fn () => $this->actingAs(member(Role::Editor)));

    it('lets editors create a working redirect', function () {
        Livewire::test(ManageRedirects::class)
            ->callAction(TestAction::make('create'), data: ['from_path' => '/Old-Page/', 'to_path' => '/ar', 'status_code' => 301, 'is_active' => true])
            ->assertHasNoActionErrors();

        expect(Redirect::first()->from_path)->toBe('/old-page');
        $this->get('/old-page')->assertRedirect('/ar');
    });

    it('rejects loops, chains and admin paths', function () {
        Redirect::create(['from_path' => '/middle', 'to_path' => '/ar']);

        $attempts = [
            [['from_path' => '/start', 'to_path' => '/middle'], 'to_path'],
            [['from_path' => '/loop', 'to_path' => '/loop/'], 'to_path'],
            [['from_path' => '/admin/users', 'to_path' => '/ar'], 'from_path'],
            [['from_path' => '/middle/', 'to_path' => '/en'], 'from_path'],
        ];

        foreach ($attempts as [$data, $field]) {
            Livewire::test(ManageRedirects::class)
                ->callAction(TestAction::make('create'), data: $data + ['status_code' => 301])
                ->assertHasActionErrors([$field]);
        }

        expect(Redirect::count())->toBe(1);
    });

    it('stores gone pages without a target', function () {
        Livewire::test(ManageRedirects::class)
            ->callAction(TestAction::make('create'), data: ['from_path' => '/removed', 'status_code' => 410])
            ->assertHasNoActionErrors();

        expect(Redirect::first()->to_path)->toBeNull();
        $this->get('/removed')->assertStatus(410);
    });

    it('keeps sales out of redirects', function () {
        $this->actingAs(member(Role::Sales))->get('/admin/redirects')->assertForbidden();
    });
});

describe('users', function () {
    beforeEach(function () {
        $this->admin = member(Role::Admin);
        $this->actingAs($this->admin);
    });

    it('is reserved for administrators', function (Role $role) {
        $this->actingAs(member($role))->get('/admin/users')->assertForbidden();
    })->with([Role::Editor, Role::Sales]);

    it('creates a staff member with a role and a strong password', function () {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Sales', 'email' => 'sales@hec.test', 'password' => 'short', 'role' => Role::Sales->value])
            ->call('create')
            ->assertHasFormErrors(['password']);

        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Sales', 'email' => 'sales@hec.test', 'password' => 'Strong-Password-2026', 'role' => Role::Sales->value])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'sales@hec.test')->first();

        expect($user->hasRole(Role::Sales->value))->toBeTrue()
            ->and(Hash::check('Strong-Password-2026', $user->password))->toBeTrue();
    });

    it('changes role and keeps the password when left empty', function () {
        $editor = member(Role::Editor);
        $hash = $editor->password;

        Livewire::test(EditUser::class, ['record' => $editor->getRouteKey()])
            ->assertSchemaStateSet(['role' => Role::Editor->value])
            ->fillForm(['role' => Role::Sales->value, 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $editor->refresh();

        expect($editor->hasRole(Role::Sales->value))->toBeTrue()
            ->and($editor->hasRole(Role::Editor->value))->toBeFalse()
            ->and($editor->password)->toBe($hash);
    });

    it('stops admins from locking themselves out', function () {
        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->fillForm(['is_active' => false, 'role' => Role::Editor->value])
            ->call('save')
            ->assertHasFormErrors(['is_active', 'role']);

        expect($this->admin->fresh()->is_active)->toBeTrue()
            ->and($this->admin->fresh()->isAdmin())->toBeTrue();
    });

    it('never deletes users and can reset a lost authenticator', function () {
        $sales = member(Role::Sales);
        $sales->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

        Livewire::test(ListUsers::class)
            ->assertActionDoesNotExist(TestAction::make('delete')->table($sales))
            ->callAction(TestAction::make('resetMfa')->table($sales));

        expect($sales->fresh()->getAppAuthenticationSecret())->toBeNull();
    });

    it('records the last login time', function () {
        $sales = member(Role::Sales);

        event(new Login('web', $sales, false));

        expect($sales->fresh()->last_login_at)->not->toBeNull();
    });
});

describe('activity log', function () {
    it('shows who changed what to administrators only', function () {
        $editor = member(Role::Editor);
        $this->actingAs($editor);
        $product = Product::where('slug', 'chains')->first();
        $product->update(['sort' => 99]);

        $activity = Activity::where('subject_id', $product->id)->where('subject_type', Product::class)->latest('id')->first();

        expect($activity->causer_id)->toBe($editor->id);

        $this->get('/admin/activities')->assertForbidden();

        $this->actingAs(member(Role::Admin));

        Livewire::test(ListActivities::class)
            ->filterTable('causer_id', $editor->id)
            ->assertCanSeeTableRecords([$activity])
            ->assertTableColumnStateSet('changed_fields', 'sort', $activity)
            ->assertActionDoesNotExist(TestAction::make('edit')->table($activity));
    });
});
