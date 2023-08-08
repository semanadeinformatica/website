<script setup>
</script>

<template>
  <main>
    <div class="flex flex-col gap-[60px] py-[65px] justify-evenly items-center content-center min-h-full">
                <img id="svg-1" class="opacity-50 absolute w-[150px] left-[7%]" src="storage/images/cy-sinf.svg" alt="Stylized SINF logo">
                <img id="svg-2" class="opacity-50 absolute top-[70px] w-[100px] right-[7%]" src="storage/images/rc-sinf.svg" alt="Stylized SINF logo">
                <img id="svg-3" class="opacity-50 absolute bottom-[100px] w-[70px] right-[15%]" src="storage/images/oc-sinf.svg" alt="Stylized SINF logo">
                
                <div class="relative">
                    <img class="w-[450px]" src="storage/images/sinf logo.png" alt="Stylized SINF logo">
                    <span id="year">2023</span>
                </div>
                <p id="semana-box">semana_de_informática</p>
                <p id="date">25 a 31 de outubro</p>
                <p id="flavor-text">Estamos a preparar mais uma edição da SINF!</p>
                <a href="https://sinf.pt" id="last-year">
                    Revê a edição do último ano aqui!
                    <img id="pointer" src="storage/images/pointer.png" alt="Pointer">
                </a>
                <section id="contacts">
                    <p id="contacts-title">Contacta-nos!</p>
                    <div id="contacts-content-desktop">
                        <a target="_blank" href="https://www.facebook.com/NIAEFEUP">
                            <i class="social-icon" data-feather="facebook"></i>
                        </a>
                        <a target="_blank" href="https://www.linkedin.com/company/nifeup/mycompany/">
                            <i class="social-icon" data-feather="linkedin"></i>
                        </a>
                        <p id="email">externas@sinf.pt</p>
                        <a target="_blank" href="https://www.instagram.com/niaefeup/">
                            <i class="social-icon" data-feather="instagram"></i>
                        </a>
                        <a target="_blank" href="https://twitter.com/niaefeup">
                            <i class="social-icon" data-feather="twitter"></i>
                        </a>
                    </div>
                    <div id="contacts-content-mobile">
                        <p id="email">externas@sinf.pt</p>
                        <div id="socials-wrapper">
                            <a target="_blank" href="https://www.facebook.com/NIAEFEUP">
                                <i class="social-icon" data-feather="facebook"></i>
                            </a>
                            <a target="_blank" href="https://www.linkedin.com/company/nifeup/mycompany/">
                                <i class="social-icon" data-feather="linkedin"></i>
                            </a>
                            <a target="_blank" href="https://www.instagram.com/niaefeup/">
                                <i class="social-icon" data-feather="instagram"></i>
                            </a>
                            <a target="_blank" href="https://twitter.com/niaefeup">
                                <i class="social-icon" data-feather="twitter"></i>
                            </a>    
                        </div>
                    </div>
                </section>    
            </div>
  </main>
</template>

