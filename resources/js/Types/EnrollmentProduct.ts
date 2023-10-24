import type Enrollment from "./Enrollment";
import type Product from "./Product";

export default interface EnrollmentProduct {
    enrollment_id: number;
    enrollment?: Enrollment;

    product_id: number;
    product?: Product;

    redeemed: boolean;
}
