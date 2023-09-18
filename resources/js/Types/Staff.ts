import type Model from "./Model";

export default interface Staff extends Model {
    coordinator: boolean;
    department_id: number;
    participant_id: number;
}
