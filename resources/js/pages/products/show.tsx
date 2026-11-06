import { Link } from '@inertiajs/react';
import { FileText, MessageCircle, Plus } from 'lucide-react';
import { useState } from 'react';
import { AvailabilityBadge } from '@/components/site/availability-badge';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { PageHeader } from '@/components/site/page-header';
import { ProductCard } from '@/components/site/product-card';
import { ResponsiveImage } from '@/components/site/responsive-image';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import { cn } from '@/lib/utils';
import type { Image, ProductCardData, SeoProps } from '@/types';

type Variant = {
    id: number;
    label: string | null;
    standard: string | null;
    thickness: string | null;
    width: string | null;
    length: string | null;
    diameter: string | null;
    nominalSize: string | null;
    availability: { value: string; label: string } | null;
};

type ProductDetail = ProductCardData & {
    id: string;
    description: string | null;
    uses: string | null;
    gallery: Image[];
    specs: { key: string; label: string; value: string; unit: string | null }[];
    variants: Variant[];
    saleUnit: string | null;
    availabilityUpdatedAt: string | null;
    documents: { name: string; url: string; size: string }[];
    price: { amount: number; currency: string } | null;
};

type ShowProps = {
    product: ProductDetail;
    category: { slug: string; name: string };
    related: ProductCardData[];
    guides: { slug: string; title: string; excerpt: string | null }[];
    seo: SeoProps;
};

const DIMENSIONS = ['thickness', 'width', 'length', 'diameter', 'nominalSize'] as const;

