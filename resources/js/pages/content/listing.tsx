import { Link } from '@inertiajs/react';
import { motion } from 'motion/react';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { PageHeader } from '@/components/site/page-header';
import { ResponsiveImage } from '@/components/site/responsive-image';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { Image, SeoProps } from '@/types';

type Item = {
    slug: string;
    title: string;
    summary: string | null;
    body?: string | null;
    image: Image | null;
    gallery?: Image[];
    date?: string | null;
    href: string | null;
};

const GLYPHS: Record<string, string> = { services: 'metal-forming', industries: 'industrial-equipment', guides: 'sections-bars', work: 'lifting-pulling' };

export default function Listing({ kind, items, seo }: { kind: 'services' | 'industries' | 'guides' | 'work'; items: Item[]; seo: SeoProps }) {
    const { locale, ui } = useSite();

    return (
        <SiteLayout seo={seo}>
            <PageHeader
                title={ui.nav[kind]}
                lead={ui.content[`${kind}_lead`]}
                crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: ui.nav[kind] }]}
                aside={<CategoryGlyph slug={GLYPHS[kind]} className="hidden size-24 text-sky/70 lg:block" />}
            />
            <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <ul className={kind === 'industries' ? 'grid gap-5 md:grid-cols-2' : 'grid gap-5 sm:grid-cols-2 lg:grid-cols-3'}>
                    {items.map((item, index) => {
                        const content = (
                            <>
                                {item.image && (
                                    <div className="aspect-[16/10] overflow-hidden bg-steel-100">
                                        <ResponsiveImage image={item.image} alt={item.image.alt || item.title} sizes="(min-width: 1024px) 33vw, 100vw" className="transition duration-700 group-hover:scale-105" />
                                    </div>
                                )}
                                <div className="flex flex-1 flex-col gap-2 p-6">
                                    {item.date && <span className="stamp text-xs text-steel-500">{item.date}</span>}
                                    <h2 className="text-lg font-semibold text-navy">{item.title}</h2>
                                    {item.summary && <p className="leading-7 text-steel-500">{item.summary}</p>}
                                    {item.body && <div className="rich-text mt-2 text-sm" dangerouslySetInnerHTML={{ __html: item.body }} />}
                                </div>
                            </>
                        );

                        return (
                            <motion.li
                                key={item.slug}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ duration: 0.5, delay: (index % 3) * 0.08 }}
                            >
                                {item.href ? (
                                    <Link href={item.href} className="group flex h-full flex-col overflow-hidden rounded-2xl border border-steel-200 bg-white transition hover:-translate-y-1 hover:shadow-xl">
                                        {content}
                                    </Link>
                                ) : (
                                    <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-steel-200 bg-white">{content}</article>
                                )}
                            </motion.li>
                        );
                    })}
                </ul>
            </section>
        </SiteLayout>
    );
}
