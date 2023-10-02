import type Model from "./Model";
import type Staff from "./Staff";

export default interface Department extends Model {
    name: string;
    edition_id: number;
    priority: number;
    staff?: Staff[];
}
