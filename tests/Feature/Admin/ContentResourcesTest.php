<?php

use App\Domain\Access\Role;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Filament\Admin\Resources\Faqs\Pages\ManageFaqs;
use App\Filament\Admin\Resources\Guides\Pages\CreateGuide;
use App\Filament\Admin\Resources\Guides\Pages\EditGuide;
use App\Filament\Admin\Resources\Guides\Pages\ListGuides;
use App\Filament\Admin\Resources\Industries\Pages\ListIndustries;
use App\Filament\Admin\Resources\Pages\Pages\EditPage;
use App\Filament\Admin\Resources\Pages\Pages\ListPages;
use App\Filament\Admin\Resources\Services\Pages\EditService;
use App\Filament\Admin\Resources\Services\Pages\ListServices;
use App\Filament\Admin\Resources\WorkItems\Pages\CreateWorkItem;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkItem;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $editor = User::factory()->create();
    $editor->assignRole(Role::Editor->value);
    $this->actingAs($editor);
});

it('seeds the services, guides, industries and pages named in the requirements as drafts', function () {
    Livewire::test(ListServices::class)->assertCountTableRecords(5);
    Livewire::test(ListGuides::class)->assertCountTableRecords(5);
    Livewire::test(ListIndustries::class)->assertCountTableRecords(4);
    Livewire::test(ListPages::class)->assertCountTableRecords(3);

    expect(Service::published()->count() + Guide::published()->count() + Page::published()->count())->toBe(0);
});

it('edits a service with materials, limits and linked products', function () {
    $service = Service::where('slug', 'metal-cutting')->first();
    $product = Product::where('slug', 'black-steel-sheets-plates')->first();

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->assertSchemaStateSet(['title.ar' => 'قص المعادن والصاج'])
        ->fillForm([
            'summary.ar' => 'قص الصاج حسب المقاسات المطلوبة.',
            'summary.en' => 'Cutting sheet to the required sizes.',
            'materials.ar' => 'الصاج الأسود والمجلفن',
            'materials.en' => 'Black and galvanized sheet',
            'products' => [$product->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $service->refresh();

    expect($service->getTranslation('materials', 'en'))->toBe('Black and galvanized sheet')
        ->and($service->products()->pluck('products.id')->all())->toBe([$product->id]);
});

it('blocks publishing a service without a real photo', function () {
    $service = Service::where('slug', 'metal-cutting')->first();
    $service->update(['summary' => ['ar' => 'ملخص', 'en' => 'Summary'], 'body' => ['ar' => 'شرح', 'en' => 'Body']]);

    Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm(['review_status' => ReviewStatus::Published->value])
        ->call('save')
        ->assertNotified(__('admin.publication.blocked'));

    expect($service->fresh()->isPublished())->toBeFalse();
});

it('publishes a guide only after a technical review is recorded', function () {
    $guide = Guide::where('slug', 'request-steel-quotation')->first();
    $content = [
        'excerpt.ar' => 'مقدمة', 'excerpt.en' => 'Intro',
        'body.ar' => '<p>المحتوى</p>', 'body.en' => '<p>Body</p>',
        'review_status' => ReviewStatus::Published->value,
    ];

    Livewire::test(EditGuide::class, ['record' => $guide->getRouteKey()])
        ->fillForm($content)
        ->call('save')
        ->assertNotified(__('admin.publication.blocked'));

    Livewire::test(EditGuide::class, ['record' => $guide->getRouteKey()])
        ->fillForm($content + ['technical_reviewer' => 'م. مراجع فني', 'technically_reviewed_at' => '2026-11-01'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($guide->fresh()->isPublished())->toBeTrue();
});

it('creates a guide linked to a category', function () {
    Livewire::test(CreateGuide::class)
        ->fillForm([
            'title.ar' => 'دليل جديد', 'title.en' => 'New guide',
            'slug' => 'new-guide',
            'categories' => [Product::first()->primary_category_id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Guide::where('slug', 'new-guide')->first()->categories()->count())->toBe(1);
});

it('rejects a future date for completed work', function () {
    Livewire::test(CreateWorkItem::class)
        ->fillForm(['title.ar' => 'تحميل', 'title.en' => 'Loading', 'slug' => 'loading', 'performed_on' => now()->addMonth()->toDateString()])
        ->call('create')
        ->assertHasFormErrors(['performed_on']);

    expect(WorkItem::count())->toBe(0);
});

it('edits fixed pages but never creates or deletes them', function () {
    $page = Page::where('key', 'privacy')->first();

    Livewire::test(ListPages::class)->assertActionDoesNotExist('create');

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertActionDoesNotExist('delete')
        ->fillForm(['body.ar' => '<p>نص الخصوصية</p>', 'body.en' => '<p>Privacy text</p>'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->fresh()->getTranslation('body', 'en'))->toBe('<p>Privacy text</p>')
        ->and($page->fresh()->key)->toBe('privacy');
});

it('manages faqs with both languages', function () {
    Livewire::test(ManageFaqs::class)
        ->callAction(TestAction::make('create'), data: [
            'question' => ['ar' => 'هل يعني إرسال الطلب تأكيد التوفر؟', 'en' => 'Does sending a request confirm availability?'],
            'answer' => ['ar' => 'لا، يتم التأكيد بعد مراجعة الطلب.', 'en' => 'No, availability is confirmed after review.'],
            'group' => 'quotes',
            'show_on_home' => true,
        ])
        ->assertHasNoActionErrors();

    $faq = Faq::first();

    expect($faq->getTranslation('question', 'en'))->toBe('Does sending a request confirm availability?')
        ->and($faq->show_on_home)->toBeTrue()
        ->and($faq->review_status)->toBe(ReviewStatus::Draft);
});

it('keeps sales users out of content management', function () {
    $sales = User::factory()->create();
    $sales->assignRole(Role::Sales->value);
    $this->actingAs($sales);

    foreach (['services', 'guides', 'industries', 'work-items', 'faqs', 'pages'] as $slug) {
        $this->get("/admin/{$slug}")->assertForbidden();
    }
});
