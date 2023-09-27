import type Model from "@/Types/Model";
import type EventDay from "@/Types/EventDay";
import type { User } from "@/Types/User";
import type EventType from "@/Types/EventType";

export default interface Event extends Model {
    name: string;
    time_start: string;
    time_end: string;
    topic: string;
    capacity?: number;
    description: string;
    room: string;

    users?: User[];

    event_type_id: number;
    type?: EventType;

    event_day_id: number;
    event_day?: EventDay;
}
