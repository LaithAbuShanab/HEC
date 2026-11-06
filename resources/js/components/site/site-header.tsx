import { Link } from '@inertiajs/react';
import { ChevronDown, ClipboardList, Menu, Search, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useSite } from '@/hooks/use-site';
import { localized, otherLocale } from '@/lib/site';
import { cn } from '@/lib/utils';

export function SiteHeader() {
    const { locale, alternates, nav, ui, quote } = useSite();
    const [scrolled, setScrolled] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);
    const target = otherLocale(locale);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 24);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        document.documentElement.style.overflow = menuOpen ? 'hidden' : '';
    }, [menuOpen]);

    const links = [
        nav.sections.services && { href: localized(locale, 'services'), label: ui.nav.services },
        nav.sections.industries && { href: localized(locale, 'industries'), label: ui.nav.industries },
        nav.sections.guides && { href: localized(locale, 'guides'), label: ui.nav.guides },
        nav.sections.about && { href: localized(locale, 'about'), label: ui.nav.about },
        { href: localized(locale, 'contact'), label: ui.nav.contact },
    ].filter((link): link is { href: string; label: string } => Boolean(link));

    return (
        <header
            className={cn(
                'fixed inset-x-0 top-0 z-50 h-(--header-height) text-white transition-[background-color,box-shadow,backdrop-filter] duration-500',
                scrolled || menuOpen ? 'bg-night/85 shadow-[0_1px_0_rgb(255_255_255/0.06)] backdrop-blur-xl' : 'bg-transparent',
            )}
        >
            <div className="mx-auto flex h-full max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
                <Link href={localized(locale)} className="flex shrink-0 items-center" aria-label={ui.brand}>
                    <img
                        src="/images/brand/logo-horizontal-on-dark.webp"
                        alt={ui.brand}
                        width={450}
                        height={160}
                        className="h-9 w-auto"
                    />
                </Link>

                <nav aria-label={ui.nav.main} className="hidden flex-1 items-center gap-1 lg:flex">
                    <div className="group relative">
                        <Link
                            href={localized(locale, 'products')}
                            className="flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-white/85 transition hover:text-white"
                        >
                            {ui.nav.products}
                            {nav.categories.length > 0 && <ChevronDown aria-hidden className="size-4 transition group-hover:rotate-180" />}
                        </Link>
                        {nav.categories.length > 0 && (
                            <div className="invisible absolute start-0 top-full w-[34rem] translate-y-2 pt-3 opacity-0 transition duration-300 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                                <div className="steel-texture grid grid-cols-2 gap-1 rounded-xl p-3 shadow-2xl ring-1 ring-white/10">
                                    {nav.categories.map((category) => (
                                        <Link
                                            key={category.slug}
                                            href={localized(locale, `products/${category.slug}`)}
                                            className="group/item rounded-lg px-3 py-2.5 transition hover:bg-white/5"
                                        >
                                            <span className="block text-sm font-medium text-white">{category.name}</span>
                                            <span className="stamp block text-xs text-steel-300">{category.products}</span>
                                        </Link>
                                    ))}
                                    <Link
                                        href={localized(locale, 'products')}
                                        className="col-span-2 mt-1 rounded-lg border border-white/10 px-3 py-2 text-center text-sm text-gold transition hover:border-gold/50"
                                    >
                                        {ui.nav.all_products}
                                    </Link>
                                </div>
                            </div>
                        )}
                    </div>
                    {links.map((link) => (
                        <Link
                            key={link.href}
                            href={link.href}
                            className="rounded-md px-3 py-2 text-sm font-medium text-white/85 transition hover:text-white"
                        >
                            {link.label}
                        </Link>
                    ))}
                </nav>

                <div className="ms-auto flex items-center gap-2">
                    <Link
                        href={localized(locale, 'search')}
                        className="grid size-10 place-items-center rounded-full text-white/80 transition hover:bg-white/10 hover:text-white"
                        aria-label={ui.actions.search}
                    >
                        <Search aria-hidden className="size-5" />
                    </Link>
                    <a
                        href={alternates[target]}
                        hrefLang={target}
                        lang={target}
                        className="hidden rounded-full px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white sm:block"
                    >
                        {ui.actions.switch_language}
                    </a>
                    <Link
                        href={localized(locale, 'request-quote')}
                        aria-label={quote.count > 0 ? `${ui.actions.request_quote} — ${ui.quote.list_badge.replace(':count', String(quote.count))}` : undefined}
                        className="relative grid size-10 place-items-center rounded-full text-white/80 transition hover:bg-white/10 hover:text-white sm:hidden"
                    >
                        <ClipboardList aria-hidden className="size-5" />
                        {quote.count > 0 ? <QuoteCount count={quote.count} /> : <span className="sr-only">{ui.actions.request_quote}</span>}
                    </Link>
                    <Link
                        href={localized(locale, 'request-quote')}
                        aria-label={quote.count > 0 ? `${ui.actions.request_quote} — ${ui.quote.list_badge.replace(':count', String(quote.count))}` : undefined}
                        className="relative hidden items-center gap-2 rounded-full bg-gold px-4 py-2 text-sm font-semibold text-navy shadow-[0_0_24px_rgb(228_180_42/0.35)] transition hover:bg-[#f0c64a] sm:inline-flex"
                    >
                        {ui.actions.request_quote}
                        {quote.count > 0 && (
                            <span aria-hidden className="stamp grid min-w-5 place-items-center rounded-full bg-navy px-1.5 text-[11px] leading-5 text-white">
                                {quote.count}
                            </span>
                        )}
                    </Link>
                    <button
                        type="button"
                        onClick={() => setMenuOpen((open) => !open)}
                        aria-expanded={menuOpen}
                        aria-controls="site-mobile-menu"
                        aria-label={menuOpen ? ui.nav.close_menu : ui.nav.menu}
                        className="grid size-10 place-items-center rounded-full text-white transition hover:bg-white/10 lg:hidden"
                    >
                        {menuOpen ? <X aria-hidden className="size-6" /> : <Menu aria-hidden className="size-6" />}
                    </button>
                </div>
            </div>

            <div
                id="site-mobile-menu"
                hidden={!menuOpen}
                className="steel-texture fixed inset-x-0 top-(--header-height) bottom-0 overflow-y-auto px-4 pb-10 lg:hidden"
            >
                <nav aria-label={ui.nav.main} className="flex flex-col gap-1 pt-4">
                    <Link href={localized(locale, 'products')} className="rounded-lg px-3 py-3 text-lg font-semibold" onClick={() => setMenuOpen(false)}>
                        {ui.nav.products}
                    </Link>
                    {nav.categories.map((category) => (
                        <Link
                            key={category.slug}
                            href={localized(locale, `products/${category.slug}`)}
                            className="rounded-lg px-6 py-2 text-steel-200"
                            onClick={() => setMenuOpen(false)}
                        >
                            {category.name}
                        </Link>
                    ))}
                    {links.map((link) => (
                        <Link key={link.href} href={link.href} className="rounded-lg px-3 py-3 text-lg font-semibold" onClick={() => setMenuOpen(false)}>
                            {link.label}
                        </Link>
                    ))}
                    <a href={alternates[target]} hrefLang={target} lang={target} className="rounded-lg px-3 py-3 text-steel-200">
                        {ui.actions.switch_language}
                    </a>
                    <Link
                        href={localized(locale, 'request-quote')}
                        className="mt-4 rounded-full bg-gold px-4 py-3 text-center font-semibold text-navy"
                        onClick={() => setMenuOpen(false)}
                    >
                        {ui.actions.request_quote}
                    </Link>
                </nav>
            </div>
        </header>
    );
}

function QuoteCount({ count }: { count: number }) {
    return (
        <span aria-hidden className="stamp absolute -end-0.5 -top-0.5 grid min-w-4.5 place-items-center rounded-full bg-gold px-1 text-[10px] leading-4.5 font-bold text-navy">
            {count}
        </span>
    );
}
