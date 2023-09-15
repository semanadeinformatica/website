import type Model from "./Model";

export default interface Sponsor extends Model {
    tier: "PLATINUM" | "GOLD" | "SILVER";
    company_id: number;
    edition_id: number;
}
