import type Enrollment from "./Enrollment";
import type Product from "./Product";

export type BuyableProduct = Product & {
    canBeBought: number;
    enrollments?: (Enrollment & {
        pivot: {
            redeemed: boolean;
        };
    })[];
};
