import type Model from "./Model";

export default interface Product extends Model {
    name: string;
    price: number;
    stock: number;
    edition_id: number;
}
