import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, FileUp, Layers, MessageCircle, Ruler, Send, ShoppingBasket } from 'lucide-react';
import { motion } from 'motion/react';
import { useRef } from 'react';
import type { ReactNode } from 'react';
import { CaliperMarks } from '@/components/site/caliper-marks';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { HeroSearch } from '@/components/site/hero-search';
import { LaserText } from '@/components/site/laser-text';
import { LogoReveal } from '@/components/site/logo-reveal';
import { QuoteLinePreview } from '@/components/site/quote-line-preview';
import { SparkField } from '@/components/site/spark-field';
import { SteelCoil } from '@/components/site/steel-coil';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import type { SeoProps } from '@/types';

type CategoryCard = { slug: string; name: string; description: string | null; image: string | null; products: number; products_label: string };
type ProductCard = { slug: string; category: string | null; name: string; summary: string | null; image: string | null; availability: string };
type ServiceCard = { slug: string; title: string; summary: string | null; image: string | null };
type IndustryCard = { slug: string; title: string; summary: string | null };
type WorkCard = { slug: string; title: string; image: string | null };
type FaqItem = { question: string; answer: string };

type HomeProps = {
    seo: SeoProps;
    stats: { categories: number; products: number };
    categories: CategoryCard[];
    featured: ProductCard[];
    services: ServiceCard[];
    industries: IndustryCard[];
    work: WorkCard[];
    faqs: FaqItem[];
};

const reveal = {
    initial: { opacity: 0, y: 24 },
    whileInView: { opacity: 1, y: 0 },
    viewport: { once: true, margin: '-80px' },
    transition: { duration: 0.7, ease: [0.2, 0.8, 0.2, 1] },
} as const;

