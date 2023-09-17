import type Model from "./Model";
import type Participant from "./Participant";

export default interface Staff extends Model {
    coordinator: boolean;
    department_id: number;
    participant_id: number;
    participant?: Participant;
}
