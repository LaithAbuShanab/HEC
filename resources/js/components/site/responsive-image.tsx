import type { Image } from '@/types';
import { cn } from '@/lib/utils';

type ResponsiveImageProps = {
    image: Image;
    sizes: string;
    className?: string;
    priority?: boolean;
    alt?: string;
};

export function ResponsiveImage({ image, sizes, className, priority = false, alt }: ResponsiveImageProps) {
    return (
        <img
            src={image.src}
            srcSet={image.srcset ?? undefined}
            sizes={image.srcset ? sizes : undefined}
            alt={alt ?? image.alt}
            loading={priority ? 'eager' : 'lazy'}
            fetchPriority={priority ? 'high' : 'auto'}
            decoding="async"
            className={cn('size-full object-cover', className)}
        />
    );
}