export default function Home({ stats, categories, featured, services, industries, work, faqs, seo }: HomeProps) {
    const { locale, ui, company } = useSite();
    const hero = useRef<HTMLElement>(null);
    const Arrow = locale === 'ar' ? ArrowLeft : ArrowRight;
    const stepIcons = [ShoppingBasket, Ruler, Send];
    const pointIcons = [Layers, FileUp, MessageCircle];

    return (
        <SiteLayout seo={seo}>

            <section ref={hero} className="steel-texture relative isolate overflow-hidden pt-(--header-height) text-white">
                <div aria-hidden className="blueprint-grid absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_at_center,#000_30%,transparent_75%)]" />
                <SparkField className="absolute inset-0 -z-10 size-full" />
                <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 pt-12 pb-10 sm:px-6 lg:min-h-[calc(100svh-var(--header-height)-7rem)] lg:grid-cols-[1.1fr_1fr] lg:px-8">
                    <div className="max-w-2xl">
                        <div className="mb-8 flex flex-wrap items-center gap-4">
                            <LogoReveal label={company.name} variant="white" className="size-14" />
                            <div>
                                <p className="stamp text-xs text-steel-300 uppercase">{ui.home.eyebrow}</p>
                                {company.address && <p className="mt-1 text-xs text-steel-400">{company.address}</p>}
                            </div>
                            {stats.categories > 0 && (
                                <div className="ms-auto hidden items-center gap-5 border-s border-white/10 ps-5 sm:flex">
                                    <Stat value={stats.categories} label={ui.home.stat_categories} />
                                    {stats.products > 0 && <Stat value={stats.products} label={ui.home.stat_products} />}
                                </div>
                            )}
                        </div>
                        <h1 className="font-display text-4xl leading-[1.15] font-bold text-balance sm:text-5xl lg:text-6xl">
                            <LaserText>{ui.home.title}</LaserText>
                        </h1>
                        <p className="mt-6 max-w-xl animate-rise text-lg leading-8 text-steel-200 [animation-delay:1.2s]">{ui.home.lead}</p>
                        <div className="mt-8 animate-rise [animation-delay:1.35s]">
                            <HeroSearch />
                        </div>
                        <div className="mt-8 flex animate-rise flex-wrap gap-3 [animation-delay:1.5s]">
                            <Link
                                href={localized(locale, 'products')}
                                className="group inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 font-semibold text-navy transition hover:bg-steel-100"
                            >
                                {ui.actions.browse_products}
                                <Arrow aria-hidden className="size-4 transition group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                            </Link>
                            <Link
                                href={localized(locale, 'request-quote')}
                                className="inline-flex items-center gap-2 rounded-full bg-gold px-6 py-3 font-semibold text-navy shadow-[0_0_32px_rgb(228_180_42/0.35)] transition hover:bg-[#f0c64a]"
                            >
                                {ui.actions.request_quote}
                            </Link>
                        </div>
                        <p className="mt-5 text-xs text-steel-400">{ui.home.quote_note}</p>
                    </div>
                    <div className="relative mx-auto w-full max-w-md lg:max-w-none">
                        <CaliperMarks className="pointer-events-none absolute inset-0 size-full max-w-[34rem] rtl:-scale-x-100" />
                        <SteelCoil target={hero} />
                        <QuoteLinePreview className="absolute end-0 top-[6%] hidden animate-rise [animation-delay:1.8s] sm:block" />
                    </div>
                </div>
                <div className="border-t border-white/10 bg-night/40 backdrop-blur-sm">
                    <ul className="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:grid-cols-3 sm:px-6 lg:px-8">
                        {ui.home.points.map((point, index) => {
                            const Icon = pointIcons[index] ?? Layers;

                            if (index === 2 && !company.whatsapp && !company.phone) {
                                return null;
                            }

                            return (
                                <li key={point.title} className="flex items-start gap-3">
                                    <span className="grid size-10 shrink-0 place-items-center rounded-lg bg-white/5 ring-1 ring-white/10">
                                        <Icon aria-hidden className="size-5 text-gold" />
                                    </span>
                                    <span>
                                        <span className="block text-sm font-semibold">{point.title}</span>
                                        <span className="block text-xs leading-5 text-steel-400">{point.body}</span>
                                    </span>
                                </li>
                            );
                        })}
                    </ul>
                </div>
                <a
                    href="#how"
                    className="absolute start-1/2 bottom-28 hidden -translate-x-1/2 flex-col items-center gap-2 text-[11px] text-steel-400 transition hover:text-white lg:flex rtl:translate-x-1/2"
                >
                    {ui.home.scroll}
                    <span aria-hidden className="flex h-9 w-5 justify-center rounded-full border border-white/25 pt-1.5">
                        <span className="h-2 w-0.5 animate-bounce rounded-full bg-gold" />
                    </span>
                </a>
            </section>

            <Section id="how" title={ui.home.how_title} tone="light">
                <ol className="grid gap-4 md:grid-cols-3">
                    {ui.home.how_steps.map((step, index) => {
                        const Icon = stepIcons[index] ?? Send;

                        return (
                            <motion.li
                                key={step.title}
                                {...reveal}
                                transition={{ ...reveal.transition, delay: index * 0.12 }}
                                className="relative overflow-hidden rounded-2xl border border-steel-200 bg-white p-6 shadow-sm"
                            >
                                <span aria-hidden className="stamp absolute end-5 top-4 text-5xl font-bold text-steel-100">
                                    0{index + 1}
                                </span>
                                <Icon aria-hidden className="size-7 text-blue" />
                                <h3 className="mt-5 text-lg font-semibold">{step.title}</h3>
                                <p className="mt-2 leading-7 text-steel-500">{step.body}</p>
                                <span aria-hidden className="absolute inset-x-6 bottom-0 h-0.5 bg-[repeating-linear-gradient(90deg,var(--color-sky)_0_8px,transparent_8px_14px)] opacity-60" />
                            </motion.li>
                        );
                    })}
                </ol>
            </Section>

            {categories.length > 0 && (
                <Section title={ui.home.categories_title} lead={ui.home.categories_lead} tone="steel">
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {categories.map((category, index) => (
                            <motion.li key={category.slug} {...reveal} transition={{ ...reveal.transition, delay: (index % 3) * 0.08 }}>
                                <Link
                                    href={localized(locale, `products/${category.slug}`)}
                                    className="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-ink p-6 text-white ring-1 ring-white/10 transition duration-500 [transform-style:preserve-3d] hover:[transform:perspective(900px)_rotateX(6deg)] motion-reduce:hover:transform-none"
                                >
                                    {category.image && (
                                        <img src={category.image} alt="" loading="lazy" className="absolute inset-0 -z-0 size-full object-cover opacity-30 transition duration-700 group-hover:scale-105 group-hover:opacity-40" />
                                    )}
                                    <span aria-hidden className="absolute inset-x-0 top-1/2 h-px bg-white/0 transition duration-500 group-hover:bg-white/25" />
                                    <div className="relative flex items-start justify-between">
                                        <CategoryGlyph slug={category.slug} className="size-16 text-sky transition duration-500 group-hover:text-gold" />
                                        <span className="stamp text-xs text-gold">{category.products_label}</span>
                                    </div>
                                    <h3 className="relative mt-auto pt-10 text-xl font-semibold">{category.name}</h3>
                                    {category.description && <p className="relative mt-2 line-clamp-2 text-sm leading-6 text-steel-300">{category.description}</p>}
                                </Link>
                            </motion.li>
                        ))}
                    </ul>
                </Section>
            )}

            {featured.length > 0 && (
                <Section title={ui.home.featured_title} tone="light" action={{ href: localized(locale, 'products'), label: ui.actions.view_all }}>
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {featured.map((product) => (
                            <motion.li key={product.slug} {...reveal}>
                                <Link
                                    href={localized(locale, `products/${product.category}/${product.slug}`)}
                                    className="group block overflow-hidden rounded-2xl border border-steel-200 bg-white transition hover:-translate-y-1 hover:shadow-xl"
                                >
                                    <div className="aspect-[4/3] bg-steel-100">
                                        {product.image && <img src={product.image} alt="" loading="lazy" className="size-full object-cover" />}
                                    </div>
                                    <div className="p-5">
                                        <span className="text-xs font-medium text-blue">{product.availability}</span>
                                        <h3 className="mt-1 font-semibold">{product.name}</h3>
                                        {product.summary && <p className="mt-2 line-clamp-2 text-sm text-steel-500">{product.summary}</p>}
                                    </div>
                                </Link>
                            </motion.li>
                        ))}
                    </ul>
                </Section>
            )}

            {services.length > 0 && (
                <Section title={ui.home.services_title} tone="steel" action={{ href: localized(locale, 'services'), label: ui.actions.view_all }}>
                    <ul className="grid gap-4 md:grid-cols-3">
                        {services.map((service) => (
                            <motion.li key={service.slug} {...reveal}>
                                <Link href={localized(locale, `services/${service.slug}`)} className="group block h-full rounded-2xl bg-ink p-6 text-white ring-1 ring-white/10 transition hover:ring-sky/50">
                                    <h3 className="text-lg font-semibold">{service.title}</h3>
                                    {service.summary && <p className="mt-2 text-sm leading-6 text-steel-300">{service.summary}</p>}
                                </Link>
                            </motion.li>
                        ))}
                    </ul>
                </Section>
            )}

            {industries.length > 0 && (
                <Section title={ui.home.industries_title} tone="light">
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {industries.map((industry) => (
                            <motion.li key={industry.slug} {...reveal} className="rounded-2xl border border-steel-200 bg-white p-6">
                                <h3 className="font-semibold">{industry.title}</h3>
                                {industry.summary && <p className="mt-2 text-sm leading-6 text-steel-500">{industry.summary}</p>}
                            </motion.li>
                        ))}
                    </ul>
                </Section>
            )}

            {work.length > 0 && (
                <Section title={ui.home.work_title} tone="steel" action={{ href: localized(locale, 'work'), label: ui.actions.view_all }}>
                    <ul className="grid gap-4 md:grid-cols-3">
                        {work.map((item) => (
                            <motion.li key={item.slug} {...reveal} className="overflow-hidden rounded-2xl bg-ink ring-1 ring-white/10">
                                {item.image && <img src={item.image} alt="" loading="lazy" className="aspect-[4/3] w-full object-cover" />}
                                <h3 className="p-5 font-semibold text-white">{item.title}</h3>
                            </motion.li>
                        ))}
                    </ul>
                </Section>
            )}

            {faqs.length > 0 && (
                <Section title={ui.home.faq_title} tone="light" action={{ href: localized(locale, 'faq'), label: ui.actions.view_all }}>
                    <div className="divide-y divide-steel-200 rounded-2xl border border-steel-200 bg-white">
                        {faqs.map((faq) => (
                            <details key={faq.question} className="group p-6">
                                <summary className="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold">
                                    {faq.question}
                                    <span aria-hidden className="text-xl text-blue transition group-open:rotate-45">+</span>
                                </summary>
                                <p className="mt-3 leading-7 text-steel-500">{faq.answer}</p>
                            </details>
                        ))}
                    </div>
                </Section>
            )}

            <section className="relative overflow-hidden bg-gold text-navy">
                <div aria-hidden className="absolute inset-0 bg-[repeating-linear-gradient(135deg,rgb(14_27_48/0.06)_0_2px,transparent_2px_14px)]" />
                <div className="relative mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-4 py-14 sm:px-6 md:flex-row md:items-center lg:px-8">
                    <div>
                        <h2 className="font-display text-2xl font-bold sm:text-3xl">{ui.home.cta_title}</h2>
                        <p className="mt-2 max-w-xl text-navy/80">{ui.home.cta_body}</p>
                    </div>
                    <Link href={localized(locale, 'request-quote')} className="rounded-full bg-navy px-7 py-3.5 font-semibold text-white transition hover:bg-ink">
                        {ui.actions.request_quote}
                    </Link>
                </div>
            </section>
        </SiteLayout>
    );
}

