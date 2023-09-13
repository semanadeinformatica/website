import type Model from "./Model";

export default interface Edition extends Model {
    name: string;
    year: number;
}
