import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/site/page-header';
import { ProductCard } from '@/components/site/product-card';
import { ResponsiveImage } from '@/components/site/responsive-image';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { Image, ProductCardData, SeoProps } from '@/types';

type ArticleProps = {
    kind: 'services' | 'guides';
    article: {
        title: string;
        summary: string | null;
        body: string | null;
        image: Image | null;
        gallery: Image[];
        facts: { label: string; value: string }[];
        meta: { published: string | null; reviewer: string | null; reviewedAt: string | null } | null;
    };
    products: ProductCardData[];
    seo: SeoProps;
};

export default function Article({ kind, article, products, seo }: ArticleProps) {
    const { locale, ui } = useSite();

    return (
        <SiteLayout seo={seo}>
            <PageHeader
                title={article.title}
                lead={article.summary}
                crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: ui.nav[kind], href: localized(locale, kind) }, { label: article.title }]}
            >
                {article.meta && (
                    <p className="mt-6 flex flex-wrap gap-x-6 gap-y-1 text-sm text-steel-400">
                        {article.meta.published && <span>{ui.content.published.replace(':date', article.meta.published)}</span>}
                        {article.meta.reviewer && <span>{ui.content.reviewed_by.replace(':name', article.meta.reviewer)}</span>}
                    </p>
                )}
            </PageHeader>

            <section className="mx-auto grid max-w-7xl gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_20rem] lg:px-8">
                <article className="min-w-0">
                    {article.image && (
                        <div className="mb-10 aspect-[16/9] overflow-hidden rounded-2xl">
                            <ResponsiveImage image={article.image} alt={article.image.alt || article.title} priority sizes="(min-width: 1024px) 65vw, 100vw" />
                        </div>
                    )}
                    {article.body && <div className="rich-text text-lg" dangerouslySetInnerHTML={{ __html: article.body }} />}
                    {article.gallery.length > 0 && (
                        <ul className="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3">
                            {article.gallery.map((image) => (
                                <li key={image.src} className="aspect-square overflow-hidden rounded-xl">
                                    <ResponsiveImage image={image} sizes="(min-width: 768px) 22vw, 50vw" />
                                </li>
                            ))}
                        </ul>
                    )}
                </article>

                <aside className="space-y-6 lg:sticky lg:top-[calc(var(--header-height)+1.5rem)] lg:self-start">
                    {article.facts.map((fact) => (
                        <div key={fact.label} className="rounded-2xl border border-steel-200 bg-white p-5">
                            <h2 className="text-sm font-semibold text-blue">{fact.label}</h2>
                            <p className="mt-2 leading-7 whitespace-pre-line text-steel-600">{fact.value}</p>
                        </div>
                    ))}
                    <div className="steel-texture rounded-2xl p-6 text-white">
                        <h2 className="font-semibold">{ui.content.quote_cta}</h2>
                        <p className="mt-2 text-sm leading-6 text-steel-300">{ui.content.quote_cta_body}</p>
                        <Link href={localized(locale, 'request-quote')} className="mt-5 inline-flex rounded-full bg-gold px-5 py-2.5 text-sm font-semibold text-navy">
                            {ui.actions.request_quote}
                        </Link>
                    </div>
                </aside>
            </section>

            {products.length > 0 && (
                <section className="bg-steel-100/60">
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <h2 className="mb-6 font-display text-2xl font-bold">{ui.content.related_products}</h2>
                        <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            {products.map((product) => (
                                <li key={product.slug}>
                                    <ProductCard product={product} />
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>
            )}
        </SiteLayout>
    );
}
