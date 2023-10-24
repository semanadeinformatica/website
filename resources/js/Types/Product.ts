import type Enrollment from "./Enrollment";
import type Model from "./Model";

export default interface Product extends Model {
    name: string;
    price: number;
    stock: number;
    edition_id: number;
    image_product_url: string;

    enrollments?: Enrollment[];
}
