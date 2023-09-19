import type Model from "./Model";

export default interface Enrollment extends Model {
    participant_id: number;
    edition_id: number;
    points: number;
}
