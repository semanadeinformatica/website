import type Company from "./Company";
import type Model from "./Model";

export default interface Sponsor extends Model {
    tier: "PLATINUM" | "GOLD" | "SILVER";
    company_id: number;
    company?: Company;
    edition_id: number;
}
