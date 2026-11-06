import { Link } from '@inertiajs/react';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { EmptyState } from '@/components/site/empty-state';
import { PageHeader } from '@/components/site/page-header';
import { Pagination } from '@/components/site/pagination';
import { ProductCard } from '@/components/site/product-card';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { Image, Paginated, ProductCardData, SeoProps } from '@/types';

type CategoryCard = { slug: string; name: string; description: string | null; image: Image | null; productsLabel: string };

export default function ProductsIndex({ categories, products, seo }: { categories: CategoryCard[]; products: Paginated<ProductCardData>; seo: SeoProps }) {
    const { locale, ui } = useSite();

    return (
        <SiteLayout seo={seo}>
            <PageHeader
                title={ui.catalog.all_products}
                lead={ui.catalog.products_lead}
                crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: ui.nav.products }]}
            >
                {categories.length > 0 && (
                    <ul className="mt-10 flex gap-3 overflow-x-auto pb-2 [scrollbar-width:thin]">
                        {categories.map((category) => (
                            <li key={category.slug} className="shrink-0">
                                <Link
                                    href={localized(locale, `products/${category.slug}`)}
                                    className="group flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 transition hover:border-gold/50 hover:bg-white/[0.08]"
                                >
                                    <CategoryGlyph slug={category.slug} className="size-9 text-sky transition group-hover:text-gold" />
                                    <span>
                                        <span className="block text-sm font-semibold whitespace-nowrap">{category.name}</span>
                                        <span className="block text-xs text-steel-400">{category.productsLabel}</span>
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </PageHeader>

            <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                {products.data.length === 0 ? (
                    <EmptyState title={ui.catalog.empty_title} body={ui.catalog.empty_body} />
                ) : (
                    <>
                        <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {products.data.map((product) => (
                                <li key={product.slug}>
                                    <ProductCard product={product} />
                                </li>
                            ))}
                        </ul>
                        <Pagination page={products} />
                    </>
                )}
            </section>
        </SiteLayout>
    );
}
