import type Model from "@/Types/Model";
import type EventDay from "@/Types/EventDay";
import type Enrollment from "./Enrollment";
import type { User } from "@/Types/User";
import type EventType from "@/Types/EventType";

export default interface Event extends Model {
    name: string;
    time_start: string;
    time_end: string;
    topic: string;
    capacity?: number;

    description?: string;
    description_html: string;

    location?: string;
    external_url?: string;

    enrollments?: Enrollment[];

    enroll_in_site: boolean;

    users?: User[];

    event_type_id: number;
    type?: EventType;

    event_day_id: number;
    event_day?: EventDay;
}
