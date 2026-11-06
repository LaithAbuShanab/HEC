import type { ReactNode } from 'react';
import { FloatingContact } from '@/components/site/floating-contact';
import { SiteFooter } from '@/components/site/site-footer';
import { SiteHeader } from '@/components/site/site-header';
import { Seo } from '@/components/site/seo';
import { useSite } from '@/hooks/use-site';
import type { SeoProps } from '@/types';

export default function SiteLayout({ children, seo }: { children: ReactNode; seo?: SeoProps }) {
    const { ui } = useSite();

    return (
        <div className="flex min-h-dvh flex-col">
            {seo && <Seo seo={seo} />}
            <a
                href="#main"
                className="fixed start-4 top-2 z-[60] -translate-y-20 rounded-md bg-gold px-4 py-2 font-semibold text-navy transition focus:translate-y-0"
            >
                {ui.skip_to_content}
            </a>
            <SiteHeader />
            <main id="main" className="flex-1">
                {children}
            </main>
            <SiteFooter />
            <FloatingContact />
        </div>
    );
}
