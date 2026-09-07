import type Enrollment from "./Enrollment";
import type Product from "./Product";

export interface EnrollmentWithRedeemed extends Enrollment {
    pivot: {
        redeemed: boolean;
    };
}

export type BuyableProduct = Omit<Product, "enrollments"> & {
    canBeBought?: boolean | number;
    alreadyBought?: boolean;
    enrollments?: EnrollmentWithRedeemed[];
};
