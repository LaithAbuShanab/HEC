import { Link } from '@inertiajs/react';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { HeroSearch } from '@/components/site/hero-search';
import { SparkField } from '@/components/site/spark-field';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { SeoProps } from '@/types';

export default function ErrorPage({ status, seo }: { status: number; seo: SeoProps }) {
    const { locale, ui } = useSite();
    const copy = ui.errors[String(status)] ?? ui.errors['404'];

    return (
        <SiteLayout seo={seo}>
            <section className="steel-texture relative isolate flex min-h-[80svh] items-center overflow-hidden pt-(--header-height) text-white">
                <div aria-hidden className="blueprint-grid absolute inset-0 -z-10 opacity-50" />
                <SparkField className="absolute inset-0 -z-10 size-full" />
                <div className="mx-auto w-full max-w-3xl px-4 py-20 text-center sm:px-6">
                    <CategoryGlyph slug="grinding-cutting" className="mx-auto size-20 text-gold" />
                    <p className="stamp mt-6 text-7xl font-bold text-white/90 sm:text-8xl">{status}</p>
                    <h1 className="mt-4 font-display text-3xl font-bold">{copy.title}</h1>
                    <p className="mx-auto mt-4 max-w-xl leading-8 text-steel-300">{copy.body}</p>
                    {status === 404 || status === 410 ? (
                        <div className="mt-10 flex justify-center">
                            <HeroSearch />
                        </div>
                    ) : null}
                    <Link href={localized(locale)} className="mt-10 inline-flex rounded-full bg-white px-6 py-3 font-semibold text-navy">
                        {ui.errors.home}
                    </Link>
                </div>
            </section>
        </SiteLayout>
    );
}
