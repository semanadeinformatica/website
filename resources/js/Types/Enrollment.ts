import type Model from "./Model";

export default interface Enrollment extends Model {
    student_id: number;
    edition_id: number;
    points: number;
}
