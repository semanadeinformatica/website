import type Model from "./Model";

export default interface Event extends Model {
    name: string;
    date_start: string;
    date_end: string;
    topic: string;
    capacity?: number;
    edition_id: number;
}
