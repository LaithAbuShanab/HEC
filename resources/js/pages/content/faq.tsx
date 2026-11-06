import { PageHeader } from '@/components/site/page-header';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { SeoProps } from '@/types';

type Group = { key: string; label: string; items: { question: string; answer: string }[] };

export default function Faq({ groups, seo }: { groups: Group[]; seo: SeoProps }) {
    const { locale, ui } = useSite();

    return (
        <SiteLayout seo={seo}>
            <PageHeader title={ui.nav.faq} lead={ui.content.faq_lead} crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: ui.nav.faq }]} />
            <section className="mx-auto max-w-4xl space-y-12 px-4 py-14 sm:px-6 lg:px-8">
                {groups.map((group) => (
                    <div key={group.key}>
                        <h2 className="mb-4 font-display text-2xl font-bold">{group.label}</h2>
                        <div className="divide-y divide-steel-200 rounded-2xl border border-steel-200 bg-white">
                            {group.items.map((item) => (
                                <details key={item.question} className="group p-6">
                                    <summary className="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-navy">
                                        {item.question}
                                        <span aria-hidden className="grid size-7 shrink-0 place-items-center rounded-full bg-steel-100 text-blue transition group-open:rotate-45">
                                            +
                                        </span>
                                    </summary>
                                    <p className="mt-3 leading-8 whitespace-pre-line text-steel-600">{item.answer}</p>
                                </details>
                            ))}
                        </div>
                    </div>
                ))}
            </section>
        </SiteLayout>
    );
}
