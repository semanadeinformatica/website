<template>
    <div class="flex justify-between">
        <section class="px-4 sm:px-0">
            <h3 class="text-lg font-medium text-2023-teal">
                <slot name="title" />
            </h3>

            <p class="mt-1 text-sm text-2023-teal-dark">
                <slot name="description" />
            </p>
        </section>
    </div>
</template>
