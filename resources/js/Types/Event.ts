import type EventDay from "./EventDay";
import type Model from "./Model";

export default interface Event extends Model {
    name: string;
    time_start: string;
    time_end: string;
    topic: string;
    capacity?: number;
    event_day_id: EventDay;
}
