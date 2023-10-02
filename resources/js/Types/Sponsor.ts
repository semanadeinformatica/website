import type Company from "./Company";
import type Model from "./Model";

export type Tier = "PLATINUM" | "GOLD" | "SILVER";

export default interface Sponsor extends Model {
    tier: Tier;
    company_id: number;
    company?: Company;
    edition_id: number;
}
