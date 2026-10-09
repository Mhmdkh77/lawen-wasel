<?php

use App\Livewire\UsersIndex;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('pagination keeps long page ranges compact', function () {
    foreach ([
        [1, 11, [1, 2, 3, 11], 1],
        [50, 100, [1, 49, 50, 51, 100], 2],
    ] as [$currentPage, $lastPage, $expectedPages, $expectedGaps]) {
        $paginator = new LengthAwarePaginator(
            range(1, 20),
            $lastPage * 20,
            20,
            $currentPage,
            ['path' => '/admin/rides'],
        );

        $html = $paginator->links('pagination.admin')->render();
        preg_match_all('/aria-label="(?:Go to page|Page) (\d+)"/', $html, $matches);

        expect(array_map('intval', $matches[1]))->toBe($expectedPages)
            ->and(substr_count($html, '&hellip;'))->toBe($expectedGaps);
    }
});

test('admin tables show numbered pagination with a result count', function () {
    $this->withoutVite();
    $this->actingAs(Admin::create([
        'name' => 'Test Admin',
        'email' => 'test-admin@example.test',
        'password' => 'password',
        'is_super_admin' => true,
    ]), 'admin');

    foreach (range(1, 25) as $number) {
        Admin::create([
            'name' => sprintf('Admin %02d', $number),
            'email' => "admin-{$number}@example.test",
            'password' => 'password',
        ]);
    }

    $this->get(route('admin.admins.index'))
        ->assertOk()
        ->assertSee('Showing')
        ->assertSee('Go to page 2')
        ->assertSee('Next');

    $this->get(route('admin.admins.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Admin 25')
        ->assertSee('Previous');
});

test('livewire tables can move between numbered pages and reset when searched', function () {
    foreach (range(1, 25) as $number) {
        User::create([
            'name' => sprintf('Passenger %02d', $number),
            'email' => "passenger-{$number}@example.test",
            'password' => 'password',
            'phone' => (string) $number,
            'role' => 'passenger',
        ]);
    }

    Livewire::test(UsersIndex::class, ['role' => 'passenger'])
        ->assertSee('Showing')
        ->assertSee('Go to page 2')
        ->call('nextPage')
        ->assertSee('Passenger 25')
        ->set('search', 'Passenger 01')
        ->assertSee('Passenger 01')
        ->assertDontSee('Passenger 25');
});
