<script setup lang="ts">
defineProps<{
    position: string;
}>();
</script>

<template>
    <div
        class="pointer-events-none fixed z-10"
        :class="{
            'top-0 left-0': position === 'top-left',
            'top-0 right-0': position === 'top-right',
            'bottom-0 left-0': position === 'bottom-left',
            'right-0 bottom-0': position === 'bottom-right',
        }"
    >
        <!-- Batik Pattern SVG -->
        <svg
            class="h-32 w-32 opacity-20 md:h-48 md:w-48"
            :class="{
                'rotate-0': position === 'top-left',
                'rotate-90': position === 'top-right',
                'rotate-180': position === 'bottom-right',
                '-rotate-90': position === 'bottom-left',
            }"
            viewBox="0 0 200 200"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <!-- Batik Kawung Pattern -->
            <defs>
                <pattern
                    id="batik-pattern"
                    x="0"
                    y="0"
                    width="50"
                    height="50"
                    patternUnits="userSpaceOnUse"
                >
                    <!-- Center Circle -->
                    <circle
                        cx="25"
                        cy="25"
                        r="8"
                        fill="#92400e"
                        opacity="0.3"
                    />

                    <!-- Four Petals -->
                    <ellipse
                        cx="25"
                        cy="15"
                        rx="6"
                        ry="10"
                        fill="#92400e"
                        opacity="0.4"
                    />
                    <ellipse
                        cx="35"
                        cy="25"
                        rx="10"
                        ry="6"
                        fill="#92400e"
                        opacity="0.4"
                    />
                    <ellipse
                        cx="25"
                        cy="35"
                        rx="6"
                        ry="10"
                        fill="#92400e"
                        opacity="0.4"
                    />
                    <ellipse
                        cx="15"
                        cy="25"
                        rx="10"
                        ry="6"
                        fill="#92400e"
                        opacity="0.4"
                    />

                    <!-- Decorative Dots -->
                    <circle cx="25" cy="5" r="2" fill="#78350f" opacity="0.5" />
                    <circle
                        cx="45"
                        cy="25"
                        r="2"
                        fill="#78350f"
                        opacity="0.5"
                    />
                    <circle
                        cx="25"
                        cy="45"
                        r="2"
                        fill="#78350f"
                        opacity="0.5"
                    />
                    <circle cx="5" cy="25" r="2" fill="#78350f" opacity="0.5" />
                </pattern>
            </defs>

            <!-- Triangle Shape for Corner -->
            <path d="M 0 0 L 200 0 L 0 200 Z" fill="url(#batik-pattern)" />

            <!-- Border Lines -->
            <line
                x1="0"
                y1="0"
                x2="200"
                y2="0"
                stroke="#92400e"
                stroke-width="2"
                opacity="0.3"
            />
            <line
                x1="0"
                y1="0"
                x2="0"
                y2="200"
                stroke="#92400e"
                stroke-width="2"
                opacity="0.3"
            />
        </svg>
    </div>
</template>
