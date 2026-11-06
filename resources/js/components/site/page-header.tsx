import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { useSite } from '@/hooks/use-site';
import { LaserText } from './laser-text';

export type Crumb = { label: string; href?: string };

type PageHeaderProps = {
    title: string;
    lead?: string | null;
    crumbs: Crumb[];
    children?: ReactNode;
    aside?: ReactNode;
};

export function PageHeader({ title, lead, crumbs, children, aside }: PageHeaderProps) {
    const { locale } = useSite();
    const Separator = locale === 'ar' ? ChevronLeft : ChevronRight;

    return (
        <section className="steel-texture relative isolate overflow-hidden pt-(--header-height) text-white">
            <div aria-hidden className="blueprint-grid absolute inset-0 -z-10 opacity-60 [mask-image:linear-gradient(to_bottom,#000,transparent)]" />
            <div className="mx-auto max-w-7xl px-4 pt-10 pb-12 sm:px-6 lg:px-8">
                <nav aria-label="breadcrumb">
                    <ol className="flex flex-wrap items-center gap-1.5 text-xs text-steel-400">
                        {crumbs.map((crumb, index) => (
                            <li key={`${crumb.label}-${index}`} className="flex items-center gap-1.5">
                                {crumb.href ? (
                                    <Link href={crumb.href} className="transition hover:text-white">
                                        {crumb.label}
                                    </Link>
                                ) : (
                                    <span aria-current="page" className="text-steel-200">
                                        {crumb.label}
                                    </span>
                                )}
                                {index < crumbs.length - 1 && <Separator aria-hidden className="size-3.5" />}
                            </li>
                        ))}
                    </ol>
                </nav>
                <div className="mt-6 flex flex-wrap items-end justify-between gap-6">
                    <div className="max-w-3xl">
                        <span aria-hidden className="mb-4 block h-1 w-12 rounded-full bg-gold" />
                        <h1 className="font-display text-3xl leading-tight font-bold text-balance sm:text-5xl">
                            <LaserText>{title}</LaserText>
                        </h1>
                        {lead && <p className="mt-4 text-lg leading-8 text-steel-300">{lead}</p>}
                    </div>
                    {aside}
                </div>
                {children}
            </div>
            <div aria-hidden className="h-px bg-gradient-to-r from-transparent via-gold/50 to-transparent" />
        </section>
    );
}
