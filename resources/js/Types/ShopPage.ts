import type Product from "./Product";

export type BuyableProduct = Product & {
    canBeBought: number;
};
