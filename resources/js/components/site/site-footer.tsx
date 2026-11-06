import { Link } from '@inertiajs/react';
import { Clock, Mail, MapPin, Phone } from 'lucide-react';
import { useSite } from '@/hooks/use-site';
import { localized } from '@/lib/site';

export function SiteFooter() {
    const { locale, nav, ui, company } = useSite();
    const year = new Date().getFullYear();

    const explore = [
        { href: localized(locale, 'products'), label: ui.nav.products, visible: true },
        { href: localized(locale, 'services'), label: ui.nav.services, visible: nav.sections.services },
        { href: localized(locale, 'industries'), label: ui.nav.industries, visible: nav.sections.industries },
        { href: localized(locale, 'guides'), label: ui.nav.guides, visible: nav.sections.guides },
        { href: localized(locale, 'work'), label: ui.nav.work, visible: nav.sections.work },
        { href: localized(locale, 'faq'), label: ui.nav.faq, visible: nav.sections.faq },
    ].filter((link) => link.visible);

    const companyLinks = [
        { href: localized(locale, 'about'), label: ui.nav.about, visible: nav.sections.about },
        { href: localized(locale, 'contact'), label: ui.nav.contact, visible: true },
        { href: localized(locale, 'request-quote'), label: ui.actions.request_quote, visible: true },
        { href: localized(locale, 'privacy'), label: ui.nav.privacy, visible: true },
        { href: localized(locale, 'terms'), label: ui.nav.terms, visible: true },
    ].filter((link) => link.visible);

    return (
        <footer className="steel-texture relative overflow-hidden text-steel-200">
            <div aria-hidden className="blueprint-grid pointer-events-none absolute inset-0 opacity-40" />
            <div className="relative mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr_1.2fr] lg:px-8">
                <div className="space-y-5">
                    <img src="/images/brand/logo-horizontal-on-dark.webp" alt={company.name} width={450} height={160} className="h-10 w-auto" loading="lazy" />
                    <p className="max-w-xs text-sm leading-7 text-steel-300">{ui.footer.about}</p>
                </div>

                <FooterColumn title={ui.footer.explore} links={explore} />
                <FooterColumn title={ui.footer.company} links={companyLinks} />

                <div>
                    <h2 className="mb-4 text-sm font-semibold text-white">{ui.footer.contact}</h2>
                    <ul className="space-y-3 text-sm">
                        {company.phone && (
                            <li>
                                <a href={company.phone.href} className="flex items-center gap-3 transition hover:text-white">
                                    <Phone aria-hidden className="size-4 text-sky" />
                                    <bdi dir="ltr">{company.phone.display}</bdi>
                                </a>
                            </li>
                        )}
                        {company.email && (
                            <li>
                                <a href={`mailto:${company.email}`} className="flex items-center gap-3 transition hover:text-white">
                                    <Mail aria-hidden className="size-4 text-sky" />
                                    <bdi dir="ltr">{company.email}</bdi>
                                </a>
                            </li>
                        )}
                        {company.address && (
                            <li className="flex items-start gap-3">
                                <MapPin aria-hidden className="mt-0.5 size-4 text-sky" />
                                {company.mapsUrl ? (
                                    <a href={company.mapsUrl} target="_blank" rel="noopener" className="transition hover:text-white">
                                        {company.address}
                                    </a>
                                ) : (
                                    <span>{company.address}</span>
                                )}
                            </li>
                        )}
                        {company.hours && (
                            <li className="flex items-start gap-3">
                                <Clock aria-hidden className="mt-0.5 size-4 text-sky" />
                                <span className="space-y-1">
                                    {company.hours.map((slot) => (
                                        <span key={`${slot.days.join('-')}-${slot.opens}`} className="block">
                                            {slot.days.map((day) => ui.weekdays[day]).join('، ')}{' '}
                                            <bdi dir="ltr" className="stamp">
                                                {slot.opens}–{slot.closes}
                                            </bdi>
                                        </span>
                                    ))}
                                </span>
                            </li>
                        )}
                    </ul>
                    {Object.keys(company.social).length > 0 && (
                        <div className="mt-6 flex gap-2">
                            {Object.entries(company.social).map(([network, url]) => (
                                <a
                                    key={network}
                                    href={url}
                                    target="_blank"
                                    rel="noopener me"
                                    className="rounded-full border border-white/15 px-3 py-1.5 text-xs capitalize transition hover:border-gold/60 hover:text-white"
                                >
                                    {network}
                                </a>
                            ))}
                        </div>
                    )}
                </div>
            </div>
            <div className="relative border-t border-white/10">
                <p className="mx-auto max-w-7xl px-4 py-6 text-xs text-steel-400 sm:px-6 lg:px-8">
                    © <span className="stamp">{year}</span> {company.legalName ?? company.name}. {ui.footer.rights}
                </p>
            </div>
        </footer>
    );
}

function FooterColumn({ title, links }: { title: string; links: { href: string; label: string }[] }) {
    return (
        <div>
            <h2 className="mb-4 text-sm font-semibold text-white">{title}</h2>
            <ul className="space-y-3 text-sm">
                {links.map((link) => (
                    <li key={link.href}>
                        <Link href={link.href} className="transition hover:text-white">
                            {link.label}
                        </Link>
                    </li>
                ))}
            </ul>
        </div>
    );
}
