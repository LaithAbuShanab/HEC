import { Head, Link } from '@inertiajs/react';

export default function LanguageSelect() {
    return (
        <>
            <Head title="HEC" />
            <main className="flex min-h-screen flex-col items-center justify-center gap-6">
                <Link href="/ar" hrefLang="ar" lang="ar">
                    العربية
                </Link>
                <Link href="/en" hrefLang="en" lang="en">
                    English
                </Link>
            </main>
        </>
    );
}
