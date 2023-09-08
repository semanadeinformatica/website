<template>
    <button
        class="flex w-fit bg-2023-teal px-4 py-1 font-semibold text-2023-bg drop-shadow-lg"
    >
        <slot />
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 pl-1 text-2023-bg"
            viewBox="0 0 512 512"
        >
            <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="48"
                d="M112 184l144 144 144-144"
            />
        </svg>
    </button>
</template>
