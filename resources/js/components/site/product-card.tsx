import { Link } from '@inertiajs/react';
import { useSite } from '@/hooks/use-site';
import { localized } from '@/lib/site';
import type { ProductCardData } from '@/types';
import { AvailabilityBadge } from './availability-badge';
import { CategoryGlyph } from './category-glyph';
import { ResponsiveImage } from './responsive-image';

export function ProductCard({ product }: { product: ProductCardData }) {
    const { locale } = useSite();

    return (
        <Link
            href={localized(locale, `products/${product.category}/${product.slug}`)}
            className="group flex h-full flex-col overflow-hidden rounded-2xl border border-steel-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-blue/30 hover:shadow-[0_24px_50px_-24px_rgb(14_27_48/0.45)]"
        >
            <div className="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-steel-100 to-steel-200">
                {product.image ? (
                    <ResponsiveImage
                        image={product.image}
                        alt={product.image.alt || product.name}
                        sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw"
                        className="transition duration-700 group-hover:scale-105"
                    />
                ) : (
                    <div className="grid size-full place-items-center">
                        <CategoryGlyph slug={product.category ?? ''} className="size-20 text-steel-400 transition group-hover:text-blue" />
                    </div>
                )}
                <span aria-hidden className="brushed-sheen pointer-events-none absolute inset-0 opacity-0 transition duration-500 group-hover:animate-sheen group-hover:opacity-100" />
            </div>
            <div className="flex flex-1 flex-col gap-3 p-5">
                {product.categoryName && <span className="text-xs text-steel-500">{product.categoryName}</span>}
                <h3 className="text-base leading-6 font-semibold text-navy">{product.name}</h3>
                {product.summary && <p className="line-clamp-2 text-sm leading-6 text-steel-500">{product.summary}</p>}
                <div className="mt-auto flex flex-wrap items-center gap-2 pt-2">
                    <AvailabilityBadge value={product.availability.value} label={product.availability.label} />
                    {product.condition && <span className="text-xs text-steel-500">{product.condition}</span>}
                </div>
            </div>
        </Link>
    );
}
