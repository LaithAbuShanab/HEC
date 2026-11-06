import { Head } from '@inertiajs/react';
import type { SeoProps } from '@/types';

export function Seo({ seo }: { seo: SeoProps }) {
    return (
        <Head title={seo.title}>
            {seo.description && <meta head-key="description" name="description" content={seo.description} />}
            <meta head-key="robots" name="robots" content={seo.robots} />
            <link head-key="canonical" rel="canonical" href={seo.canonical} />
            {Object.entries(seo.alternates).map(([hreflang, href]) => (
                <link key={hreflang} head-key={`alternate-${hreflang}`} rel="alternate" hrefLang={hreflang} href={href} />
            ))}
            <meta head-key="og:type" property="og:type" content={seo.type} />
            <meta head-key="og:title" property="og:title" content={seo.title} />
            {seo.description && <meta head-key="og:description" property="og:description" content={seo.description} />}
            <meta head-key="og:url" property="og:url" content={seo.canonical} />
            <meta head-key="og:image" property="og:image" content={seo.image} />
            <meta head-key="og:locale" property="og:locale" content={seo.locale} />
            <meta head-key="og:site_name" property="og:site_name" content={seo.siteName} />
            <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
            {seo.jsonLd.map((schema, index) => (
                <script
                    key={index}
                    head-key={`jsonld-${index}`}
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: JSON.stringify(schema).replace(/</g, '\\u003c') }}
                />
            ))}
        </Head>
    );
}