export default function ProductShow({ product, category, related, guides, seo }: ShowProps) {
    const { locale, ui, company } = useSite();
    const [active, setActive] = useState(0);
    const [variantId, setVariantId] = useState<number | null>(product.variants[0]?.id ?? null);
    const variant = product.variants.find((item) => item.id === variantId) ?? null;
    const image = product.gallery[active] ?? null;
    const columns = DIMENSIONS.filter((key) => product.variants.some((item) => item[key] !== null));
    const availability = variant?.availability ?? product.availability;

    const quoteHref = `${localized(locale, 'request-quote')}?${new URLSearchParams({ product: product.slug, ...(variant ? { variant: String(variant.id) } : {}) }).toString()}`;
    const whatsappText = [ui.catalog.whatsapp_message.replace(':name', product.name), variant?.label, seo.canonical].filter(Boolean).join('\n');
    const whatsappHref = company.whatsapp ? `${company.whatsapp.href}?text=${encodeURIComponent(whatsappText)}` : null;

    return (
        <SiteLayout seo={seo}>
            <PageHeader
                title={product.name}
                crumbs={[
                    { label: ui.nav.home, href: localized(locale) },
                    { label: ui.nav.products, href: localized(locale, 'products') },
                    { label: category.name, href: localized(locale, `products/${category.slug}`) },
                    { label: product.name },
                ]}
            />

            <section className="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.15fr_1fr] lg:px-8">
                <div>
                    <div className="relative aspect-[4/3] overflow-hidden rounded-2xl border border-steel-200 bg-gradient-to-br from-steel-100 to-steel-200">
                        {image ? (
                            <ResponsiveImage image={image} alt={image.alt || product.name} priority sizes="(min-width: 1024px) 55vw, 100vw" />
                        ) : (
                            <div className="grid size-full place-items-center">
                                <CategoryGlyph slug={category.slug} className="size-32 text-steel-400" />
                            </div>
                        )}
                    </div>
                    {product.gallery.length > 1 && (
                        <ul className="mt-4 flex gap-3 overflow-x-auto pb-1">
                            {product.gallery.map((item, index) => (
                                <li key={item.src}>
                                    <button
                                        type="button"
                                        onClick={() => setActive(index)}
                                        aria-label={item.alt || `${product.name} ${index + 1}`}
                                        aria-pressed={index === active}
                                        className={cn('block size-20 overflow-hidden rounded-xl border-2 transition', index === active ? 'border-blue' : 'border-transparent opacity-70 hover:opacity-100')}
                                    >
                                        <ResponsiveImage image={item} alt="" sizes="80px" />
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className="flex flex-col gap-6">
                    <div className="flex flex-wrap items-center gap-3">
                        <AvailabilityBadge value={availability.value} label={availability.label} />
                        {product.availabilityUpdatedAt && (
                            <span className="text-xs text-steel-500">{ui.catalog.availability_updated.replace(':date', product.availabilityUpdatedAt)}</span>
                        )}
                    </div>

                    {product.summary && <p className="text-lg leading-8 text-steel-600">{product.summary}</p>}

                    <div className="rounded-2xl border border-steel-200 bg-white p-5">
                        <div className="flex items-baseline justify-between gap-4">
                            <span className="text-sm text-steel-500">{product.saleUnit && `${ui.specs.sale_unit}: ${product.saleUnit}`}</span>
                            {product.price ? (
                                <span className="stamp text-2xl font-bold text-navy">
                                    <bdi dir="ltr">
                                        {product.price.amount} {product.price.currency}
                                    </bdi>
                                </span>
                            ) : (
                                <span className="font-semibold text-navy">{ui.catalog.price_on_request}</span>
                            )}
                        </div>

                        {product.variants.length > 0 && (
                            <fieldset className="mt-5">
                                <legend className="mb-2 text-sm font-semibold text-navy">{ui.catalog.variants}</legend>
                                <div className="flex flex-wrap gap-2">
                                    {product.variants.map((item) => (
                                        <label
                                            key={item.id}
                                            className={cn(
                                                'cursor-pointer rounded-lg border px-3 py-2 text-sm transition has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-sky',
                                                item.id === variantId ? 'border-navy bg-navy text-white' : 'border-steel-200 text-navy hover:border-blue/50',
                                            )}
                                        >
                                            <input type="radio" name="variant" className="sr-only" checked={item.id === variantId} onChange={() => setVariantId(item.id)} />
                                            <bdi>{item.label}</bdi>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>
                        )}

                        <div className="mt-6 flex flex-wrap gap-3">
                            <Link
                                href={quoteHref}
                                data-analytics="add_to_quote"
                                className="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-gold px-6 py-3 font-semibold text-navy shadow-[0_0_24px_rgb(228_180_42/0.3)] transition hover:bg-[#f0c64a]"
                            >
                                <Plus aria-hidden className="size-4" />
                                {ui.catalog.add_to_quote}
                            </Link>
                            {whatsappHref && (
                                <a
                                    href={whatsappHref}
                                    target="_blank"
                                    rel="noopener"
                                    data-analytics="whatsapp_click"
                                    className="inline-flex items-center justify-center gap-2 rounded-full border border-[#1f9d55]/40 px-5 py-3 font-semibold text-[#17803f] transition hover:bg-[#1f9d55]/5"
                                >
                                    <MessageCircle aria-hidden className="size-4" />
                                    {ui.catalog.ask_whatsapp}
                                </a>
                            )}
                        </div>
                        <p className="mt-4 text-xs text-steel-500">{ui.home.quote_note}</p>
                    </div>

                    {product.specs.length > 0 ? (
                        <section>
                            <h2 className="mb-3 font-display text-xl font-bold">{ui.catalog.specs}</h2>
                            <dl className="divide-y divide-steel-200 overflow-hidden rounded-2xl border border-steel-200 bg-white">
                                {product.specs.map((spec) => (
                                    <div key={spec.key} className="grid grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] gap-4 px-5 py-3 text-sm">
                                        <dt className="text-steel-500">{spec.label}</dt>
                                        <dd className="font-medium text-navy">
                                            <bdi>{spec.value}</bdi> {spec.unit && <span className="text-steel-500">{spec.unit}</span>}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        </section>
                    ) : (
                        <div className="rounded-2xl border border-dashed border-blue/40 bg-blue/5 p-5">
                            <h2 className="font-semibold text-navy">{ui.catalog.confirm_specs}</h2>
                            <p className="mt-1 text-sm leading-6 text-steel-600">{ui.catalog.confirm_specs_body}</p>
                        </div>
                    )}
                </div>
            </section>

            {columns.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
                    <h2 className="mb-4 font-display text-2xl font-bold">{ui.catalog.variants}</h2>
                    <div className="overflow-x-auto rounded-2xl border border-steel-200 bg-white">
                        <table className="w-full text-sm">
                            <thead className="bg-steel-50 text-start text-steel-500">
                                <tr>
                                    <th scope="col" className="px-5 py-3 text-start font-medium">{ui.catalog.variant}</th>
                                    {columns.map((key) => (
                                        <th key={key} scope="col" className="px-5 py-3 text-start font-medium">
                                            {key === 'nominalSize' ? ui.catalog.nominal_size : `${ui.catalog[key]} (${ui.catalog.mm})`}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-steel-100">
                                {product.variants.map((item) => (
                                    <tr key={item.id} className={cn(item.id === variantId && 'bg-gold/10')}>
                                        <th scope="row" className="px-5 py-3 text-start font-medium text-navy">
                                            <bdi>{item.label}</bdi>
                                        </th>
                                        {columns.map((key) => (
                                            <td key={key} className="stamp px-5 py-3 text-navy">
                                                <bdi dir="ltr">{item[key] ?? '—'}</bdi>
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            )}

            {(product.uses || product.description || product.documents.length > 0) && (
                <section className="mx-auto grid max-w-7xl gap-10 px-4 pb-16 sm:px-6 lg:grid-cols-[1.5fr_1fr] lg:px-8">
                    <div className="space-y-10">
                        {product.description && (
                            <div>
                                <h2 className="mb-4 font-display text-2xl font-bold">{ui.catalog.description}</h2>
                                <div className="rich-text" dangerouslySetInnerHTML={{ __html: product.description }} />
                            </div>
                        )}
                        {product.uses && (
                            <div>
                                <h2 className="mb-4 font-display text-2xl font-bold">{ui.catalog.uses}</h2>
                                <p className="leading-8 whitespace-pre-line text-steel-600">{product.uses}</p>
                            </div>
                        )}
                    </div>
                    {product.documents.length > 0 && (
                        <aside>
                            <h2 className="mb-4 font-display text-2xl font-bold">{ui.catalog.documents}</h2>
                            <ul className="space-y-3">
                                {product.documents.map((document) => (
                                    <li key={document.url}>
                                        <a
                                            href={document.url}
                                            target="_blank"
                                            rel="noopener"
                                            data-analytics="catalog_download"
                                            className="flex items-center gap-3 rounded-xl border border-steel-200 bg-white px-4 py-3 transition hover:border-blue/40"
                                        >
                                            <FileText aria-hidden className="size-5 text-blue" />
                                            <span className="flex-1 font-medium text-navy">{document.name}</span>
                                            <span className="stamp text-xs text-steel-500">{document.size}</span>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </aside>
                    )}
                </section>
            )}

            {(related.length > 0 || guides.length > 0) && (
                <section className="bg-steel-100/60">
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        {related.length > 0 && (
                            <>
                                <h2 className="mb-6 font-display text-2xl font-bold">{ui.catalog.related}</h2>
                                <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                                    {related.map((item) => (
                                        <li key={item.slug}>
                                            <ProductCard product={item} />
                                        </li>
                                    ))}
                                </ul>
                            </>
                        )}
                        {guides.length > 0 && (
                            <div className={cn(related.length > 0 && 'mt-14')}>
                                <h2 className="mb-6 font-display text-2xl font-bold">{ui.catalog.guides}</h2>
                                <ul className="grid gap-4 md:grid-cols-3">
                                    {guides.map((guide) => (
                                        <li key={guide.slug}>
                                            <Link href={localized(locale, `guides/${guide.slug}`)} className="block h-full rounded-2xl border border-steel-200 bg-white p-5 transition hover:border-blue/40">
                                                <h3 className="font-semibold">{guide.title}</h3>
                                                {guide.excerpt && <p className="mt-2 line-clamp-3 text-sm text-steel-500">{guide.excerpt}</p>}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </div>
                </section>
            )}
        </SiteLayout>
    );
}
