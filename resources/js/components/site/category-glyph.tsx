import { cn } from '@/lib/utils';

const GLYPHS: Record<string, string[]> = {
    'sheets-coils': ['M8 44 L36 30 L58 40 L30 54 Z', 'M8 36 L36 22 L58 32', 'M8 28 L36 14 L58 24', 'M44 46 m-8 0 a8 8 0 1 0 16 0 a8 8 0 1 0 -16 0', 'M44 46 m-3 0 a3 3 0 1 0 6 0 a3 3 0 1 0 -6 0'],
    'sections-bars': ['M14 12 H50', 'M14 52 H50', 'M32 12 V52', 'M14 12 V18 H28', 'M50 12 V18 H36', 'M14 52 V46 H28', 'M50 52 V46 H36'],
    'pipes-tubes': ['M24 32 m-14 0 a14 14 0 1 0 28 0 a14 14 0 1 0 -28 0', 'M24 32 m-9 0 a9 9 0 1 0 18 0 a9 9 0 1 0 -18 0', 'M42 20 H58 V44 H42 Z', 'M46 24 H54 V40 H46 Z'],
    'wire-welding': ['M12 40 c0-10 20-10 20 0 s20 10 20 0', 'M12 32 c0-10 20-10 20 0 s20 10 20 0', 'M12 24 c0-10 20-10 20 0 s20 10 20 0', 'M44 50 L58 10'],
    'metal-forming': ['M8 48 H30 L52 18', 'M8 54 H33 L56 21', 'M28 12 L40 24', 'M22 18 L34 30'],
    'industrial-equipment': ['M32 32 m-10 0 a10 10 0 1 0 20 0 a10 10 0 1 0 -20 0', 'M32 32 m-4 0 a4 4 0 1 0 8 0 a4 4 0 1 0 -8 0', 'M32 10 V18', 'M32 46 V54', 'M10 32 H18', 'M46 32 H54', 'M16 16 L22 22', 'M42 42 L48 48', 'M48 16 L42 22', 'M22 42 L16 48'],
    'lifting-pulling': ['M32 6 V24', 'M26 6 H38', 'M32 24 c0 0 12 2 12 14 a12 12 0 0 1 -24 0', 'M20 38 L26 34', 'M28 12 h8 v6 h-8 z'],
    'grinding-cutting': ['M30 34 m-20 0 a20 20 0 1 0 40 0 a20 20 0 1 0 -40 0', 'M30 34 m-5 0 a5 5 0 1 0 10 0 a5 5 0 1 0 -10 0', 'M48 14 L58 8', 'M50 20 L60 18', 'M46 10 L50 2'],
    'fasteners-locks': ['M20 10 H44 V18 H20 Z', 'M28 18 V54', 'M36 18 V48 L32 54 L28 48', 'M28 24 L36 28', 'M28 32 L36 36', 'M28 40 L36 44'],
};

export function CategoryGlyph({ slug, className }: { slug: string; className?: string }) {
    const paths = GLYPHS[slug] ?? GLYPHS['sections-bars'];

    return (
        <svg viewBox="0 0 64 64" fill="none" aria-hidden className={cn('category-glyph', className)}>
            {paths.map((d, index) => (
                <path key={d} d={d} pathLength={1} style={{ transitionDelay: `${index * 60}ms` }} />
            ))}
        </svg>
    );
}
