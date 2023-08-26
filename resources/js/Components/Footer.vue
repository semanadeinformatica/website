<script setup>

const contacts = [
    ["io-logo-facebook", "bg-2023-red", "https://www.facebook.com/NIAEFEUP"],
    ["io-logo-linkedin", "bg-2023-red-dark", "https://www.linkedin.com/company/nifeup/mycompany/"],
    ["io-logo-instagram", "bg-2023-teal", "https://www.instagram.com/niaefeup/"],
    ["io-mail", "bg-2023-orange", "mailto:ni@aefeup.pt"]
];
</script>

<template>
    <footer class="bg-2023-bg py-4">
        <section class="flex content-center items-center justify-center relative p-8 mx-[25%]">
            <div class="hidden gap-2 flex-row items-center w-full justify-evenly md:flex">
                <template v-for="platform in contacts">
                    <a class="p-2.5 rounded-full flex w-fit" :class="platform[1]" target="_blank" :href="platform[2]">
                        <v-icon :name="platform[0]" fill="white" scale="1.3"></v-icon>
                    </a>
                </template>
            </div>
            <div class="flex flex-col items-center gap-2 md:hidden">
                <div class="grid grid-cols-2 gap-2 justify-center">
                    <template v-for="platform in contacts">
                        <a class="p-2.5 rounded-full flex w-fit" :class="platform[1]" target="_blank" :href="platform[2]">
                            <v-icon :name="platform[0]" fill="white" scale="1.3"></v-icon>
                        </a>
                    </template>    
                </div>
            </div>
        </section>  
        <div class="container mx-auto text-center font-bold text-2023-teal">
            <p>Copyright &copy; 2023 niaefeup, todos os direitos reservados</p>
        </div>
    </footer>

</template>

<style>
</style>