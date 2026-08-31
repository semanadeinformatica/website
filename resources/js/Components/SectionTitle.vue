<template>
    <div class="flex justify-between">
        <section class="px-4 sm:px-0">
            <h3 class="text-2023-teal text-lg font-medium">
                <slot name="title" />
            </h3>

            <p class="text-2023-teal-dark mt-1 text-sm">
                <slot name="description" />
            </p>
        </section>
    </div>
</template>
