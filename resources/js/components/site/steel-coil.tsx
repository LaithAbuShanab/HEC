import { motion, useReducedMotion, useScroll, useSpring, useTransform } from 'motion/react';
import type { RefObject } from 'react';

const RINGS = Array.from({ length: 14 }, (_, index) => 46 + index * 9.5);

export function SteelCoil({ target }: { target: RefObject<HTMLElement | null> }) {
    const reduced = useReducedMotion();
    const { scrollYProgress } = useScroll({ target, offset: ['start start', 'end start'] });
    const progress = useSpring(scrollYProgress, { stiffness: 80, damping: 20, mass: 0.4 });
    const rotate = useTransform(progress, [0, 1], [0, reduced ? 0 : 220]);
    const sheet = useTransform(progress, [0, 1], ['62%', reduced ? '62%' : '150%']);
    const tilt = useTransform(progress, [0, 1], [reduced ? 0 : -8, reduced ? 0 : 6]);

    return (
        <div aria-hidden className="relative aspect-square w-full max-w-[34rem] [perspective:1200px]">
            <motion.div style={{ rotateX: tilt }} className="absolute inset-0 [transform-style:preserve-3d]">
                <motion.div
                    style={{ width: sheet }}
                    className="brushed-sheen absolute end-1/2 top-[89%] h-[6%] animate-sheen rounded-s-sm bg-gradient-to-b from-steel-100 via-steel-300 to-steel-500 shadow-[0_24px_30px_-18px_rgb(0_0_0/0.9)]"
                >
                    <span className="absolute inset-y-0 start-0 w-1 bg-gold/80 shadow-[0_0_12px_rgb(228_180_42/0.8)]" />
                </motion.div>
                <motion.svg viewBox="0 0 400 400" style={{ rotate }} className="relative size-full drop-shadow-[0_40px_60px_rgb(0_0_0/0.55)]">
                    <defs>
                        <radialGradient id="coil-face" cx="50%" cy="45%" r="60%">
                            <stop offset="0%" stopColor="#e7ebf0" />
                            <stop offset="45%" stopColor="#a3afbf" />
                            <stop offset="80%" stopColor="#566476" />
                            <stop offset="100%" stopColor="#283241" />
                        </radialGradient>
                        <linearGradient id="coil-light" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stopColor="#ffffff" stopOpacity="0.55" />
                            <stop offset="40%" stopColor="#ffffff" stopOpacity="0" />
                            <stop offset="75%" stopColor="#4ca3d6" stopOpacity="0.18" />
                            <stop offset="100%" stopColor="#000c1e" stopOpacity="0.4" />
                        </linearGradient>
                    </defs>
                    <circle cx="200" cy="200" r="182" fill="url(#coil-face)" />
                    {RINGS.map((radius, index) => (
                        <circle
                            key={radius}
                            cx="200"
                            cy="200"
                            r={radius}
                            fill="none"
                            stroke={index % 3 === 0 ? '#283241' : '#3c4858'}
                            strokeOpacity={0.35 + (index % 4) * 0.12}
                            strokeWidth={index % 5 === 0 ? 1.6 : 0.8}
                        />
                    ))}
                    <circle cx="200" cy="200" r="182" fill="url(#coil-light)" />
                    <circle cx="200" cy="200" r="40" fill="#0e1b30" stroke="#7a8899" strokeWidth="3" />
                    <circle cx="200" cy="200" r="22" fill="#000c1e" />
                    <path d="M 200 18 A 182 182 0 0 1 382 200" fill="none" stroke="#e4b42a" strokeWidth="3" strokeLinecap="round" strokeOpacity="0.9" />
                </motion.svg>
            </motion.div>
        </div>
    );
}
