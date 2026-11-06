import { Link } from '@inertiajs/react';
import { useSite } from '@/hooks/use-site';
import { localized } from '@/lib/site';
import { CategoryGlyph } from './category-glyph';

export function EmptyState({ title, body }: { title: string; body: string }) {
    const { locale, ui } = useSite();

    return (
        <div className="rounded-2xl border border-dashed border-steel-300 bg-white px-6 py-16 text-center">
            <CategoryGlyph slug="sections-bars" className="mx-auto size-16 text-steel-300" />
            <h2 className="mt-6 text-xl font-semibold text-navy">{title}</h2>
            <p className="mx-auto mt-2 max-w-md leading-7 text-steel-500">{body}</p>
            <Link href={localized(locale, 'request-quote')} className="mt-6 inline-flex rounded-full bg-gold px-6 py-3 font-semibold text-navy transition hover:bg-[#f0c64a]">
                {ui.actions.request_quote}
            </Link>
        </div>
    );
}
