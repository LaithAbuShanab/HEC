import { router } from '@inertiajs/react';
import { SlidersHorizontal, X } from 'lucide-react';
import { useState } from 'react';
import { useSite } from '@/hooks/use-site';
import { cn } from '@/lib/utils';

export type Facet = {
    key: string;
    label: string;
    options: { value: string; label: string; count: number; selected: boolean }[];
};

export function FilterPanel({ facets, baseUrl, filtered }: { facets: Facet[]; baseUrl: string; filtered: boolean }) {
    const { ui } = useSite();
    const [open, setOpen] = useState(false);

    if (facets.length === 0) {
        return null;
    }

    const toggle = (key: string, value: string) => {
        const params = new URLSearchParams(window.location.search);
        const current = (params.get(key) ?? '').split(',').filter(Boolean);
        const next = current.includes(value) ? current.filter((item) => item !== value) : [...current, value];

        if (next.length === 0) {
            params.delete(key);
        } else {
            params.set(key, next.join(','));
        }

        params.delete('page');
        router.get(`${baseUrl}${params.size ? `?${params.toString()}` : ''}`, {}, { preserveScroll: true, preserveState: true, replace: true });
    };

    const panel = (
        <div className="space-y-6">
            {facets.map((facet) => (
                <fieldset key={facet.key}>
                    <legend className="mb-3 text-sm font-semibold text-navy">{facet.label}</legend>
                    <div className="flex flex-wrap gap-2">
                        {facet.options.map((option) => (
                            <label
                                key={option.value}
                                className={cn(
                                    'flex cursor-pointer items-center gap-2 rounded-full border px-3 py-1.5 text-sm transition has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-sky',
                                    option.selected ? 'border-navy bg-navy text-white' : 'border-steel-200 bg-white text-steel-600 hover:border-blue/50',
                                )}
                            >
                                <input type="checkbox" className="sr-only" checked={option.selected} onChange={() => toggle(facet.key, option.value)} />
                                <bdi>{option.label}</bdi>
                                <span className={cn('stamp rounded-full px-1.5 py-0.5 text-[10px] leading-none', option.selected ? 'bg-white/15 text-white' : 'bg-steel-100 text-steel-500')}>
                                    {option.count}
                                </span>
                            </label>
                        ))}
                    </div>
                </fieldset>
            ))}
            {filtered && (
                <button
                    type="button"
                    onClick={() => router.get(baseUrl, {}, { preserveScroll: true })}
                    className="inline-flex items-center gap-1.5 text-sm font-semibold text-blue hover:underline"
                >
                    <X aria-hidden className="size-4" />
                    {ui.filters.clear}
                </button>
            )}
        </div>
    );

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-expanded={open}
                className="mb-6 inline-flex items-center gap-2 rounded-full border border-steel-200 bg-white px-4 py-2 text-sm font-semibold text-navy lg:hidden"
            >
                <SlidersHorizontal aria-hidden className="size-4" />
                {ui.filters.title}
            </button>
            <aside aria-label={ui.filters.title} className={cn('lg:block', open ? 'mb-8 block' : 'hidden')}>
                <h2 className="mb-5 hidden items-center gap-2 text-sm font-semibold tracking-normal text-steel-500 lg:flex">
                    <SlidersHorizontal aria-hidden className="size-4" />
                    {ui.filters.title}
                </h2>
                {panel}
            </aside>
        </>
    );
}
