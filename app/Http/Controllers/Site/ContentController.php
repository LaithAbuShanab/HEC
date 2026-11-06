<?php

namespace App\Http\Controllers\Site;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Presenters\ProductPresenter;
use App\Domain\Company\CompanyProfile;
use App\Domain\Seo\OrganizationSchema;
use App\Domain\Seo\SeoData;
use App\Domain\Seo\SeoTitle;
use App\Domain\Shared\Support\RichText;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use App\Models\WorkItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContentController
{
    public function services(Request $request): Response
    {
        $locale = app()->getLocale();
        $items = Service::published()->with('media')->orderBy('sort')->get();
        abort_if($items->isEmpty(), 404);

        return $this->listing($request, 'services', $items->map(fn (Service $service) => [
            'slug' => $service->slug,
            'title' => $service->getTranslation('title', $locale),
            'summary' => $service->getTranslation('summary', $locale, false) ?: null,
            'image' => $service->imageFor('cover', $locale),
            'href' => url("{$locale}/services/{$service->slug}"),
        ])->all());
    }

    public function service(Request $request, string $locale, string $slug): Response
    {
        $service = Service::published()->with(['media', 'products' => fn ($query) => $query->where('review_status', ReviewStatus::Published)->with(['media', 'primaryCategory'])])
            ->where('slug', $slug)->firstOrFail();

        $title = (string) $service->getTranslation('title', $locale);
        $summary = $service->getTranslation('summary', $locale, false) ?: null;

        return Inertia::render('content/article', [
            'kind' => 'services',
            'article' => [
                'title' => $title,
                'summary' => $summary,
                'body' => RichText::clean($service->getTranslation('body', $locale, false)),
                'image' => $service->imageFor('cover', $locale),
                'gallery' => $this->gallery($service->getMedia('gallery'), $locale),
                'facts' => array_values(array_filter([
                    $this->fact('materials', $service->getTranslation('materials', $locale, false)),
                    $this->fact('capabilities', $service->getTranslation('capabilities', $locale, false)),
                    $this->fact('pricing_requirements', $service->getTranslation('pricing_requirements', $locale, false)),
                ])),
                'meta' => null,
            ],
            'products' => $this->productCards($service->products, $locale),
            'seo' => $this->articleSeo($request, $title, $summary, $service, 'services', $locale)->toArray($request),
        ]);
    }

    public function industries(Request $request): Response
    {
        $locale = app()->getLocale();
        $items = Industry::published()->with('media')->orderBy('sort')->get();
        abort_if($items->isEmpty(), 404);

        return $this->listing($request, 'industries', $items->map(fn (Industry $industry) => [
            'slug' => $industry->slug,
            'title' => $industry->getTranslation('title', $locale),
            'summary' => $industry->getTranslation('summary', $locale, false) ?: null,
            'body' => RichText::clean($industry->getTranslation('body', $locale, false)),
            'image' => $industry->imageFor('cover', $locale),
            'href' => null,
        ])->all());
    }

    public function guides(Request $request): Response
    {
        $locale = app()->getLocale();
        $items = Guide::published()->with('media')->latest('published_at')->get();
        abort_if($items->isEmpty(), 404);

        return $this->listing($request, 'guides', $items->map(fn (Guide $guide) => [
            'slug' => $guide->slug,
            'title' => $guide->getTranslation('title', $locale),
            'summary' => $guide->getTranslation('excerpt', $locale, false) ?: null,
            'image' => $guide->imageFor('cover', $locale),
            'href' => url("{$locale}/guides/{$guide->slug}"),
        ])->all());
    }

    public function guide(Request $request, string $locale, string $slug): Response
    {
        $guide = Guide::published()->with(['media', 'products' => fn ($query) => $query->where('review_status', ReviewStatus::Published)->with(['media', 'primaryCategory'])])
            ->where('slug', $slug)->firstOrFail();

        $title = (string) $guide->getTranslation('title', $locale);
        $excerpt = $guide->getTranslation('excerpt', $locale, false) ?: null;
        $seo = $this->articleSeo($request, $title, $excerpt, $guide, 'guides', $locale)->type('article')->schema(array_filter([
            '@type' => 'Article',
            'headline' => $title,
            'description' => $excerpt,
            'datePublished' => $guide->published_at?->toAtomString(),
            'dateModified' => $guide->updated_at?->toAtomString(),
            'image' => $guide->imageFor('cover', $locale)['src'] ?? null,
            'inLanguage' => $locale,
            'publisher' => ['@id' => url('/#organization')],
            'mainEntityOfPage' => url("{$locale}/guides/{$guide->slug}"),
        ]));

        return Inertia::render('content/article', [
            'kind' => 'guides',
            'article' => [
                'title' => $title,
                'summary' => $excerpt,
                'body' => RichText::clean($guide->getTranslation('body', $locale, false)),
                'image' => $guide->imageFor('cover', $locale),
                'gallery' => [],
                'facts' => [],
                'meta' => [
                    'published' => $guide->published_at?->toDateString(),
                    'reviewer' => $guide->technical_reviewer,
                    'reviewedAt' => $guide->technically_reviewed_at?->toDateString(),
                ],
            ],
            'products' => $this->productCards($guide->products, $locale),
            'seo' => $seo->toArray($request),
        ]);
    }

    public function work(Request $request): Response
    {
        $locale = app()->getLocale();
        $items = WorkItem::published()->with('media')->orderBy('sort')->get();
        abort_if($items->isEmpty(), 404);

        return $this->listing($request, 'work', $items->map(fn (WorkItem $item) => [
            'slug' => $item->slug,
            'title' => $item->getTranslation('title', $locale),
            'summary' => $item->getTranslation('summary', $locale, false) ?: null,
            'image' => $item->imageFor('gallery', $locale),
            'gallery' => $this->gallery($item->getMedia('gallery'), $locale),
            'date' => $item->performed_on?->toDateString(),
            'href' => null,
        ])->all());
    }

    public function faq(Request $request): Response
    {
        $locale = app()->getLocale();
        $faqs = Faq::published()->orderBy('sort')->get();
        abort_if($faqs->isEmpty(), 404);

        $groups = $faqs->groupBy('group')->map(fn ($items, $group) => [
            'key' => (string) $group,
            'label' => __("admin.content.faq_groups.{$group}", locale: $locale),
            'items' => $items->map(fn (Faq $faq) => [
                'question' => $faq->getTranslation('question', $locale),
                'answer' => $faq->getTranslation('answer', $locale),
            ])->values()->all(),
        ])->values()->all();

        return Inertia::render('content/faq', [
            'groups' => $groups,
            'seo' => SeoData::make(SeoTitle::section(__('site.nav.faq'), $locale), __('site.content.faq_lead'))->toArray($request),
        ]);
    }

    public function page(Request $request, string $locale, string $key): Response
    {
        $page = Page::published()->where('key', $key)->firstOrFail();
        $title = (string) $page->getTranslation('title', $locale);
        $body = RichText::clean($page->getTranslation('body', $locale, false));

        return Inertia::render('content/page', [
            'page' => ['key' => $page->key, 'title' => $title, 'body' => $body, 'updated' => $page->updated_at?->toDateString()],
            'seo' => SeoData::make(SeoTitle::section($title, $locale), $body)->overrides($page->seo, $locale)->toArray($request),
        ]);
    }

    public function contact(Request $request, CompanyProfile $profile): Response
    {
        $locale = app()->getLocale();
        $seo = SeoData::make(SeoTitle::section(__('site.nav.contact'), $locale), __('site.content.contact_lead'));

        foreach (OrganizationSchema::build($locale) as $schema) {
            $seo->schema($schema);
        }

        return Inertia::render('content/contact', [
            'profile' => $profile->toArray($locale),
            'seo' => $seo->toArray($request),
        ]);
    }

    private function listing(Request $request, string $kind, array $items): Response
    {
        $locale = app()->getLocale();
        $title = __("site.nav.{$kind}");

        return Inertia::render('content/listing', [
            'kind' => $kind,
            'items' => $items,
            'seo' => SeoData::make(SeoTitle::section($title, $locale), __("site.content.{$kind}_lead"))->toArray($request),
        ]);
    }

    private function articleSeo(Request $request, string $title, ?string $description, Model $model, string $section, string $locale): SeoData
    {
        $seo = SeoData::make(SeoTitle::section($title, $locale), $description);

        if (method_exists($model, 'imageFor')) {
            $seo->image($model->imageFor('cover', $locale)['src'] ?? null);
        }

        $settings = $model->getAttribute('seo');

        return $seo->overrides(is_array($settings) ? $settings : null, $locale)->schema([
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('site.nav.home'), 'item' => url($locale)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __("site.nav.{$section}"), 'item' => url("{$locale}/{$section}")],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $request->url()],
            ],
        ]);
    }

    private function productCards(iterable $products, string $locale): array
    {
        $cards = [];

        foreach ($products as $product) {
            if ($product instanceof Product) {
                $cards[] = ProductPresenter::card($product, $locale);
            }
        }

        return array_slice($cards, 0, 8);
    }

    private function gallery(iterable $media, string $locale): array
    {
        $images = [];

        foreach ($media as $item) {
            if ($item instanceof Media) {
                $images[] = Product::presentImage($item, $locale);
            }
        }

        return $images;
    }

    private function fact(string $key, mixed $value): ?array
    {
        return filled($value) ? ['label' => __("site.content.{$key}"), 'value' => (string) $value] : null;
    }
}
