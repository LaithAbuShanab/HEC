import { Link, router } from '@inertiajs/react';
import { Search as SearchIcon } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { EmptyState } from '@/components/site/empty-state';
import { PageHeader } from '@/components/site/page-header';
import { ProductCard } from '@/components/site/product-card';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { ProductCardData, SeoProps } from '@/types';

type SearchProps = {
    query: string;
    products: ProductCardData[];
    categories: { slug: string; name: string }[];
    seo: SeoProps;
};

export default function SearchPage({ query, products, categories, seo }: SearchProps) {
    const { locale, ui } = useSite();
    const [value, setValue] = useState(query);
    const hasResults = products.length > 0 || categories.length > 0;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(localized(locale, 'search'), value.trim() ? { q: value.trim() } : {});
    };

    return (
        <SiteLayout seo={seo}>
            <PageHeader
                title={query ? ui.search.results_for.replace(':query', query) : ui.search.title}
                crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: ui.search.title }]}
            >
                <form role="search" onSubmit={submit} className="relative mt-8 max-w-2xl">
                    <label htmlFor="search-page" className="sr-only">
                        {ui.search.title}
                    </label>
                    <SearchIcon aria-hidden className="pointer-events-none absolute start-5 top-1/2 size-5 -translate-y-1/2 text-steel-300" />
                    <input
                        id="search-page"
                        type="search"
                        value={value}
                        onChange={(event) => setValue(event.target.value)}
                        placeholder={ui.search.prompt}
                        className="h-14 w-full rounded-full border border-white/15 bg-white/[0.06] ps-14 pe-32 text-white placeholder:text-steel-400 outline-none focus:border-sky/70"
                    />
                    <button type="submit" className="absolute end-2 top-1/2 h-10 -translate-y-1/2 rounded-full bg-sky px-5 text-sm font-semibold text-night">
                        {ui.actions.search}
                    </button>
                </form>
            </PageHeader>

            <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                {query && !hasResults && <EmptyState title={ui.search.empty.replace(':query', query)} body={ui.search.empty_body} />}

                {categories.length > 0 && (
                    <div className="mb-12">
                        <h2 className="mb-4 text-sm font-semibold text-steel-500">{ui.search.categories}</h2>
                        <ul className="flex flex-wrap gap-3">
                            {categories.map((category) => (
                                <li key={category.slug}>
                                    <Link href={localized(locale, `products/${category.slug}`)} className="group flex items-center gap-3 rounded-xl border border-steel-200 bg-white px-4 py-3 transition hover:border-blue/40">
                                        <CategoryGlyph slug={category.slug} className="size-8 text-blue" />
                                        <span className="font-semibold text-navy">{category.name}</span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {products.length > 0 && (
                    <>
                        <h2 className="mb-4 text-sm font-semibold text-steel-500">{ui.search.products}</h2>
                        <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {products.map((product) => (
                                <li key={product.slug}>
                                    <ProductCard product={product} />
                                </li>
                            ))}
                        </ul>
                    </>
                )}
            </section>
        </SiteLayout>
    );
}
