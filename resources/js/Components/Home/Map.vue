<script setup lang="ts">
import { LMap, LTileLayer, LMarker, LIcon } from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";

const zoom = 17;
const center = [41.17759413688228, -8.59592179753306];
const url = "https://tile.openstreetmap.de/{z}/{x}/{y}.png";
const attribution =
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
</script>

<template>
    <div
        class="align-center flex h-full w-full justify-center overflow-hidden rounded"
    >
        <LMap
            v-model:zoom="zoom"
            class="cursor-auto border border-solid border-black"
            :min-zoom="4"
            :max-zoom="18"
            :zoom-animation="true"
            :center="center"
            :use-global-leaflet="false"
            style="height: 400px; width: 800px"
        >
            <LTileLayer
                :url="url"
                :attribution="attribution"
                layer-type="base"
            />
            <LMarker :lat-lng="center">
                <LIcon>
                    <v-icon
                        class="absolute -bottom-[9px] -right-[14px]"
                        name="io-location-sharp"
                        fill="#d94f04"
                        scale="2"
                    ></v-icon>
                </LIcon>
            </LMarker>
        </LMap>
    </div>
</template>
