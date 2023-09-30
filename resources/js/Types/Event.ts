import type Model from "@/Types/Model";
import type EventDay from "@/Types/EventDay";
import type { User } from "@/Types/User";
import type Edition from "@/Types/Edition";
import type Enrollment from "./Enrollment";

export default interface Event extends Model {
    name: string;
    time_start: string;
    time_end: string;
    topic: string;
    capacity?: number;
    description: string;
    room: string;

    enrollments?: Enrollment[];

    users?: User[];

    edition_id: number;
    edition?: Edition;

    event_day_id: number;
    event_day?: EventDay;
}
