import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useSite } from '@/hooks/use-site';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

export function Pagination({ page }: { page: Paginated<unknown> }) {
    const { locale, ui } = useSite();

    if (page.lastPage <= 1) {
        return null;
    }

    const Prev = locale === 'ar' ? ChevronRight : ChevronLeft;
    const Next = locale === 'ar' ? ChevronLeft : ChevronRight;

    return (
        <nav aria-label="pagination" className="mt-12 flex items-center justify-center gap-1.5">
            {page.prev && (
                <Link href={page.prev} preserveScroll rel="prev" className="grid size-10 place-items-center rounded-full border border-steel-200 bg-white text-navy transition hover:border-blue">
                    <Prev aria-hidden className="size-4" />
                </Link>
            )}
            {page.links.map((link) => (
                <Link
                    key={link.page}
                    href={link.url}
                    aria-current={link.active ? 'page' : undefined}
                    aria-label={ui.catalog.page.replace(':page', String(link.page))}
                    className={cn(
                        'stamp grid size-10 place-items-center rounded-full text-sm transition',
                        link.active ? 'bg-navy text-white' : 'border border-steel-200 bg-white text-navy hover:border-blue',
                    )}
                >
                    {link.page}
                </Link>
            ))}
            {page.next && (
                <Link href={page.next} rel="next" className="grid size-10 place-items-center rounded-full border border-steel-200 bg-white text-navy transition hover:border-blue">
                    <Next aria-hidden className="size-4" />
                </Link>
            )}
        </nav>
    );
}
