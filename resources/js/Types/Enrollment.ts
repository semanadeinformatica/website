import type Edition from "./Edition";
import type Model from "./Model";
import type Participant from "./Participant";

export default interface Enrollment extends Model {
    participant_id: number;
    participant?: Participant;

    edition_id: number;
    edition?: Edition;

    points: number;
}
