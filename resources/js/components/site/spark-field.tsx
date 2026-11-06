import { useEffect, useRef } from 'react';
import { prefersReducedMotion } from '@/lib/site';

type Spark = { x: number; y: number; vx: number; vy: number; life: number; max: number; size: number };

export function SparkField({ className }: { className?: string }) {
    const canvasRef = useRef<HTMLCanvasElement>(null);

    useEffect(() => {
        const canvas = canvasRef.current;
        const context = canvas?.getContext('2d');

        if (!canvas || !context || prefersReducedMotion()) {
            return;
        }

        const sparks: Spark[] = [];
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        let frame = 0;
        let visible = true;
        let pointer = { x: -1, y: -1, active: false };

        const resize = () => {
            const { width, height } = canvas.getBoundingClientRect();
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
        };

        const emit = (x: number, y: number, count: number, force: number) => {
            for (let index = 0; index < count; index++) {
                const angle = -Math.PI / 2 + (Math.random() - 0.5) * Math.PI * 1.1;
                const speed = force * (0.4 + Math.random());
                sparks.push({
                    x,
                    y,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    life: 0,
                    max: 40 + Math.random() * 50,
                    size: 0.6 + Math.random() * 1.6,
                });
            }
        };

        const tick = () => {
            const { width, height } = canvas.getBoundingClientRect();
            context.clearRect(0, 0, width, height);

            if (pointer.active) {
                emit(pointer.x, pointer.y, 2, 3.2);
            } else if (Math.random() < 0.35) {
                emit(width * (0.15 + Math.random() * 0.7), height * (0.78 + Math.random() * 0.2), 1, 2.4);
            }

            context.globalCompositeOperation = 'lighter';

            for (let index = sparks.length - 1; index >= 0; index--) {
                const spark = sparks[index];
                spark.life++;
                spark.vy += 0.06;
                spark.vx *= 0.985;
                spark.x += spark.vx;
                spark.y += spark.vy;

                const progress = spark.life / spark.max;

                if (progress >= 1) {
                    sparks.splice(index, 1);
                    continue;
                }

                const alpha = 1 - progress;
                context.strokeStyle = `rgba(255, ${Math.round(200 - progress * 120)}, ${Math.round(80 - progress * 60)}, ${alpha})`;
                context.lineWidth = spark.size;
                context.beginPath();
                context.moveTo(spark.x, spark.y);
                context.lineTo(spark.x - spark.vx * 2.2, spark.y - spark.vy * 2.2);
                context.stroke();
            }

            context.globalCompositeOperation = 'source-over';

            if (visible) {
                frame = requestAnimationFrame(tick);
            }
        };

        const onMove = (event: PointerEvent) => {
            const rect = canvas.getBoundingClientRect();
            pointer = { x: event.clientX - rect.left, y: event.clientY - rect.top, active: true };
        };
        const onLeave = () => {
            pointer = { ...pointer, active: false };
        };

        const observer = new IntersectionObserver(([entry]) => {
            visible = entry.isIntersecting;

            if (visible) {
                cancelAnimationFrame(frame);
                frame = requestAnimationFrame(tick);
            }
        });

        resize();
        observer.observe(canvas);
        window.addEventListener('resize', resize);
        canvas.parentElement?.addEventListener('pointermove', onMove);
        canvas.parentElement?.addEventListener('pointerleave', onLeave);

        return () => {
            cancelAnimationFrame(frame);
            observer.disconnect();
            window.removeEventListener('resize', resize);
            canvas.parentElement?.removeEventListener('pointermove', onMove);
            canvas.parentElement?.removeEventListener('pointerleave', onLeave);
        };
    }, []);

    return <canvas ref={canvasRef} aria-hidden className={className} />;
}
