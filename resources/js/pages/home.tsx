import { Head, Link, usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types';

export default function Home() {
    const { locale, alternates } = usePage<SharedProps>().props;
    const other = locale === 'ar' ? 'en' : 'ar';

    return (
        <>
            <Head title="HEC" />
            <main className="flex min-h-screen items-center justify-center">
                <Link href={alternates[other]} hrefLang={other}>
                    {other === 'ar' ? 'العربية' : 'English'}
                </Link>
            </main>
        </>
    );
}
