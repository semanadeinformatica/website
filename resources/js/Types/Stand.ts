import type Model from "@/Types/Model";
import type Sponsor from "@/Types/Sponsor";

export default interface Stand extends Model {
    event_day_id: number;
    sponsor_id: number;

    sponsor: Sponsor;
}
