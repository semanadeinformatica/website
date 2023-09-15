import type Model from "./Model";

export default interface Department extends Model {
    id: number;
    coordinator: string;
}
