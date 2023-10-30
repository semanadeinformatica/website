<script setup lang="ts">
import type CompetitionTeam from "@/Types/CompetitionTeam";
import { type CompetitionPrizes } from "@/Types/Competition";
import { computed } from "vue";

interface Props {
    prizes: CompetitionPrizes;
    leaderboard: CompetitionTeam[];
}

const props = defineProps<Props>();

const leaderboard = computed(() => props.leaderboard);
const prizes = computed(() => props.prizes);

const images = computed(() => {
    // we need to have all 3 teams in order to display stuff
    if (leaderboard.value.length < 3)
        return [
            prizes.value.firstPlace,
            prizes.value.secondPlace,
            prizes.value.thirdPlace,
        ];
    return leaderboard.value.map((team) =>
        team.image_competition_team_url !== "http://localhost:8000/storage/"
            ? team.image_competition_team_url
            : `https://ui-avatars.com/api/?size=512&name=${team.name
                  .split(" ")
                  .map((t) => t[0])
                  .join("+")}&color=f8f5e7&background=d94f04`,
    );
});

const hasLeaderboardTeams = computed(() => leaderboard.value.length >= 3);
</script>

<template>
    <section class="relative flex flex-col items-center justify-center py-28">
        <svg
            width="75%"
            height="75%"
            viewBox="0 0 1220 582"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <g filter="url(#filter0_i_541_2)">
                <rect
                    x="806"
                    y="440.72"
                    width="398"
                    height="141.28"
                    fill="#D48145"
                />
            </g>
            <rect x="790" y="435" width="430" height="10.8677" fill="#D48145" />
            <path
                d="M1015.8 513.918C1018.53 514.373 1020.9 515.444 1022.91 517.132C1024.99 518.755 1026.58 520.801 1027.69 523.268C1028.85 525.67 1029.44 528.397 1029.44 531.449C1029.44 535.474 1028.37 538.98 1026.23 541.967C1024.08 544.954 1021.03 547.259 1017.07 548.882C1013.11 550.505 1008.44 551.317 1003.05 551.317C998.111 551.317 993.696 550.57 989.801 549.077C985.97 547.648 982.659 545.506 979.867 542.649L988.242 531.546C989.996 533.299 992.008 534.663 994.281 535.637C996.553 536.61 999.215 537.097 1002.27 537.097C1005.97 537.097 1008.73 536.448 1010.55 535.15C1012.36 533.786 1013.27 531.741 1013.27 529.014C1013.27 526.092 1012.17 523.885 1009.96 522.391C1007.82 520.898 1004.41 520.151 999.735 520.151H996.813V508.951H1000.03C1003.6 508.951 1006.42 508.205 1008.5 506.711C1010.58 505.153 1011.62 503.173 1011.62 500.77C1011.62 499.342 1011.26 498.141 1010.55 497.167C1009.9 496.193 1008.95 495.479 1007.72 495.024C1006.55 494.505 1005.12 494.245 1003.44 494.245C1000.84 494.245 998.436 494.862 996.229 496.095C994.021 497.329 992.171 499.147 990.677 501.549L980.841 490.252C982.269 488.369 984.217 486.746 986.684 485.382C989.216 483.954 992.073 482.85 995.255 482.071C998.436 481.292 1001.75 480.902 1005.19 480.902C1009.8 480.902 1013.82 481.617 1017.27 483.045C1020.77 484.473 1023.47 486.486 1025.35 489.083C1027.3 491.615 1028.27 494.635 1028.27 498.141C1028.27 500.673 1027.75 503.075 1026.71 505.348C1025.67 507.555 1024.21 509.438 1022.33 510.996C1020.45 512.49 1018.27 513.464 1015.8 513.918Z"
                fill="#F8F5E7"
            />
            <g filter="url(#filter1_i_541_2)">
                <rect
                    x="16"
                    y="404.198"
                    width="398"
                    height="177.802"
                    fill="#C3C3C3"
                />
            </g>
            <rect y="397" width="430" height="13.677" fill="#C3C3C3" />
            <path
                d="M181.55 533C181.55 528.952 181.852 525.334 182.455 522.148C183.144 518.961 184.22 516.076 185.685 513.492C187.235 510.822 189.259 508.367 191.757 506.128C194.341 503.802 197.571 501.606 201.446 499.539C205.494 497.386 208.853 495.577 211.523 494.113C214.28 492.562 216.476 491.27 218.112 490.237C219.749 489.203 220.869 488.213 221.471 487.265C222.16 486.232 222.505 485.112 222.505 483.906C222.505 482.27 222.117 480.849 221.342 479.643C220.567 478.437 219.447 477.533 217.983 476.93C216.519 476.241 214.71 475.896 212.557 475.896C210.834 475.896 209.198 476.112 207.648 476.542C206.097 476.973 204.59 477.619 203.126 478.48C201.748 479.341 200.413 480.375 199.121 481.581C197.829 482.7 196.537 483.992 195.245 485.457L182.196 470.212C183.661 468.748 185.512 467.283 187.752 465.819C190.077 464.355 192.704 463.063 195.633 461.943C198.561 460.738 201.748 459.79 205.193 459.101C208.724 458.326 212.385 457.938 216.174 457.938C222.376 457.938 227.587 458.886 231.807 460.781C236.113 462.589 239.386 465.173 241.626 468.532C243.865 471.891 244.985 475.853 244.985 480.418C244.985 483.346 244.511 485.93 243.564 488.17C242.702 490.323 241.54 492.261 240.075 493.983C238.611 495.706 236.932 497.256 235.037 498.634C233.228 500.012 231.419 501.261 229.611 502.381C227.544 503.415 225.606 504.448 223.797 505.482C221.988 506.429 220.309 507.377 218.758 508.324C217.208 509.185 215.744 510.133 214.366 511.166C213.074 512.114 211.825 513.19 210.619 514.396H246.406V533H181.55Z"
                fill="#F8F5E7"
            />
            <g filter="url(#filter2_i_541_2)">
                <rect x="408" y="335" width="398" height="247" fill="#F2BC30" />
            </g>
            <rect x="392" y="325" width="430" height="19" fill="#F2BC30" />
            <path
                d="M641.47 489.897V511H578.161V489.897H598.971V452.527C598.091 453.7 596.724 454.872 594.867 456.044C593.011 457.217 590.911 458.34 588.566 459.415C586.319 460.49 584.023 461.369 581.678 462.053C579.333 462.639 577.184 462.932 575.23 462.932V441.243C576.891 441.243 578.942 440.754 581.385 439.777C583.827 438.703 586.27 437.384 588.712 435.821C591.253 434.257 593.451 432.694 595.307 431.131C597.261 429.568 598.482 428.298 598.971 427.321H622.858V489.897H641.47Z"
                fill="#F8F5E7"
            />

            <clipPath id="first">
                <rect
                    x="471.5"
                    y="3.5"
                    width="271"
                    height="271"
                    rx="15.5"
                    stroke="#007172"
                    stroke-width="7"
                />
            </clipPath>
            <image
                width="271"
                height="271"
                x="471.5"
                y="3.5"
                :href="images[0]"
                clip-path="url(#first)"
            />
            <clipPath id="second">
                <rect
                    x="69.5"
                    y="76.5"
                    width="271"
                    height="271"
                    rx="15.5"
                    stroke="#007172"
                    stroke-width="7"
                />
            </clipPath>
            <image
                width="271"
                height="271"
                x="69.5"
                y="76.5"
                :href="images[1]"
                clip-path="url(#second)"
            />

            <clipPath id="third">
                <rect
                    x="873.5"
                    y="98.5"
                    width="271"
                    height="271"
                    rx="15.5"
                    stroke="#007172"
                    stroke-width="7"
                />
            </clipPath>
            <image
                width="271"
                height="271"
                x="873.5"
                y="98.5"
                :href="images[2]"
                clip-path="url(#third)"
            />

            <defs>
                <filter
                    id="filter0_i_541_2"
                    x="806"
                    y="440.72"
                    width="398"
                    height="141.28"
                    filterUnits="userSpaceOnUse"
                    color-interpolation-filters="sRGB"
                >
                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                    <feBlend
                        mode="normal"
                        in="SourceGraphic"
                        in2="BackgroundImageFix"
                        result="shape"
                    />
                    <feColorMatrix
                        in="SourceAlpha"
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                        result="hardAlpha"
                    />
                    <feOffset dy="21" />
                    <feComposite
                        in2="hardAlpha"
                        operator="arithmetic"
                        k2="-1"
                        k3="1"
                    />
                    <feColorMatrix
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"
                    />
                    <feBlend
                        mode="normal"
                        in2="shape"
                        result="effect1_innerShadow_541_2"
                    />
                </filter>
                <filter
                    id="filter1_i_541_2"
                    x="16"
                    y="404.198"
                    width="398"
                    height="177.802"
                    filterUnits="userSpaceOnUse"
                    color-interpolation-filters="sRGB"
                >
                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                    <feBlend
                        mode="normal"
                        in="SourceGraphic"
                        in2="BackgroundImageFix"
                        result="shape"
                    />
                    <feColorMatrix
                        in="SourceAlpha"
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                        result="hardAlpha"
                    />
                    <feOffset dy="21" />
                    <feComposite
                        in2="hardAlpha"
                        operator="arithmetic"
                        k2="-1"
                        k3="1"
                    />
                    <feColorMatrix
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"
                    />
                    <feBlend
                        mode="normal"
                        in2="shape"
                        result="effect1_innerShadow_541_2"
                    />
                </filter>
                <filter
                    id="filter2_i_541_2"
                    x="408"
                    y="335"
                    width="398"
                    height="247"
                    filterUnits="userSpaceOnUse"
                    color-interpolation-filters="sRGB"
                >
                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                    <feBlend
                        mode="normal"
                        in="SourceGraphic"
                        in2="BackgroundImageFix"
                        result="shape"
                    />
                    <feColorMatrix
                        in="SourceAlpha"
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                        result="hardAlpha"
                    />
                    <feOffset dy="21" />
                    <feComposite
                        in2="hardAlpha"
                        operator="arithmetic"
                        k2="-1"
                        k3="1"
                    />
                    <feColorMatrix
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"
                    />
                    <feBlend
                        mode="normal"
                        in2="shape"
                        result="effect1_innerShadow_541_2"
                    />
                </filter>
                <filter
                    id="filter3_i_541_2"
                    x="582"
                    y="264"
                    width="50"
                    height="50"
                    filterUnits="userSpaceOnUse"
                    color-interpolation-filters="sRGB"
                >
                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                    <feBlend
                        mode="normal"
                        in="SourceGraphic"
                        in2="BackgroundImageFix"
                        result="shape"
                    />
                    <feColorMatrix
                        in="SourceAlpha"
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                        result="hardAlpha"
                    />
                    <feMorphology
                        radius="6"
                        operator="erode"
                        in="SourceAlpha"
                        result="effect1_innerShadow_541_2"
                    />
                    <feOffset />
                    <feComposite
                        in2="hardAlpha"
                        operator="arithmetic"
                        k2="-1"
                        k3="1"
                    />
                    <feColorMatrix
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"
                    />
                    <feBlend
                        mode="normal"
                        in2="shape"
                        result="effect1_innerShadow_541_2"
                    />
                </filter>
                <filter
                    id="filter4_i_541_2"
                    x="180"
                    y="335"
                    width="50"
                    height="50"
                    filterUnits="userSpaceOnUse"
                    color-interpolation-filters="sRGB"
                >
                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                    <feBlend
                        mode="normal"
                        in="SourceGraphic"
                        in2="BackgroundImageFix"
                        result="shape"
                    />
                    <feColorMatrix
                        in="SourceAlpha"
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                        result="hardAlpha"
                    />
                    <feMorphology
                        radius="6"
                        operator="erode"
                        in="SourceAlpha"
                        result="effect1_innerShadow_541_2"
                    />
                    <feOffset />
                    <feComposite
                        in2="hardAlpha"
                        operator="arithmetic"
                        k2="-1"
                        k3="1"
                    />
                    <feColorMatrix
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"
                    />
                    <feBlend
                        mode="normal"
                        in2="shape"
                        result="effect1_innerShadow_541_2"
                    />
                </filter>
                <filter
                    id="filter5_i_541_2"
                    x="984"
                    y="359"
                    width="50"
                    height="50"
                    filterUnits="userSpaceOnUse"
                    color-interpolation-filters="sRGB"
                >
                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                    <feBlend
                        mode="normal"
                        in="SourceGraphic"
                        in2="BackgroundImageFix"
                        result="shape"
                    />
                    <feColorMatrix
                        in="SourceAlpha"
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                        result="hardAlpha"
                    />
                    <feMorphology
                        radius="6"
                        operator="erode"
                        in="SourceAlpha"
                        result="effect1_innerShadow_541_2"
                    />
                    <feOffset />
                    <feComposite
                        in2="hardAlpha"
                        operator="arithmetic"
                        k2="-1"
                        k3="1"
                    />
                    <feColorMatrix
                        type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"
                    />
                    <feBlend
                        mode="normal"
                        in2="shape"
                        result="effect1_innerShadow_541_2"
                    />
                </filter>
            </defs>
        </svg>

        <div
            v-if="hasLeaderboardTeams"
            class="mt-10 grid w-3/4 grid-cols-3 items-center gap-4 px-1.5 text-center align-middle text-2023-teal-dark max-xs:grid-cols-1"
        >
            <div
                class="align-center text-wrap flex flex-col items-center truncate px-1 text-xl max-xs:row-start-2"
            >
                <span class="whitespace-normal font-bold">{{
                    leaderboard[1].name
                }}</span>
                <span class="">{{ leaderboard[1].points }}</span>
            </div>
            <div
                class="align-center text-wrap flex flex-col items-center truncate px-3 text-2xl"
            >
                <span class="whitespace-normal font-bold">{{
                    leaderboard[0].name
                }}</span>
                <span class="">{{ leaderboard[0].points }}</span>
            </div>
            <div
                class="align-center text-wrap flex flex-col items-center truncate px-1 text-lg"
            >
                <span class="whitespace-normal font-bold">{{
                    leaderboard[2].name
                }}</span>
                <span class="">{{ leaderboard[2].points }}</span>
            </div>
        </div>
    </section>
</template>
