<?php

namespace App\Http\Controllers\Site;

use App\Domain\Quotes\Actions\SubmitQuote;
use App\Domain\Quotes\Enums\FulfillmentMethod;
use App\Domain\Quotes\Exceptions\EmptyQuote;
use App\Domain\Quotes\QuoteCartManager;
use App\Domain\Quotes\Support\QuoteCartPresenter;
use App\Domain\Seo\SeoData;
use App\Domain\Seo\SeoTitle;
use App\Http\Middleware\CaptureAttribution;
use App\Http\Requests\Site\SubmitQuoteRequest;
use App\Models\QuoteAttachment;
use App\Models\SaleUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Honeypot\Exceptions\SpamException;
use Spatie\Honeypot\Honeypot;
use Spatie\Honeypot\SpamProtection;
use Throwable;

class QuoteRequestController
{
    public const SESSION_KEY = 'quote_submitted';

    public function __construct(private readonly QuoteCartManager $carts) {}

    public function create(Request $request, Honeypot $honeypot): Response
    {
        $locale = app()->getLocale();

        $items = QuoteCartPresenter::items($this->carts->current($request), $locale);

        return Inertia::render('quote/create', [
            'items' => $items,
            'itemsLabel' => trans_choice('site.quote.items_count', count($items), ['count' => count($items)]),
            'units' => SaleUnit::orderBy('sort')->get()->map(fn (SaleUnit $unit) => (string) $unit->getTranslation('name', $locale))->filter()->unique()->values()->all(),
            'fulfillment' => collect(FulfillmentMethod::cases())->map(fn (FulfillmentMethod $method) => ['value' => $method->value, 'label' => $method->label($locale)])->all(),
            'limits' => [
                'files' => QuoteAttachment::MAX_FILES,
                'sizeMb' => QuoteAttachment::MAX_SIZE_KB / 1024,
                'accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png',
            ],
            'honeypot' => $honeypot->toArray(),
            'idempotencyKey' => (string) Str::uuid(),
            'seo' => SeoData::make(SeoTitle::section(__('site.quote.title'), $locale), __('site.quote.lead'))->noindex()->toArray($request),
        ]);
    }

    public function store(SubmitQuoteRequest $request, SpamProtection $spam, SubmitQuote $submit, string $locale): RedirectResponse
    {
        try {
            $spam->check($request->all());
        } catch (SpamException) {
            return back()->withErrors(['form' => __('site.quote.errors.save_failed')]);
        }

        try {
            $quote = $submit->handle(
                $this->carts->current($request),
                $request->safe()->except(['attachments', 'privacy']),
                Arr::wrap($request->file('attachments', [])),
                $locale,
                [
                    'source' => $request->session()->get(CaptureAttribution::SESSION_KEY),
                    'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                ],
            );
        } catch (EmptyQuote) {
            return back()->withErrors(['items' => __('site.quote.errors.empty')]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['form' => __('site.quote.errors.save_failed')]);
        }

        $this->carts->forget($request);
        $request->session()->put(self::SESSION_KEY, ['reference' => $quote->reference, 'items' => $quote->items()->count()]);

        return redirect()->to(url("{$locale}/request-quote/success"), 303);
    }

    public function success(Request $request, string $locale): Response|RedirectResponse
    {
        $submitted = $request->session()->get(self::SESSION_KEY);

        if (! is_array($submitted) || ! is_string($submitted['reference'] ?? null)) {
            return redirect()->to(url("{$locale}/request-quote"));
        }

        return Inertia::render('quote/success', [
            'reference' => $submitted['reference'],
            'itemsLabel' => trans_choice('site.quote.success_items', max(1, (int) ($submitted['items'] ?? 1)), ['count' => (int) ($submitted['items'] ?? 1)]),
            'seo' => SeoData::make(SeoTitle::section(__('site.quote.success_title'), $locale), __('site.quote.success_lead'))->noindex()->toArray($request),
        ]);
    }
}
