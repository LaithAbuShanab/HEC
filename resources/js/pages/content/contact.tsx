import { Link } from '@inertiajs/react';
import { Clock, Mail, MapPin, MessageCircle, Phone } from 'lucide-react';
import type { ReactNode } from 'react';
import { PageHeader } from '@/components/site/page-header';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { CompanyProfile, SeoProps } from '@/types';

export default function Contact({ profile, seo }: { profile: CompanyProfile; seo: SeoProps }) {
    const { locale, ui } = useSite();
    const hasChannels = Boolean(profile.phone || profile.whatsapp || profile.email || profile.address);

    return (
        <SiteLayout seo={seo}>
            <PageHeader title={ui.nav.contact} lead={ui.content.contact_lead} crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: ui.nav.contact }]} />
            <section className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.2fr_1fr] lg:px-8">
                <div>
                    <h2 className="mb-6 font-display text-2xl font-bold">{ui.content.contact_channels}</h2>
                    {hasChannels ? (
                        <ul className="grid gap-4 sm:grid-cols-2">
                            {profile.phone && (
                                <Channel icon={<Phone className="size-5" />} label={ui.actions.call} href={profile.phone.href} analytics="phone_click">
                                    <bdi dir="ltr">{profile.phone.display}</bdi>
                                </Channel>
                            )}
                            {profile.whatsapp && (
                                <Channel icon={<MessageCircle className="size-5" />} label={ui.actions.whatsapp} href={profile.whatsapp.href} analytics="whatsapp_click" external>
                                    <bdi dir="ltr">{profile.whatsapp.display}</bdi>
                                </Channel>
                            )}
                            {profile.email && (
                                <Channel icon={<Mail className="size-5" />} label={ui.actions.email} href={`mailto:${profile.email}`}>
                                    <bdi dir="ltr">{profile.email}</bdi>
                                </Channel>
                            )}
                            {profile.address && (
                                <Channel icon={<MapPin className="size-5" />} label={ui.actions.directions} href={profile.mapsUrl ?? undefined} external>
                                    {profile.address}
                                </Channel>
                            )}
                        </ul>
                    ) : (
                        <p className="rounded-2xl border border-dashed border-steel-300 bg-white p-6 text-steel-600">{ui.content.no_contact}</p>
                    )}
                    {profile.hours && (
                        <div className="mt-8 rounded-2xl border border-steel-200 bg-white p-6">
                            <h3 className="mb-4 flex items-center gap-2 font-semibold">
                                <Clock aria-hidden className="size-5 text-blue" />
                                {ui.footer.hours}
                            </h3>
                            <ul className="space-y-2 text-steel-600">
                                {profile.hours.map((slot) => (
                                    <li key={`${slot.days.join('-')}-${slot.opens}`} className="flex flex-wrap justify-between gap-2">
                                        <span>{slot.days.map((day) => ui.weekdays[day]).join('، ')}</span>
                                        <bdi dir="ltr" className="stamp">
                                            {slot.opens} – {slot.closes}
                                        </bdi>
                                    </li>
                                ))}
                            </ul>
                            {profile.hoursNote && <p className="mt-3 text-sm text-steel-500">{profile.hoursNote}</p>}
                        </div>
                    )}
                </div>
                <aside className="steel-texture relative overflow-hidden rounded-3xl p-8 text-white">
                    <div aria-hidden className="blueprint-grid absolute inset-0 opacity-50" />
                    <div className="relative">
                        <h2 className="font-display text-2xl font-bold">{ui.content.quote_cta}</h2>
                        <p className="mt-3 leading-7 text-steel-300">{ui.content.quote_cta_body}</p>
                        <Link href={localized(locale, 'request-quote')} className="mt-8 inline-flex rounded-full bg-gold px-6 py-3 font-semibold text-navy shadow-[0_0_28px_rgb(228_180_42/0.35)]">
                            {ui.actions.request_quote}
                        </Link>
                        {profile.serviceAreas && <p className="mt-8 border-t border-white/10 pt-6 text-sm text-steel-300">{profile.serviceAreas}</p>}
                    </div>
                </aside>
            </section>
        </SiteLayout>
    );
}

function Channel({ icon, label, href, children, analytics, external = false }: { icon: ReactNode; label: string; href?: string; children: ReactNode; analytics?: string; external?: boolean }) {
    const body = (
        <>
            <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-blue/10 text-blue">{icon}</span>
            <span>
                <span className="block text-xs text-steel-500">{label}</span>
                <span className="block font-semibold text-navy">{children}</span>
            </span>
        </>
    );

    return (
        <li>
            {href ? (
                <a href={href} data-analytics={analytics} {...(external ? { target: '_blank', rel: 'noopener' } : {})} className="flex items-center gap-4 rounded-2xl border border-steel-200 bg-white p-5 transition hover:border-blue/40 hover:shadow-md">
                    {body}
                </a>
            ) : (
                <div className="flex items-center gap-4 rounded-2xl border border-steel-200 bg-white p-5">{body}</div>
            )}
        </li>
    );
}
