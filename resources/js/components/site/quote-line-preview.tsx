import { useEffect, useState } from 'react';
import { useSite } from '@/hooks/use-site';
import { prefersReducedMotion } from '@/lib/site';

export function QuoteLinePreview({ className }: { className?: string }) {
    const { ui } = useSite();
    const examples = ui.home.line_examples;
    const [index, setIndex] = useState(0);
    const [length, setLength] = useState(examples[0]?.length ?? 0);

    useEffect(() => {
        if (prefersReducedMotion() || examples.length === 0) {
            return;
        }

        const current = examples[index] ?? '';
        let typed = 0;
        setLength(0);

        const typing = window.setInterval(() => {
            typed++;
            setLength(typed);

            if (typed >= current.length) {
                window.clearInterval(typing);
            }
        }, 55);

        const next = window.setTimeout(() => setIndex((value) => (value + 1) % examples.length), current.length * 55 + 2600);

        return () => {
            window.clearInterval(typing);
            window.clearTimeout(next);
        };
    }, [index, examples]);

    const item = (examples[index] ?? '').slice(0, length);

    return (
        <div className={className}>
            <div className="w-64 rounded-xl border border-white/10 bg-night/70 p-4 text-start text-white shadow-[0_20px_50px_-10px_rgb(0_0_0/0.7)] backdrop-blur-md">
                <div className="mb-3 flex items-center justify-between">
                    <span className="text-xs font-semibold text-gold">{ui.home.line_title}</span>
                    <span aria-hidden className="size-2 animate-ember rounded-full bg-ember shadow-[0_0_10px_var(--color-ember)]" />
                </div>
                <dl className="space-y-2 text-sm">
                    <Row label={ui.home.line_item}>
                        <span>{item}</span>
                        <span aria-hidden className="ms-0.5 inline-block h-4 w-px translate-y-0.5 animate-pulse bg-sky" />
                    </Row>
                    <Row label={ui.home.line_thickness}>
                        <span className="text-steel-400">— mm</span>
                    </Row>
                    <Row label={ui.home.line_size}>
                        <span className="text-steel-400">— × —</span>
                    </Row>
                    <Row label={ui.home.line_quantity}>
                        <span className="text-steel-400">—</span>
                    </Row>
                </dl>
                <p className="mt-3 border-t border-white/10 pt-2 text-[11px] leading-5 text-steel-400">{ui.home.line_hint}</p>
            </div>
        </div>
    );
}

function Row({ label, children }: { label: string; children: React.ReactNode }) {
    return (
        <div className="flex items-baseline justify-between gap-3 border-b border-dashed border-white/10 pb-1.5">
            <dt className="shrink-0 text-xs text-steel-400">{label}</dt>
            <dd className="truncate text-end font-medium">{children}</dd>
        </div>
    );
}
