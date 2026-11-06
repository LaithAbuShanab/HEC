import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { useSite } from '@/hooks/use-site';
import { localized } from '@/lib/site';

export function HeroSearch() {
    const { locale, ui } = useSite();
    const [query, setQuery] = useState('');

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (query.trim() !== '') {
            router.get(localized(locale, 'search'), { q: query.trim() });
        }
    };

    return (
        <div className="w-full max-w-xl">
            <form role="search" onSubmit={submit} action={localized(locale, 'search')} className="group relative">
                <label htmlFor="hero-search" className="sr-only">
                    {ui.home.search_label}
                </label>
                <Search aria-hidden className="pointer-events-none absolute start-5 top-1/2 size-5 -translate-y-1/2 text-steel-300 transition group-focus-within:text-sky" />
                <input
                    id="hero-search"
                    name="q"
                    type="search"
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder={ui.actions.search_placeholder}
                    autoComplete="off"
                    className="h-14 w-full rounded-full border border-white/15 bg-white/[0.06] ps-14 pe-32 text-base text-white placeholder:text-steel-400 backdrop-blur-md transition outline-none focus:border-sky/70 focus:bg-white/10 focus:shadow-[0_0_0_4px_rgb(76_163_214/0.18)]"
                />
                <button
                    type="submit"
                    className="absolute end-2 top-1/2 h-10 -translate-y-1/2 rounded-full bg-sky px-5 text-sm font-semibold text-night transition hover:bg-[#6bb7e3]"
                >
                    {ui.actions.search}
                </button>
            </form>
            <div className="mt-4 flex flex-wrap items-center gap-2">
                <span className="text-xs text-steel-400">{ui.home.popular}</span>
                {ui.home.popular_terms.map((term) => (
                    <a
                        key={term}
                        href={`${localized(locale, 'search')}?q=${encodeURIComponent(term)}`}
                        className="rounded-full border border-white/10 px-3 py-1 text-xs text-steel-200 transition hover:border-gold/50 hover:text-white"
                    >
                        {term}
                    </a>
                ))}
            </div>
        </div>
    );
}
