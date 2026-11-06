import { Link } from '@inertiajs/react';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { EmptyState } from '@/components/site/empty-state';
import { FilterPanel } from '@/components/site/filter-panel';
import type { Facet } from '@/components/site/filter-panel';
import { PageHeader } from '@/components/site/page-header';
import { Pagination } from '@/components/site/pagination';
import { ProductCard } from '@/components/site/product-card';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { Image, Paginated, ProductCardData, SeoProps } from '@/types';

type CategoryProps = {
    category: { slug: string; name: string; description: string | null; image: Image | null };
    products: Paginated<ProductCardData>;
    facets: Facet[];
    filtered: boolean;
    total: number;
    resultsLabel: string;
    siblings: { slug: string; name: string; productsLabel: string }[];
    guides: { slug: string; title: string; excerpt: string | null }[];
    seo: SeoProps;
};

export default function CategoryPage({ category, products, facets, filtered, resultsLabel, siblings, guides, seo }: CategoryProps) {
    const { locale, ui } = useSite();
    const baseUrl = localized(locale, `products/${category.slug}`);

    return (
        <SiteLayout seo={seo}>
            <PageHeader
                title={category.name}
                lead={category.description}
                crumbs={[
                    { label: ui.nav.home, href: localized(locale) },
                    { label: ui.nav.products, href: localized(locale, 'products') },
                    { label: category.name },
                ]}
                aside={<CategoryGlyph slug={category.slug} className="hidden size-28 text-sky/80 lg:block" />}
            />

            <section className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[17rem_1fr] lg:px-8">
                <div>
                    <FilterPanel facets={facets} baseUrl={baseUrl} filtered={filtered} />
                    {siblings.length > 0 && (
                        <nav aria-label={ui.catalog.related_categories} className="mt-10 hidden lg:block">
                            <h2 className="mb-4 text-sm font-semibold text-steel-500">{ui.catalog.related_categories}</h2>
                            <ul className="space-y-1">
                                {siblings.map((sibling) => (
                                    <li key={sibling.slug}>
                                        <Link href={localized(locale, `products/${sibling.slug}`)} className="group flex items-center gap-3 rounded-lg px-2 py-2 text-sm text-navy transition hover:bg-white">
                                            <CategoryGlyph slug={sibling.slug} className="size-6 text-steel-400 group-hover:text-blue" />
                                            {sibling.name}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </nav>
                    )}
                </div>

                <div>
                    <p aria-live="polite" className="mb-6 text-sm text-steel-500">
                        {resultsLabel}
                    </p>
                    {products.data.length === 0 ? (
                        <EmptyState title={filtered ? ui.catalog.no_matches : ui.catalog.empty_title} body={ui.catalog.empty_body} />
                    ) : (
                        <>
                            <ul className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                                {products.data.map((product) => (
                                    <li key={product.slug}>
                                        <ProductCard product={product} />
                                    </li>
                                ))}
                            </ul>
                            <Pagination page={products} />
                        </>
                    )}

                    {guides.length > 0 && (
                        <section className="mt-16">
                            <h2 className="mb-5 font-display text-2xl font-bold">{ui.catalog.guides}</h2>
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
                        </section>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
