import type { ReactNode } from 'react';

type QuoteFieldProps = {
    id: string;
    label: string;
    error?: string;
    hint?: string;
    optional?: string;
    required?: boolean;
    className?: string;
    children: (props: { id: string; 'aria-invalid': boolean; 'aria-describedby': string | undefined; className: string }) => ReactNode;
};

export const inputClass =
    'block w-full rounded-xl border border-steel-300 bg-white px-4 py-3 text-navy shadow-xs transition placeholder:text-steel-400 hover:border-steel-400 focus:border-blue focus:ring-4 focus:ring-blue/15 focus:outline-none aria-invalid:border-rose-500 aria-invalid:ring-rose-500/15';

export function QuoteField({ id, label, error, hint, optional, required, className, children }: QuoteFieldProps) {
    const hintId = hint ? `${id}-hint` : null;
    const errorId = error ? `${id}-error` : null;
    const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

    return (
        <div className={className}>
            <label htmlFor={id} className="mb-1.5 flex items-baseline justify-between gap-3 text-sm font-semibold text-navy">
                <span>
                    {label}
                    {required && (
                        <span aria-hidden className="ms-1 text-ember">
                            *
                        </span>
                    )}
                </span>
                {optional && <span className="text-xs font-normal text-steel-500">{optional}</span>}
            </label>
            {children({ id, 'aria-invalid': Boolean(error), 'aria-describedby': describedBy, className: inputClass })}
            {hint && (
                <p id={hintId ?? undefined} className="mt-1.5 text-xs leading-5 text-steel-500">
                    {hint}
                </p>
            )}
            {error && (
                <p id={errorId ?? undefined} className="mt-1.5 text-sm font-medium text-rose-700">
                    {error}
                </p>
            )}
        </div>
    );
}
