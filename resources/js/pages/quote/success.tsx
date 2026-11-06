import { Link } from '@inertiajs/react';
import { Check, Copy, MessageCircle, Phone } from 'lucide-react';
import { useState } from 'react';
import { PageHeader } from '@/components/site/page-header';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { SeoProps } from '@/types';

export default function QuoteSuccess({ reference, itemsLabel, seo }: { reference: string; itemsLabel: string; seo: SeoProps }) {
    const { locale, ui, company } = useSite();
    const q = ui.quote;
    const [copied, setCopied] = useState(false);
    const nextSteps = q.next_steps as unknown as string[];

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(reference);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2500);
        } catch {
            setCopied(false);
        }
    };

    return (
        <SiteLayout seo={seo}>
            <PageHeader title={q.success_title} lead={q.success_lead} crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: q.success_title }]} />

            <section className="mx-auto grid max-w-5xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-[1.2fr_1fr] lg:px-8" data-analytics-event="quote_submit_success">
                <div className="relative overflow-hidden rounded-3xl border border-steel-200 bg-white p-8 shadow-sm">
                    <div aria-hidden className="brushed-sheen absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-gold via-[#f6d77a] to-gold" />
                    <span className="inline-flex size-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                        <Check aria-hidden className="size-6" />
                    </span>
                    <p className="mt-6 text-sm font-medium text-steel-500">{q.reference}</p>
                    <div className="mt-2 flex flex-wrap items-center gap-3">
                        <output className="stamp rounded-xl bg-navy px-5 py-3 font-display text-2xl font-bold tracking-wider text-white sm:text-3xl" dir="ltr" aria-live="polite">
                            {reference}
                        </output>
                        <button
                            type="button"
                            onClick={copy}
                            className="inline-flex items-center gap-2 rounded-full border border-steel-300 px-4 py-2 text-sm font-medium text-navy transition hover:border-navy"
                        >
                            {copied ? <Check aria-hidden className="size-4 text-emerald-700" /> : <Copy aria-hidden className="size-4" />}
                            <span aria-live="polite">{copied ? q.copied : q.copy}</span>
                        </button>
                    </div>
                    <p className="mt-4 text-sm leading-6 text-steel-600">{q.reference_hint}</p>
                    <p className="mt-2 text-sm text-steel-500">{itemsLabel}</p>

                    <div className="mt-8 flex flex-wrap gap-3">
                        <Link href={localized(locale, 'products')} className="rounded-full bg-gold px-6 py-3 font-semibold text-navy transition hover:bg-[#f0c64a]">
                            {ui.actions.browse_products}
                        </Link>
                        <Link href={localized(locale)} className="rounded-full border border-steel-300 px-6 py-3 font-semibold text-navy transition hover:border-navy">
                            {ui.errors.home}
                        </Link>
                    </div>
                </div>

                <div className="space-y-6">
                    <div className="rounded-3xl border border-steel-200 bg-steel-50 p-6">
                        <h2 className="font-display text-lg font-bold">{q.next_title}</h2>
                        <ol className="mt-4 space-y-3">
                            {nextSteps.map((step, index) => (
                                <li key={step} className="flex gap-3 text-sm leading-6 text-steel-700">
                                    <span className="stamp flex size-7 shrink-0 items-center justify-center rounded-full bg-navy text-xs text-white">{index + 1}</span>
                                    {step}
                                </li>
                            ))}
                        </ol>
                    </div>
                    {(company.phone || company.whatsapp) && (
                        <div className="flex flex-wrap gap-3">
                            {company.phone && (
                                <a href={company.phone.href} data-analytics="phone_click" className="inline-flex items-center gap-2 rounded-full border border-steel-300 px-5 py-2.5 text-sm font-semibold text-navy">
                                    <Phone aria-hidden className="size-4" />
                                    <bdi dir="ltr">{company.phone.display}</bdi>
                                </a>
                            )}
                            {company.whatsapp && (
                                <a
                                    href={company.whatsapp.href}
                                    target="_blank"
                                    rel="noopener"
                                    data-analytics="whatsapp_click"
                                    className="inline-flex items-center gap-2 rounded-full border border-[#1f9d55]/40 px-5 py-2.5 text-sm font-semibold text-[#17803f]"
                                >
                                    <MessageCircle aria-hidden className="size-4" />
                                    {ui.actions.whatsapp}
                                </a>
                            )}
                        </div>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
