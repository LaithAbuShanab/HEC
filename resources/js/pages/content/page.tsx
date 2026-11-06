import { PageHeader } from '@/components/site/page-header';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { SeoProps } from '@/types';

export default function StaticPage({ page, seo }: { page: { key: string; title: string; body: string | null; updated: string | null }; seo: SeoProps }) {
    const { locale, ui } = useSite();

    return (
        <SiteLayout seo={seo}>
            <PageHeader title={page.title} crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: page.title }]}>
                {page.updated && page.key !== 'about' && <p className="mt-4 text-sm text-steel-400">{ui.content.updated.replace(':date', page.updated)}</p>}
            </PageHeader>
            <section className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
                {page.body && <div className="rich-text text-lg" dangerouslySetInnerHTML={{ __html: page.body }} />}
            </section>
        </SiteLayout>
    );
}
