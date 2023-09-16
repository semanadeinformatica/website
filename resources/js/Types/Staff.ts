import type Model from "./Model";

export default interface Staff extends Model {
    coordinator: boolean;
    department_id: number;
    student_id: number;
}
