<script setup lang="ts">
import { LMap, LTileLayer, LMarker, LIcon } from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";

const zoom = 17;
const center = [41.17835293313974, -8.595830311142494];
const url = "https://tile.openstreetmap.de/{z}/{x}/{y}.png";
const attribution =
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
</script>

<template>
    <div
        class="align-center flex h-full w-full justify-center overflow-hidden rounded"
    >
        <l-map
            v-model:zoom="zoom"
            class="cursor-auto border border-solid border-black"
            :minZoom="4"
            :maxZoom="18"
            :zoomAnimation="true"
            :center="center"
            :use-global-leaflet="false"
            style="height: 300px; width: 600px"
        >
            <l-tile-layer
                :url="url"
                :attribution="attribution"
                layer-type="base"
            />
            <l-marker :lat-lng="[41.17835293313974, -8.595830311142494]">
                <l-icon>
                    <v-icon
                        class="absolute -bottom-[9px] -right-[14px]"
                        name="io-location-sharp"
                        fill="#d94f04"
                        scale="2"
                    ></v-icon>
                </l-icon>
            </l-marker>
        </l-map>
    </div>
</template>
