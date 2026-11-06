<?php

use App\Domain\Access\Role;
use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Filament\Admin\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Filament\Admin\Resources\QuoteRequests\Pages\ViewQuoteRequest;
use App\Models\QuoteAttachment;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

function userWithRole(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->sales = userWithRole(Role::Sales);
    $this->actingAs($this->sales);
});

it('lists quotes newest first for sales', function () {
    $older = QuoteRequest::factory()->withItems()->create(['created_at' => now()->subDay()]);
    $newer = QuoteRequest::factory()->withItems()->create();

    Livewire::test(ListQuoteRequests::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true)
        ->assertTableColumnStateSet('items_count', 3, $newer)
        ->assertActionDoesNotExist('create');
});

it('keeps editors away from quote requests', function () {
    $quote = QuoteRequest::factory()->create();
    $this->actingAs(userWithRole(Role::Editor));

    $this->get('/admin/quote-requests')->assertForbidden();
    $this->get("/admin/quote-requests/{$quote->id}")->assertForbidden();
});

it('finds quotes by reference, phone or company', function () {
    $target = QuoteRequest::factory()->create(['phone' => '0791112223', 'company' => 'Al Noor Steel']);
    $other = QuoteRequest::factory()->create(['phone' => '0785554443', 'company' => 'Other']);

    Livewire::test(ListQuoteRequests::class)
        ->searchTable('0791112223')->assertCanSeeTableRecords([$target])->assertCanNotSeeTableRecords([$other])
        ->searchTable('Al Noor')->assertCanSeeTableRecords([$target])->assertCanNotSeeTableRecords([$other])
        ->searchTable($target->fresh()->reference)->assertCanSeeTableRecords([$target])->assertCanNotSeeTableRecords([$other]);
});

it('filters quotes assigned to me and failed notifications', function () {
    $mine = QuoteRequest::factory()->create(['assigned_to' => $this->sales->id]);
    $failed = QuoteRequest::factory()->create(['notification_status' => NotificationStatus::Failed]);

    Livewire::test(ListQuoteRequests::class)
        ->filterTable('mine')->assertCanSeeTableRecords([$mine])->assertCanNotSeeTableRecords([$failed])
        ->resetTableFilters()
        ->filterTable('notification_failed')->assertCanSeeTableRecords([$failed])->assertCanNotSeeTableRecords([$mine]);
});

it('shows the full request and moves new quotes into review when opened', function () {
    $quote = QuoteRequest::factory()->withItems(2)->create();

    Livewire::test(ViewQuoteRequest::class, ['record' => $quote->getRouteKey()])
        ->assertOk()
        ->assertSee($quote->fresh()->reference)
        ->assertSee($quote->name)
        ->assertSee('صاج مثقب 2 ملم');

    expect($quote->fresh()->status)->toBe(QuoteStatus::Reviewing);
});

it('changes status, assigns and keeps an audit trail', function () {
    $quote = QuoteRequest::factory()->create();

    Livewire::test(ViewQuoteRequest::class, ['record' => $quote->getRouteKey()])
        ->callAction('changeStatus', data: ['status' => QuoteStatus::Quoted->value])
        ->callAction('assignToMe')
        ->callAction('notes', data: ['internal_notes' => 'أُرسل العرض عبر البريد'])
        ->assertHasNoActionErrors();

    $quote->refresh();

    expect($quote->status)->toBe(QuoteStatus::Quoted)
        ->and($quote->assigned_to)->toBe($this->sales->id)
        ->and($quote->internal_notes)->toBe('أُرسل العرض عبر البريد')
        ->and(Activity::where('subject_type', QuoteRequest::class)->where('subject_id', $quote->id)->where('causer_id', $this->sales->id)->count())->toBeGreaterThanOrEqual(3);
});

it('builds call and whatsapp links from the customer phone', function () {
    $quote = QuoteRequest::factory()->create(['phone' => '0791112223']);

    Livewire::test(ViewQuoteRequest::class, ['record' => $quote->getRouteKey()])
        ->assertActionHasUrl('call', 'tel:+962791112223')
        ->assertActionHasUrl('whatsapp', 'https://wa.me/962791112223?text='.rawurlencode('HEC '.$quote->fresh()->reference));
});

it('does not let sales delete quotes but lets admins archive and restore them', function () {
    $quote = QuoteRequest::factory()->create();

    Livewire::test(ViewQuoteRequest::class, ['record' => $quote->getRouteKey()])->assertActionHidden('delete');

    $admin = userWithRole(Role::Admin);
    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    $this->actingAs($admin);
    Livewire::test(ViewQuoteRequest::class, ['record' => $quote->getRouteKey()])->callAction('delete');
    expect($quote->fresh()->trashed())->toBeTrue();

    Livewire::test(ViewQuoteRequest::class, ['record' => $quote->getRouteKey()])->callAction('restore');
    expect($quote->fresh()->trashed())->toBeFalse();
});

it('serves attachments only to staff allowed to see the quote', function () {
    Storage::fake('local');
    Storage::disk('local')->put('quotes/drawing.pdf', '%PDF-1.4 test');
    $quote = QuoteRequest::factory()->create();
    $attachment = QuoteAttachment::create([
        'quote_request_id' => $quote->id,
        'disk' => 'local',
        'path' => 'quotes/drawing.pdf',
        'original_name' => 'drawing.pdf',
        'mime_type' => 'application/pdf',
        'size_bytes' => 13,
        'sha256' => hash('sha256', '%PDF-1.4 test'),
    ]);
    $url = route('admin.quote-attachments.download', $attachment);

    $this->get($url)
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertDownload('drawing.pdf');

    $this->actingAs(userWithRole(Role::Editor))->get($url)->assertForbidden();

    auth()->logout();
    $this->get($url)->assertForbidden();
});

it('exports quotes to private storage for sales', function () {
    Storage::fake('local');
    QuoteRequest::factory()->count(3)->withItems()->create();

    Livewire::test(ListQuoteRequests::class)
        ->callAction(TestAction::make('export')->table())
        ->assertHasNoActionErrors();

    $export = Export::latest('id')->first();

    expect($export)->not->toBeNull()
        ->and($export->user_id)->toBe($this->sales->id)
        ->and($export->file_disk)->toBe('local')
        ->and($export->successful_rows)->toBe(3);
});