type SectionProps = {
    id?: string;
    title: string;
    lead?: string;
    tone: 'light' | 'steel';
    action?: { href: string; label: string };
    children: ReactNode;
};

function Section({ id, title, lead, tone, action, children }: SectionProps) {
    return (
        <section id={id} className={tone === 'steel' ? 'steel-texture text-white' : 'bg-steel-50'}>
            <div className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <motion.div {...reveal} className="mb-10 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <span aria-hidden className="mb-4 block h-1 w-12 rounded-full bg-gold" />
                        <h2 className="font-display text-3xl font-bold sm:text-4xl">{title}</h2>
                        {lead && <p className={tone === 'steel' ? 'mt-3 text-steel-300' : 'mt-3 text-steel-500'}>{lead}</p>}
                    </div>
                    {action && (
                        <Link href={action.href} className={tone === 'steel' ? 'text-sm font-semibold text-gold' : 'text-sm font-semibold text-blue'}>
                            {action.label}
                        </Link>
                    )}
                </motion.div>
                {children}
            </div>
        </section>
    );
}

function Stat({ value, label }: { value: number; label: string }) {
    return (
        <div>
            <span className="stamp block text-2xl font-bold text-white">{value}</span>
            <span className="block text-[11px] text-steel-400">{label}</span>
        </div>
    );
}
