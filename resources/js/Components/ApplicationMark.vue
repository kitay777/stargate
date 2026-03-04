<style scoped>
.icon-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 24px;
}
.icon-row img {
    height: 24px;
    width: auto;
}
</style>
<template>
    <Link :href="'/dashboard'">
        <div class="grid gap-6 lg:grid-cols-2 lg:gap-8 text-center">
            <div class="icon-row">
                <img
                    src="/assets/imgs/starmark.png"
                    alt="Star Mark Icon"
                    style="height: 36px; width: auto"
                />
            </div>
        </div>
    </Link>
</template>