<style>
    /* @import url('https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap'); */

    :root {
        --cyan: #007172;
        --dorange: #D94F04;
        --whiteish: #F8F5E7;
        --rose: #B15D5D;
        --lorange: #F29325;
    }

    p {
        margin: 0px;
    }

    a, a:visited, a:hover, a:active {
        color: inherit;
        text-decoration: none;
    }  

    * {
        box-sizing: border-box;
        font-family: 'Raleway', sans-serif;
    }

    body, html {
        margin: 0;
        background: var(--whiteish);
        min-height: 100%;
        min-width: 100%;
    }

    main {
        min-height: 100vh;
        min-width: 100vw;
    }

    img[id^="svg-"] {
        opacity: 0.5;
        position: absolute;
        animation-duration: 2s;
        animation-iteration-count: infinite;
        animation-name: jump;
        animation-timing-function: cubic-bezier(0.46, 0.03, 0.52, 0.96);
    }

    @keyframes jump {
        0%   { transform: translateY(0px); }
        50%  { transform: translateY(-30px); }
        100% { transform: translateY(0px); }
    }


    #svg-1 {
        left: 7%;
        width: 150px;
        animation-delay: -0.5s;
    }

    #svg-2 {
        right: 7%;
        top: 70px;
        width: 150px;
        animation-delay: -1s;
    }

    #svg-3 {
        right: 15%;
        bottom: 100px;
        width: 100px;
        animation-delay: -0.8s;
    }

    #sinf-logo {
        width: 450px;
    }

    #semana-box {
        padding: 10px;
        border: 1px solid #000;
        padding-inline: 30px;
        box-shadow: 8px 8px 0px -1px var(--cyan), 8px 8px 0px 0px black;
        font-size: large;
        font-weight: bold;
        color: var(--cyan);
        margin-right: 8px;
    }

    #date {
        font-size: xx-large;
        font-weight: bold;
        color: var(--cyan);
        margin: 0;
    }

    #top-wrapper {
        padding-block: 65px;
        position: relative;
        display: flex;
        gap: 60px;
        flex-direction: column;
        justify-content: space-evenly;
        align-items: center;
        align-content: center;
        min-height: 100vh;
    }

    #flavor-text, #last-year {
        font-size: large;
        font-weight: bold;
        color: var(--cyan);
        margin: 0;
    }

    #last-year, #logo-wrapper{
        position: relative
    }

    #year {
        font-size: x-large;
        font-weight: bold;
        color: var(--cyan);
        margin: 0;
        position: absolute;
        bottom: -20px;
        right: 0px;
    }

    #pointer {
        width: 12px;
        position: absolute;
        bottom: -5px;
        right: 5px;
    }

    #contacts-title {
        background: var(--dorange);
        padding: 10px;
        padding-inline: 20px;
        color: white;
        font-size: large;
        border: solid 1px black;
        font-weight: bold;
        box-shadow: 8px 8px 0px -1px var(--whiteish), 8px 8px 0px 0px black;
        width: 165px;
        position: absolute;
        top: -30px;
        left: calc(50% - 86.5px) ;
    }

    #contacts {
        display: flex;
        justify-content: center;
        align-items: center;
        align-content: center;
        position: relative;
        padding: 30px;
        border: 1px solid black;
        margin-inline: 25%;
    }

    .social-icon {
        color: white;
    }

    #email {
        font-size: large;
        font-weight: bold;
        color: var(--cyan);
    }

    #socials-wrapper {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        flex-direction: column;
        gap: 10px;
        justify-content: center;
    }

    #contacts-content-mobile {
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    #contacts-content-desktop {
        display: flex;
        gap: 10px;
        flex-direction: row;
        align-items: center;
        width: 100%;
        justify-content: space-evenly
    }

    #contacts-content-desktop > a,
    #socials-wrapper > a {
        padding: 7px;
        border-radius: 99px;    
        display: flex;
        width: fit-content;
    }

    #contacts-content-desktop > a:nth-child(1),
    #socials-wrapper > a:nth-child(1) {
        background: var(--dorange);
    }

    #contacts-content-desktop > a:nth-child(2),
    #socials-wrapper > a:nth-child(2) {
        background: var(--rose);
    }

    #contacts-content-desktop > a:nth-child(4),
    #socials-wrapper > a:nth-child(3) {
        background: var(--cyan);
    }

    #contacts-content-desktop > a:nth-child(5),
    #socials-wrapper > a:nth-child(4) {
        background: var(--lorange);
    }

    @media only screen and (max-width: 900px) {
        img[id^="svg-"] {
            display: none;
        }    

        #sinf-logo {
            width: 300px;
        }

        #flavor-text {
            font-size: medium;
        }

        #email {
            font-size: medium;
        }

        #contacts-content-desktop {
            display: none;
        }


        #contacts-content-mobile {
            display: flex;
        }
    }
</style>