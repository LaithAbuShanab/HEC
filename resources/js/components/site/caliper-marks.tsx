export function CaliperMarks({ className }: { className?: string }) {
    const ticks = Array.from({ length: 41 }, (_, index) => index);

    return (
        <svg viewBox="0 0 400 400" aria-hidden className={className}>
            <g stroke="rgb(76 163 214 / 0.55)" strokeWidth="1" fill="none">
                <path d="M 18 392 H 382" />
                <path d="M 18 384 V 398" />
                <path d="M 382 384 V 398" />
                {ticks.map((tick) => (
                    <path key={tick} d={`M ${20 + tick * 9} 392 V ${tick % 5 === 0 ? 384 : 388}`} strokeOpacity={tick % 5 === 0 ? 0.9 : 0.5} />
                ))}
                <path d="M 392 18 V 382" />
                <path d="M 384 18 H 398" />
                <path d="M 384 382 H 398" />
                <circle cx="200" cy="200" r="196" strokeDasharray="2 10" strokeOpacity="0.45" />
            </g>
        </svg>
    );
}
