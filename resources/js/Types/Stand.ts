import type Model from "@/Types/Model";
import type Sponsor from "@/Types/Sponsor";
import type EventDay from "@/Types/EventDay";

export default interface Stand extends Model {
    event_day_id: number;
    event_day?: EventDay;

    sponsor_id: number;
    sponsor?: Sponsor;
}
